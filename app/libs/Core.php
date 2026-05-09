<?php

class Core
{
    public $controller = "Index";
    public $method = "index";
    public $params = [];
    public function __construct()
    {
        $route = $this->getUrl();
        if (isset($route[0])) {
            $searchcontroller = ucwords($route[0]);
            if ($searchcontroller === $this->controller || (class_exists(\Errors::class) && $searchcontroller === "Errors")) {
                \Controller::errors(404);
            }
            if (file_exists("../app/controllers/" . $searchcontroller . ".php")) {
                $this->controller = $searchcontroller;
                unset($route[0]);
            } else {
                \Controller::errors(404);
            }
            require_once "../app/controllers/" . $this->controller . ".php";
            $this->controller = new $this->controller;
            if (isset($route[1])) {
                if (method_exists($this->controller, $route[1])) {
                    $this->method = $route[1];
                    unset($route[1]);
                } else {
                    \Controller::errors(404);
                }
            }
            $this->params = $route ? array_values($route) : [];
            if (method_exists($this->controller, $this->method)) {
                $reflection = new \Reflectionmethod($this->controller, $this->method);
                $requiredparams = $reflection->getNumberOfRequiredParameters();
                $totalparams = $reflection->getNumberOfParameters();
                if ($totalparams === 0 && !empty($this->params)) {
                    \Controller::errors(404);
                }
                if (count($this->params) < $requiredparams) {
                    \Controller::errors(404);
                }
                if (count($this->params) > $totalparams) {
                    \Controller::errors(404);
                }
                call_user_func_array([$this->controller, $this->method], $this->params);
            } else {
                \Controller::errors(404);
            }
        } else {
            require_once "../app/controllers/" . $this->controller . ".php";
            $this->controller = new $this->controller;
            call_user_func_array([$this->controller, $this->method], $this->params);
        }
    }
    private function getUrl()
    {
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], "/");
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode("/", $url);
            return $url;
        }
        return [];
    }
}