<?php
namespace PsyNexus\Database;
use PDO;
use PDOException;
class Connection
{
    private static $instance = null;
    private $pdo;
    private function __construct()
    {
        $host = 'localhost';
        $db = 'psy_nexus_generator';
        $user = 'psymne_db';
        $pass = 'zEw7j9Y3qLp2rT4sK5fH8gV6';
        $charset = 'utf8mb4';
        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
    }
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Connection();
        }
        return self::$instance;
    }
    public function __call($method, $args)
    {
        return call_user_func_array([$this->pdo, $method], $args);
    }
}
