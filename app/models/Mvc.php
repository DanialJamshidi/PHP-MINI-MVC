<?php

class Mvc
{
    private $db;

    public function __construct()
    {
        $this->db = new \Database;
    }

    public function getAll()
    {
        $stmt = $this->db->db->prepare("SELECT * FROM mvc ORDER BY id DESC");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
}
