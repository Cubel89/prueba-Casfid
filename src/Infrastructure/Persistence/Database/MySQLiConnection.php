<?php

namespace App\Infrastructure\Persistence\Database;

class MySQLiConnection
{
    private static ?MySQLiConnection $instance = null;
    private \mysqli $connection;

    private function __construct()
    {
        $host = getenv('DB_HOST');
        $dbname = getenv('DB_NAME');
        $username = getenv('DB_USER');
        $password = getenv('DB_PASSWORD');

        error_log("DB Connection: Host=$host, DB=$dbname, User=$username");

        try {
            $this->connection = new \mysqli($host, $username, $password, $dbname);

            if ($this->connection->connect_error) {
                throw new \RuntimeException('Connection error: ' . $this->connection->connect_error);
            }

            $this->connection->set_charset('utf8mb4');

        } catch (\Exception $e) {
            error_log('Database connection error: ' . $e->getMessage());
            throw new \RuntimeException('Error connecting to database. See error log for details.');
        }
    }

    private function __clone() {}

    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getConnection(): \mysqli
    {
        return $this->connection;
    }

    public function escape($value): string
    {
        return $this->connection->real_escape_string($value);
    }

    public function query($sql)
    {
        $result = $this->connection->query($sql);

        if ($result === false) {
            throw new \RuntimeException('Query error: ' . $this->connection->error . ' SQL: ' . $sql);
        }

        return $result;
    }

    public function lastInsertId(): int
    {
        return $this->connection->insert_id;
    }

    public function affectedRows(): int
    {
        return $this->connection->affected_rows;
    }

    public function getRow($sql)
    {
        $result = $this->query($sql);

        if ($result->num_rows === 0) {
            return null;
        }

        return $result->fetch_assoc();
    }

    public function getAllRows($sql): array
    {
        $result = $this->query($sql);
        $rows = [];

        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        return $rows;
    }
}