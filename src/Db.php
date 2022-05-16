<?php

namespace PhpMysqlDb;

use Exception;

/**
 * Database connector
 *
 * @author Nelson Dias <nelson@websvc.net>
 *
 * @file Db.php
 * @creation Apr 22, 2017
 * https://github.com/erangalp/database-tutorial
 * http://www.binpress.com/tutorial/using-php-with-mysql-the-right-way/17
 *
 */
class Db
{
    /**
     * MySQLi resource
     *
     * @var object
     */
    protected $connection;

    /**
     * Log handler class
     *
     * @var object
     */
    protected $logger = false;

    /**
     * MySQL hostname
     *
     * Include port if required
     *
     * @var string
     */
    protected $host = null;

    /**
     * MySQL database name
     *
     * @var string
     */
    protected $database = null;

    /**
     * MySQL username
     *
     * @var string
     */
    protected $username = null;

    /**
     * MySQL password
     *
     * @var string
     */
    protected $password = null;

    /**
     * MySQL server port
     *
     * @var int
     */
    protected $port = 3306;

    /**
     * MySQL operation affected rows
     *
     * @var int
     */
    protected $affected_rows = 0;

    /**
     * Initialize DB connection
     */
    public function __construct($host, $username, $password, $database, $port = 3306)
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
        $this->port = $port;

        $this->openDb();
    }

    /**
     * Open a connection to database
     *
     * @throws Exception
     * @throws \InvalidArgumentException
     */
    protected function openDb()
    {
        try {
            if (empty($this->host) || empty($this->username) || empty($this->database)) {
                $this->addLog('Missing DB connection data', 'CRITICAL', [__FUNCTION__]);
                throw new \InvalidArgumentException("Missing DB connection data");
            }

            $this->connection = mysqli_init();

            try {
                $this->connection->real_connect(
                    $this->host,
                    $this->username,
                    $this->password,
                    $this->database,
                    $this->port
                );
                $this->connection->autocommit(true);
            } catch (Exception $e) {
                throw new Exception('Error on DB connect ' . $e);
            }
            if (!$this->connection || $this->connection->errno != 0) {
                $this->addLog('Error on DB connect', 'CRITICAL', [__FUNCTION__]);
                throw new Exception('Error on DB connect');
            }

            $this->connection->set_charset("utf8");
        } catch (Exception $e) {
            $this->addLog('Error on DB connect: ' . $e, 'CRITICAL', [__FUNCTION__]);
            throw new Exception('Error on DB connect :' . $e);
        }
    }

    /**
     * Query the database
     *
     * @param $query The query string
     * @return mixed The result of the mysqli::query() function
     */
    public function query($query)
    {
        $result = $this->connection->query($query);
        if ($this->connection->error) {
            $msg = "Failed to execute query: " . $this->connection->error;
            $this->addLog($msg, 'CRITICAL', [__FUNCTION__]);
            throw new Exception($msg);
        }
        $this->affected_rows = $this->connection->affected_rows;
        $this->addLog($query, 'DEBUG', [__CLASS__, __FUNCTION__]);
        return $result;
    }

    /**
     * An alias for Db::query()
     *
     * @param $query The query string
     * @return mixed The result of the mysqli::query() function
     */
    public function exec($query)
    {
        return $this->query($query);
    }

    /**
     * Fetch rows from the database (SELECT query)
     *
     * @param $query The query string
     * @return bool False on failure / array Database rows on success
     */
    public function select($query)
    {
        $rows = array();
        $result = $this->query($query);
        if ($result === false) {
            return false;
        }
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * Fetch the last error from the database
     *
     * @return string Database error message
     */
    public function error()
    {
        return $this->connection->error;
    }

    /**
     * Quote and escape value for use in a database query
     *
     * @param string $value The value to be quoted and escaped
     * @return string The quoted and escaped string
     */
    public function quote($value)
    {
        return "'" . $this->connection->real_escape_string($value) . "'";
    }

    /**
     * Escape value for use in a database query
     *
     * @param string $value The value to be escaped
     * @return string The escaped string
     */
    public function escape($value)
    {
        return $this->connection->real_escape_string($value);
    }

    /**
     * Get mysql last insert id
     *
     * @return integer
     */
    public function getLastInsertId()
    {
        $lastId = mysqli_insert_id($this->connection);

        return $lastId;
    }

    /**
     * Defines a log handler for the class
     *
     * @param object $logHandler
     */
    public function setLogger($logHandler)
    {
        $this->logger = $logHandler;
    }

    /**
     * Logs a message
     *
     * Ignores if there is no log handler defined
     *
     * @param string $msg
     * @param string $mode
     * @param array $context
     */
    private function addLog($msg, $mode = 'DEBUG', $context = [])
    {
        if ($this->logger) {
            $this->logger->addLog($mode, $msg, $context);
        }
    }

    /**
     * Closes mysql Connection
     *
     * @return void
     */
    public function close()
    {
        @mysqli_close($this->connection);
    }
}
