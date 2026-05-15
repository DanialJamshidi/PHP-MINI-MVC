<?php

class Controller
{
    static public function model($name, $route = [])
    {
        $routes = trim(!empty($route) ? implode("/", $route) . "/" : "");
        $path = \Config::APPROOT . "/models/" . $routes . periodPath(ucwords($name)) . ".php";

        if (file_exists($path)) {
            require_once $path;
            return new $name;
        } else {
            Controller::errors(404);
        }
    }
    static public function view($view, $data = [])
    {
        $path = \Config::APPROOT . "/views/" . periodPath($view) . ".php";
        extract($data, EXTR_SKIP);
        if (file_exists($path)) {
            require_once $path;
        } else {
            Controller::errors(404);
        }
    }
    static public function post()
    {
        if (strtoupper($_SERVER["REQUEST_METHOD"]) !== "POST") {
            Controller::errors(403);
        }
        return true;
    }
    static public function errors($code)
    {
        $path = \Config::APPROOT . "/controllers/Errors.php";
        if (file_exists($path)) {
            require_once $path;
            \Errors::error($code);
        } else {
            http_response_code($code);
            exit;
        }
    }
}
