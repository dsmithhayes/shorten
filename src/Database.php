<?php

namespace Shorten;

use PDO;

class Database
{
    /**
     * @var string
     */
    private $driver;

    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    /**
     * @var string
     */
    private $database;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * Database constructor.
     * @param array $conf
     *      Each key in the $conf array corresponds to a property of this object
     */
    public function __construct(array $conf)
    {
        foreach ($conf as $prop => $val) {
            $this->{$prop} = $val;
        }
    }

    /**
     * @return PDO
     */
    public function getPdo(): PDO
    {
        return new PDO($this->buildDsn(), $this->username, $this->password);
    }

    /**
     * @return string
     */
    public function getDsn(): string
    {
        return $this->buildDsn();
    }

    /**
     * @return string
     */
    protected function buildDsn(): string
    {
        $dsn = "{$this->driver}:host={$this->host}";

        if ($this->port) {
            $dsn .= ";port={$this->port}";
        }

        $dsn .= ";dbname={$this->database}";

        return $dsn;
    }
}