<?php

class Controller
{
    static protected function view($view, $data = [])
    {
        $path = Config::PRIVATEROOT . "/views/" . periodPath($view) . ".php";

        extract($data, EXTR_SKIP);

        if (file_exists($path)) {
            require_once $path;
        } else {
            Errors::error(404);
        }
    }

    static protected function authorization($sessionName)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (
            !isset($_SESSION[$sessionName]) ||
            empty($_SESSION[$sessionName])
        ) {
            Errors::error(403);
        }

        return true;
    }

    static public function post()
    {
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            Errors::error(403);
        }

        validateToken();

        return true;
    }
}