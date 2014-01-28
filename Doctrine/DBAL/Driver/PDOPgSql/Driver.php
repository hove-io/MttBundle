<?php

namespace CanalTP\MethBundle\Doctrine\DBAL\Driver\PDOPgSql;

class Driver extends \Doctrine\DBAL\Driver\PDOPgSql\Driver implements \Doctrine\DBAL\Driver
{
    public function connect(
        array $params,
        $username = null,
        $password = null,
        array $driverOptions = array()
    )
    {
        $searchPath = $driverOptions['search_path'];
        unset($driverOptions['search_path']);

        $connection = new \Doctrine\DBAL\Driver\PDOConnection(
            $this->_constructPdoDsn($params),
            $username,
            $password,
            $driverOptions
        );

        $connection->exec("SET SEARCH_PATH TO {$searchPath};");

        return $connection;
    }

    /**
     * Constructs the Postgres PDO DSN.
     *
     * @return string The DSN.
     */
    protected function _constructPdoDsn(array $params)
    {
        $dsn = 'pgsql:';
        if (isset($params['host']) && $params['host'] != '') {
            $dsn .= 'host=' . $params['host'] . ' ';
        }
        if (isset($params['port']) && $params['port'] != '') {
            $dsn .= 'port=' . $params['port'] . ' ';
        }
        if (isset($params['dbname'])) {
            $dsn .= 'dbname=' . $params['dbname'] . ' ';
        }

        return $dsn;
    }
}
