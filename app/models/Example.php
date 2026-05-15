<?php

class Example
{
    private $db;

    public function __construct()
    {
        $this->db = new \Database;
    }

    public function getModels()
    {
        $stmt = $this->db->db->prepare("SELECT * FROM table ORDER BY column DESC");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }

    public function getModel($column)
    {
        $stmt = $this->db->db->prepare("SELECT * FROM table WHERE column = :column");
        $stmt->execute(['column' => $column]);
        return $stmt->fetch(\PDO::FETCH_OBJ);
    }

    public function createModel($column)
    {
        $stmt = $this->db->db->prepare("INSERT INTO table (column) VALUES (:column)");
        $stmt->execute(['column' => $column]);
    }

    public function updateModel($column1, $column2)
    {
        $stmt = $this->db->db->prepare("UPDATE table SET column1 = :column1 WHERE column2 = :column2");
        $stmt->execute(['column1' => $column1, 'column2' => $column2]);
        return $stmt->fetch(\PDO::FETCH_OBJ);
    }

    public function deleteModel($column)
    {
        $stmt = $this->db->db->prepare("DELETE FROM table WHERE column = :column");
        $stmt->execute(['column' => $column]);
    }
}
