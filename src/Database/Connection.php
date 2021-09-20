<?php

namespace App\Database;

use PDO;

abstract class Connection
{
    /**
     * @var PDO
     */
    private static $conn;

    public static function getConn()
    {
        if(self::$conn == null){
            self::$conn = new PDO('mysql: host=localhost; dbname=pastelaria', 'root', '');
        }
        return self::$conn;
    }
}