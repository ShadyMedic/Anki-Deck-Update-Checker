<?php


namespace Models;
use PDO;

class Db
{
    private const DB_HOST = 'localhost';
    private const DB_USER = 'root';
    private const DB_PASS = '';
    private const DB_NAME = 'ankipacks';
    private const DB_SETTINGS = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
        PDO::ATTR_EMULATE_PREPARES => false
    );
    
    /**
     * Connects to the database
     * @return PDO Database connection
     */
    public static function connect(): PDO
    {
        $pdo = new PDO('mysql:host='.self::DB_HOST.';dbname='.self::DB_NAME, self::DB_USER, self::DB_PASS, self::DB_SETTINGS);
        return $pdo;
    }
}
