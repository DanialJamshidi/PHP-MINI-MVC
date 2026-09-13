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
                Errors::error(404);
            }

            if (file_exists("../private/controllers/" . $searchcontroller . ".php")) {
                $this->controller = $searchcontroller;
                unset($route[0]);
            } else {
                Errors::error(404);
            }

            require_once "../private/controllers/" . $this->controller . ".php";

            $this->controller = new $this->controller;

            if (isset($route[1])) {
                $methodParts = [];
                $methodFound = false;

                foreach ($route as $key => $value) {
                    $methodParts[] = $value;

                    $normalMethod = implode('__', $methodParts);
                    $postMethod = '_' . $normalMethod;

                    if (method_exists($this->controller, $normalMethod)) {
                        $this->method = $normalMethod;
                        $methodFound = true;
                    } elseif (method_exists($this->controller, $postMethod)) {
                        $this->method = $postMethod;
                        $methodFound = true;
                    } else {
                        continue;
                    }

                    foreach ($methodParts as $methodKey => $methodValue) {
                        unset($route[$methodKey + 1]);
                    }

                    break;
                }

                if (!$methodFound) {
                    Errors::error(404);
                }
            }

            $this->params = $route ? array_values($route) : [];

            if (method_exists($this->controller, $this->method)) {
                $reflection = new Reflectionmethod($this->controller, $this->method);
                $requiredparams = $reflection->getNumberOfRequiredParameters();
                $totalparams = $reflection->getNumberOfParameters();

                if ($totalparams === 0 && !empty($this->params)) {
                    Errors::error(404);
                }

                if (count($this->params) < $requiredparams) {
                    Errors::error(404);
                }

                if (count($this->params) > $totalparams) {
                    Errors::error(404);
                }

                if (isset($this->method[0]) && $this->method[0] === '_' && (!isset($this->method[1]) || $this->method[1] !== '_')) {
                    Controller::post();
                }

                call_user_func_array([$this->controller, $this->method], $this->params);
            } else {
                Errors::error(404);
            }
        } else {
            require_once "../private/controllers/" . $this->controller . ".php";

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