<?php

namespace App\Providers;

use App\Database\MysqliConnection;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Connection;

/**
 * Registers a custom 'mysqli' database driver for Laravel.
 * This driver uses mysqli instead of PDO to bypass SSL connection issues.
 */
class MysqliDatabaseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Connection::resolverFor('mysqli', function ($connection, $database, $prefix, $config) {
            return new MysqliConnection($config, $database, $prefix);
        });
    }

    public function boot(): void
    {
        //
    }
}
