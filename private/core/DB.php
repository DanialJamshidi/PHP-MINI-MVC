<?php

class DB
{
    private static $db;

    public static function connect()
    {
        if (!self::$db) {
            try {
                self::$db = new PDO(
                    "mysql:host=" . Config::DB_LOCALHOST .
                        ";dbname=" . Config::DB_NAME .
                        ";charset=utf8mb4",
                    Config::DB_USER,
                    Config::DB_PASSWORD,
                    [
                        PDO::ATTR_EMULATE_PREPARES => false,
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
                    ]
                );
            } catch (PDOException $e) {
                Errors::error(500);
                exit;
            }
        }

        return self::$db;
    }

    public static function get($sql, $params = [])
    {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public static function findUnique($sql, $params = [])
    {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);

        $result = $stmt->fetch();

        if (!$result) {
            Errors::error(404);
            exit;
        }

        return $result;
    }

    public static function all($table, $sort = "ASC")
    {
        $table = dbIdentifier($table);

        $sort = strtoupper($sort) === "ASC" ? "ASC" : "DESC";

        $stmt = self::connect()->prepare(
            "SELECT * FROM {$table} ORDER BY `id` {$sort}"
        );

        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function find($table, $id)
    {
        $table = dbIdentifier($table);

        $stmt = self::connect()->prepare(
            "SELECT * FROM {$table} WHERE `id` = ? LIMIT 1"
        );

        $stmt->execute([$id]);

        $result = $stmt->fetch();

        if (!$result) {
            Errors::error(404);
            exit;
        }

        return $result;
    }

    public static function add($table, $data)
    {
        if (empty($data)) {
            throw new InvalidArgumentException('Data cannot be empty.');
        }

        $table = dbIdentifier($table);

        $columns = array_map(
            fn($column) => dbIdentifier($column),
            array_keys($data)
        );

        $columns = implode(', ', $columns);

        $values = implode(
            ', ',
            array_fill(0, count($data), '?')
        );

        $stmt = self::connect()->prepare(
            "INSERT INTO {$table} ({$columns}) VALUES ({$values})"
        );

        $stmt->execute(array_values($data));

        return self::connect()->lastInsertId();
    }

    public static function update($table, $id, $data)
    {
        if (empty($data)) {
            throw new InvalidArgumentException('Data cannot be empty.');
        }

        if (!self::exists($table, $id)) {
            Errors::error(404);
            exit;
        }

        $table = dbIdentifier($table);

        $set = implode(
            ', ',
            array_map(
                fn($column) => dbIdentifier($column) . ' = ?',
                array_keys($data)
            )
        );

        $stmt = self::connect()->prepare(
            "UPDATE {$table} SET {$set} WHERE `id` = ?"
        );

        $stmt->execute([
            ...array_values($data),
            $id
        ]);

        return $stmt->rowCount();
    }

    public static function delete($table, $id)
    {
        if (!self::exists($table, $id)) {
            Errors::error(404);
            exit;
        }

        $table = dbIdentifier($table);

        $stmt = self::connect()->prepare(
            "DELETE FROM {$table} WHERE `id` = ?"
        );

        $stmt->execute([$id]);

        return $stmt->rowCount();
    }

    public static function deleteAll($table)
    {
        $table = dbIdentifier($table);

        $stmt = self::connect()->prepare(
            "DELETE FROM {$table}"
        );

        $stmt->execute();

        return $stmt->rowCount();
    }

    public static function count($table)
    {
        $table = dbIdentifier($table);

        $stmt = self::connect()->prepare(
            "SELECT COUNT(*) FROM {$table}"
        );

        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public static function exists($table, $id)
    {
        $table = dbIdentifier($table);

        $stmt = self::connect()->prepare(
            "SELECT `id` FROM {$table} WHERE `id` = ? LIMIT 1"
        );

        $stmt->execute([$id]);

        return $stmt->fetch() !== false;
    }

    public static function OneToOne($table, $foreignKey, $localValue)
    {
        $table = dbIdentifier($table);
        $foreignKey = dbIdentifier($foreignKey);

        $stmt = self::connect()->prepare(
            "SELECT *
             FROM {$table}
             WHERE {$foreignKey} = ?
             LIMIT 1"
        );

        $stmt->execute([$localValue]);

        return $stmt->fetch();
    }

    public static function OneToMany($table, $foreignKey, $localValue)
    {
        $table = dbIdentifier($table);
        $foreignKey = dbIdentifier($foreignKey);

        $stmt = self::connect()->prepare(
            "SELECT *
             FROM {$table}
             WHERE {$foreignKey} = ?"
        );

        $stmt->execute([$localValue]);

        return $stmt->fetchAll();
    }

    public static function ManyToOne($table, $foreignKey, $localValue)
    {
        $table = dbIdentifier($table);

        $stmt = self::connect()->prepare(
            "SELECT *
             FROM {$table}
             WHERE `id` = ?
             LIMIT 1"
        );

        $stmt->execute([$localValue]);

        return $stmt->fetch();
    }

    public static function ManyToMany(
        $table,
        $pivotTable,
        $foreignKey,
        $relatedKey,
        $localValue
    ) {
        $table = dbIdentifier($table);
        $pivotTable = dbIdentifier($pivotTable);
        $foreignKey = dbIdentifier($foreignKey);
        $relatedKey = dbIdentifier($relatedKey);

        $stmt = self::connect()->prepare(
            "SELECT {$table}.*
             FROM {$table}
             INNER JOIN {$pivotTable}
             ON {$table}.`id` = {$pivotTable}.{$relatedKey}
             WHERE {$pivotTable}.{$foreignKey} = ?"
        );

        $stmt->execute([$localValue]);

        return $stmt->fetchAll();
    }
}