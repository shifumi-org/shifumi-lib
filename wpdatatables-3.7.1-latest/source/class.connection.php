<?php

defined('ABSPATH') or die('Access denied.');

class Connection {
    public static $MYSQL = 'mysql';
    public static $MSSQL = 'mssql';
    public static $POSTGRESQL = 'postgresql';

    public static function create($id = null, $host = null, $database = null, $user = null, $password = null, $port = null, $vendor = null, $driver = null) {
        if ($id) {
            foreach (self::getAll() as $connection) {
                if ($connection['id'] === $id) {
                    $host = $connection['host'];
                    $database = $connection['database'];
                    $user = $connection['user'];
                    $password = $connection['password'];
                    $port = $connection['port'];
                    $vendor = $connection['vendor'];
                    $driver = $connection['driver'];
                }
            }
        }

        switch ($vendor) {
            case (self::$MSSQL):
                if (isset($driver) && $driver == 'sqlsrv') {
                    return new PDOSql($vendor, "$driver:Server=$host,$port;Database=$database", $user, $password);
                } elseif (isset($driver) && $driver == 'dblib') {
                    return new PDOSql($vendor, "$driver:version=7.0;charset=UTF-8;host=$host:$port;dbname=$database", $user, $password);
                } elseif (isset($driver) && $driver == 'odbc') {
                    return new PDOSql($vendor, "$driver:DRIVER={ODBC Driver 17 for SQL Server};Server=$host;Database=$database", $user, $password);
                }

            case (self::$POSTGRESQL):
                return new PDOSql($vendor, "pgsql:host=$host;port=$port;dbname=$database", $user, $password);

            default:
                return new PDTSql($host, $database, $user, $password, $port);
                //return new PDOSql($vendor, "mysql:host=$host;port=$port;dbname=$database", $user, $password);
        }
    }

    /**
     * Return left quote for table column based on vendor
     * @param String $vendor of the connection
     * @return String
     */
    public static function getLeftColumnQuote($vendor) {
        if ($vendor === Connection::$MYSQL) {
            return '`';
        }

        if ($vendor === Connection::$MSSQL) {
            return '[';
        }

        if ($vendor === Connection::$POSTGRESQL) {
            return '"';
        }
    }

    /**
     * Return right quote for table column based on vendor
     * @param String $vendor of the connection
     * @return String
     */
    public static function getRightColumnQuote($vendor) {
        if ($vendor === Connection::$MYSQL) {
            return '`';
        }

        if ($vendor === Connection::$MSSQL) {
            return ']';
        }

        if ($vendor === Connection::$POSTGRESQL) {
            return '"';
        }
    }

    /**
     * Checks if separate connection is used
     * @param String $id of the connection, in case of empty string, connection is WP MySql
     * @return boolean
     * @throws Exception
     */
    public static function isSeparate($id = null) {
        if ($id && get_option('wdtUseSeparateCon')) {
            if ($id) {
                foreach (self::getAll() as $connection) {
                    if ($connection['id'] === $id) {
                        return true;
                    }
                }

                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * Get type of DB (MySQL, MSSQL, PostgreSQL)
     * @param String $id of the connection, in case of empty string, connection is WP MySql
     * @return String
     * @throws Exception
     */
    public static function getVendor($id = null) {
        if ($id) {
            foreach (self::getAll() as $connection) {
                if ($connection['id'] === $id) {
                    return $connection['vendor'];
                }
            }

            throw new \Exception("Connection '$id' is not defined in Settings");
        }

        return self::$MYSQL;
    }

    /**
     * Get name of DB
     * @param String $id of the connection, in case of empty string, connection is WP MySql
     * @return String
     * @throws Exception
     */
    public static function getName($id = null) {
        if ($id) {
            foreach (self::getAll() as $connection) {
                if ($connection['id'] === $id) {
                    return $connection['name'];
                }
            }

            return 'Unknown Connection';
        }

        return 'WP Connection';
    }

    /**
     * Get all connections created in Settings
     */
    public static function getAll() {
        return (array)json_decode(get_option('wdtSeparateCon'), true);
    }

    /**
     * Checks if separate connection is enabled
     * @return boolean
     */
    public static function enabledSeparate() {
        return get_option('wdtUseSeparateCon') ? true : false;
    }

    /**
     * Save all connections created in Settings
     *
     * @param $connections
     */
    public static function saveAll($connections) {
        update_option('wdtUseSeparateCon', true);
        update_option('wdtSeparateCon', $connections);
    }
}

?>
