<?php
class Errors extends \Controller
{
    static public function error($code)
    {
        switch ($code) {
            case 403:
                echo "ERROR || YOU DONT HAVE PERMISSION || 403";
                break;
            case 404:
                echo "ERROR || NOT FOUND || 404";
                break;
            case 500:
                echo "ERROR || CONNECTION || 500";
                break;
            default:
                http_response_code(500);
                break;
        }
        exit;
    }
}
