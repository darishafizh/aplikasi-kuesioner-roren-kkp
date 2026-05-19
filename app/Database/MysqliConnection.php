<?php

namespace App\Database;

use Illuminate\Database\MySqlConnection;
use Illuminate\Database\QueryException;

/**
 * Custom MySQL connection that uses mysqli instead of PDO.
 * This bypasses PDO SSL connection issues on servers where
 * mysqli can connect but PDO cannot.
 */
class MysqliConnection extends MySqlConnection
{
    private \mysqli $mysqli;

    public function __construct(array $config, string $database, string $tablePrefix)
    {
        $this->config = $config;
        $this->database = $database;
        $this->tablePrefix = $tablePrefix;

        // Create mysqli connection
        $this->mysqli = $this->createMysqliConnection($config);

        // Set parent properties via a dummy PDO closure (never actually called)
        // We override all methods that would use PDO
        parent::__construct(function () {
            throw new \RuntimeException('PDO should not be used in MysqliConnection');
        }, $database, $tablePrefix, $config);

        // Set default fetch mode
        $this->fetchMode = \PDO::FETCH_OBJ;
    }

    private function createMysqliConnection(array $config): \mysqli
    {
        $mysqli = new \mysqli();
        $mysqli->options(42, 1); // MYSQL_OPT_SSL_MODE = 42, SSL_MODE_DISABLED = 1

        $host = $config['host'] ?? '127.0.0.1';
        $username = $config['username'] ?? 'root';
        $password = $config['password'] ?? '';
        $database = $config['database'] ?? '';
        $port = (int) ($config['port'] ?? 3306);
        $socket = $config['unix_socket'] ?? null;

        $connected = @$mysqli->real_connect(
            $host,
            $username,
            $password,
            $database,
            $port,
            $socket,
            MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT
        );

        if (!$connected || $mysqli->connect_error) {
            throw new \RuntimeException(
                'mysqli connection failed: ' . ($mysqli->connect_error ?? 'Unknown error')
            );
        }

        // Set charset
        $charset = $config['charset'] ?? 'utf8mb4';
        $mysqli->set_charset($charset);

        // Set collation
        $collation = $config['collation'] ?? 'utf8mb4_unicode_ci';
        $mysqli->query("SET NAMES '{$charset}' COLLATE '{$collation}'");

        // Set timezone if needed
        if (isset($config['timezone'])) {
            $mysqli->query("SET time_zone = '{$config['timezone']}'");
        }

        // Set strict mode
        if ($config['strict'] ?? false) {
            $mysqli->query("SET SESSION sql_mode='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
        }

        return $mysqli;
    }

    /**
     * Override prepared() to accept our custom statement proxy.
     */
    protected function prepared($statement)
    {
        if ($statement instanceof MysqliStatementProxy) {
            $statement->setFetchMode($this->fetchMode);
        }
        return $statement;
    }

    /**
     * Run a select statement against the database.
     */
    public function select($query, $bindings = [], $useReadPdo = true)
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            $statement = $this->prepared(new MysqliStatementProxy($this->mysqli, $query));
            $this->bindMysqliValues($statement, $this->prepareBindings($bindings));
            $statement->execute();
            $results = $statement->fetchAll();
            $statement->closeCursor();
            return $results;
        });
    }

    /**
     * Run a select statement and return a generator.
     */
    public function cursor($query, $bindings = [], $useReadPdo = true)
    {
        $statement = $this->run($query, $bindings, function ($query, $bindings) {
            $statement = $this->prepared(new MysqliStatementProxy($this->mysqli, $query));
            $this->bindMysqliValues($statement, $this->prepareBindings($bindings));
            $statement->execute();
            return $statement;
        });

        while ($record = $statement->fetch()) {
            yield $record;
        }
    }

    /**
     * Execute an SQL statement and return the boolean result.
     */
    public function statement($query, $bindings = [])
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            $statement = new MysqliStatementProxy($this->mysqli, $query);
            $this->bindMysqliValues($statement, $this->prepareBindings($bindings));
            return $statement->execute();
        });
    }

    /**
     * Run an SQL statement and get the number of rows affected.
     */
    public function affectingStatement($query, $bindings = [])
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            $statement = new MysqliStatementProxy($this->mysqli, $query);
            $this->bindMysqliValues($statement, $this->prepareBindings($bindings));
            $statement->execute();
            $count = $statement->rowCount();
            $this->recordsHaveBeenModified($count > 0);
            $statement->closeCursor();
            return $count;
        });
    }

    /**
     * Run a raw, unprepared query against the PDO connection.
     */
    public function unprepared($query)
    {
        return $this->run($query, [], function ($query) {
            $result = $this->mysqli->query($query);
            if ($result === false) {
                return false;
            }
            $this->recordsHaveBeenModified(
                ($count = $this->mysqli->affected_rows) > 0
            );
            if ($result instanceof \mysqli_result) {
                $result->free();
            }
            return true;
        });
    }

    /**
     * Bind values to their parameters in the given statement.
     */
    private function bindMysqliValues(MysqliStatementProxy $statement, array $bindings): void
    {
        foreach ($bindings as $key => $value) {
            $type = match (true) {
                is_int($value) => \PDO::PARAM_INT,
                is_resource($value) => \PDO::PARAM_LOB,
                default => \PDO::PARAM_STR,
            };
            $statement->bindValue(
                is_string($key) ? $key : $key + 1,
                $value,
                $type
            );
        }
    }

    /**
     * Start a new database transaction.
     */
    public function beginTransaction()
    {
        $this->transactions++;
        if ($this->transactions === 1) {
            $this->mysqli->begin_transaction();
        } elseif ($this->queryGrammar->supportsSavepoints()) {
            $this->mysqli->query(
                $this->queryGrammar->compileSavepoint('trans' . $this->transactions)
            );
        }
        $this->fireConnectionEvent('beganTransaction');
    }

    /**
     * Commit the active database transaction.
     */
    public function commit()
    {
        if ($this->transactions === 1) {
            $this->mysqli->commit();
        }
        $this->transactions = max(0, $this->transactions - 1);
        $this->fireConnectionEvent('committed');
    }

    /**
     * Rollback the active database transaction.
     */
    public function rollBack($toLevel = null)
    {
        $toLevel = is_null($toLevel) ? $this->transactions - 1 : $toLevel;

        if ($toLevel < 0 || $toLevel >= $this->transactions) {
            return;
        }

        if ($toLevel === 0) {
            $this->mysqli->rollback();
        } elseif ($this->queryGrammar->supportsSavepoints()) {
            $this->mysqli->query(
                $this->queryGrammar->compileSavepointRollBack('trans' . ($toLevel + 1))
            );
        }

        $this->transactions = $toLevel;
        $this->fireConnectionEvent('rollingBack');
    }

    /**
     * Get the mysqli instance.
     */
    public function getMysqli(): \mysqli
    {
        return $this->mysqli;
    }

    /**
     * Reconnect to the database if a PDO connection is missing.
     */
    public function reconnectIfMissingConnection()
    {
        if (!$this->mysqli->ping()) {
            $this->mysqli = $this->createMysqliConnection($this->config);
        }
    }

    /**
     * Disconnect from the underlying connection.
     */
    public function disconnect()
    {
        $this->mysqli->close();
    }

    /**
     * Get the server version for the connection.
     */
    public function getServerVersion(): string
    {
        return $this->mysqli->server_info;
    }
}
