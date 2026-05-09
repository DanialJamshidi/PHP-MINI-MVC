<?php

class Database
{
    private $localhost = \Config::DB_LOCALHOST;
    private $user = \Config::DB_USER;
    private $password = \Config::DB_PASSWORD;
    private $name = \Config::DB_NAME;
    public $db;

    public function __construct()
    {
        try {
            $dsn = "mysql:host=$this->localhost;dbname=$this->name;";
            $this->db = new PDO($dsn, $this->user, $this->password);
            $this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            \Controller::errors(500);
        }
    }
}