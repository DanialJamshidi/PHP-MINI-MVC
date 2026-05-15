<?php
// Change "WEB" In Bootstrap

class Config
{
    // Database
    public const DB_LOCALHOST = "localhost";
    public const DB_USER = "root";
    public const DB_PASSWORD = "";
    public const DB_NAME = "mvc";

    // Default
    public const APPROOT = "../app/";
    public const PUBLICROOT = "/";


    // --------- Auto fill ---------


    
    static public function URLROOT()
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            ? "https://"
            : "http://";
        $host = $_SERVER['HTTP_HOST'];
        $uri = $_SERVER['REQUEST_URI'];
        return $protocol . $host . $uri;
    }
}
