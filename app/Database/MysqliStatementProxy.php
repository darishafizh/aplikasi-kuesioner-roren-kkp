<?php

namespace App\Database;

/**
 * A PDOStatement-compatible wrapper around mysqli prepared statements.
 * Used to bypass PDO SSL issues on servers where mysqli works but PDO doesn't.
 */
class MysqliStatementProxy
{
    private \mysqli $mysqli;
    private string $query;
    private ?\mysqli_stmt $stmt = null;
    private ?\mysqli_result $result = null;
    private int $fetchMode = \PDO::FETCH_OBJ;
    private array $bindings = [];
    private int $affectedRows = 0;

    public function __construct(\mysqli $mysqli, string $query)
    {
        $this->mysqli = $mysqli;
        $this->query = $query;
    }

    public function setFetchMode(int $mode, mixed ...$args): bool
    {
        $this->fetchMode = $mode;
        return true;
    }

    public function bindValue(int|string $param, mixed $value, int $type = \PDO::PARAM_STR): bool
    {
        $this->bindings[$param] = ['value' => $value, 'type' => $type];
        return true;
    }

    public function execute(?array $params = null): bool
    {
        if ($params !== null) {
            foreach (array_values($params) as $i => $val) {
                $this->bindings[$i + 1] = ['value' => $val, 'type' => \PDO::PARAM_STR];
            }
        }

        if (empty($this->bindings)) {
            // Simple query without bindings
            $result = $this->mysqli->query($this->query);
            if ($result === false) {
                return false;
            }
            if ($result instanceof \mysqli_result) {
                $this->result = $result;
            }
            $this->affectedRows = $this->mysqli->affected_rows;
            return true;
        }

        // Prepared statement with bindings
        $this->stmt = $this->mysqli->prepare($this->query);
        if ($this->stmt === false) {
            return false;
        }

        // Build types string and values array
        $types = '';
        $values = [];
        ksort($this->bindings);
        foreach ($this->bindings as $binding) {
            $val = $binding['value'];
            $pdoType = $binding['type'];
            if ($val === null) {
                $types .= 's';
                $values[] = null;
            } elseif ($pdoType === \PDO::PARAM_INT || is_int($val)) {
                $types .= 'i';
                $values[] = (int) $val;
            } elseif (is_float($val) || is_double($val)) {
                $types .= 'd';
                $values[] = (float) $val;
            } else {
                $types .= 's';
                $values[] = (string) $val;
            }
        }

        if (!empty($types)) {
            $this->stmt->bind_param($types, ...$values);
        }

        $success = $this->stmt->execute();
        if (!$success) {
            return false;
        }

        $this->affectedRows = $this->stmt->affected_rows;
        $this->result = $this->stmt->get_result();

        return true;
    }

    public function fetch(int $mode = \PDO::FETCH_DEFAULT): mixed
    {
        if (!$this->result) {
            return false;
        }

        $fetchMode = ($mode === \PDO::FETCH_DEFAULT || $mode === 0) ? $this->fetchMode : $mode;

        return match ($fetchMode) {
            \PDO::FETCH_ASSOC => $this->result->fetch_assoc() ?: false,
            \PDO::FETCH_NUM => $this->result->fetch_row() ?: false,
            \PDO::FETCH_BOTH => $this->result->fetch_array() ?: false,
            \PDO::FETCH_OBJ => $this->result->fetch_object() ?: false,
            default => $this->result->fetch_assoc() ?: false,
        };
    }

    public function fetchAll(int $mode = \PDO::FETCH_DEFAULT, mixed ...$args): array
    {
        if (!$this->result) {
            return [];
        }

        $fetchMode = ($mode === \PDO::FETCH_DEFAULT || $mode === 0) ? $this->fetchMode : $mode;
        $rows = [];

        while (true) {
            $row = match ($fetchMode) {
                \PDO::FETCH_ASSOC => $this->result->fetch_assoc(),
                \PDO::FETCH_NUM => $this->result->fetch_row(),
                \PDO::FETCH_BOTH => $this->result->fetch_array(),
                \PDO::FETCH_OBJ => $this->result->fetch_object(),
                \PDO::FETCH_COLUMN => $this->result->fetch_row(),
                default => $this->result->fetch_assoc(),
            };

            if ($row === null || $row === false) {
                break;
            }

            if ($fetchMode === \PDO::FETCH_COLUMN) {
                $col = $args[0] ?? 0;
                $rows[] = $row[$col] ?? null;
            } else {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    public function fetchColumn(int $column = 0): mixed
    {
        $row = $this->result?->fetch_row();
        return $row[$column] ?? false;
    }

    public function rowCount(): int
    {
        return $this->affectedRows >= 0 ? $this->affectedRows : 0;
    }

    public function columnCount(): int
    {
        return $this->result ? $this->result->field_count : 0;
    }

    public function closeCursor(): bool
    {
        if ($this->result) {
            $this->result->free();
            $this->result = null;
        }
        if ($this->stmt) {
            $this->stmt->close();
            $this->stmt = null;
        }
        $this->bindings = [];
        return true;
    }

    public function errorCode(): ?string
    {
        $errno = $this->stmt ? $this->stmt->errno : $this->mysqli->errno;
        return $errno ? (string) $errno : null;
    }

    public function errorInfo(): array
    {
        if ($this->stmt) {
            return [$this->stmt->sqlstate ?? '', $this->stmt->errno, $this->stmt->error];
        }
        return [$this->mysqli->sqlstate ?? '', $this->mysqli->errno, $this->mysqli->error];
    }

    public function __destruct()
    {
        $this->closeCursor();
    }
}
