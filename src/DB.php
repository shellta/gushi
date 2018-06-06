<?php

namespace App;

class DB
{
    private static $instance;

    private $dsn;

    private $username;

    private $password;

    /**
     * @var \PDO
     */
    private $pdo;

    private function __construct()
    {
        $this->dsn = 'mysql:dbname=gushi;host=127.0.0.1;port=3306;charset=UTF8';
        $this->username = 'root';
        $this->password = 'password';

        $this->pdo = new \PDO($this->dsn, $this->username, $this->password);
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function insert(array $values)
    {
        $v = '';

        foreach ($values as $value) {
            $v .= "('{$value['title']}', '{$value['dynasty']}', '{$value['author']}', '{$value['content_text']}', '{$value['content_html']}', '{$value['tags']}'),";
        }

        $v = rtrim($v, ',');

        $sql = "INSERT INTO `gushi` (`title`, `dynasty`, `author`, `content_text`, `content_html`, `tags`) VALUES {$v}";

        $ps = self::getInstance()->pdo->prepare($sql);

        $ps->execute();
    }
}
