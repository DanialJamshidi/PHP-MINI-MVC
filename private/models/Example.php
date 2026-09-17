<?php

class Example extends Model
{
    private static string $table = 'example';
    // Auto Name: static::name();
    public static function getAll()
    {
        return DB::all(self::$table);
    }
    public static function getOne($id)
    {
        return DB::find(self::$table, $id);
    }
    public static function create($data)
    {
        return DB::add(self::$table, $data);
    }
    public static function update($id, $data)
    {
        return DB::update(self::$table, $id, $data);
    }
    public static function deleteAll()
    {
        return DB::deleteAll(self::$table);
    }
    public static function delete($id)
    {
        return DB::delete(self::$table, $id);
    }
}