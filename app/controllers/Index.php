<?php

class Index extends \Controller
{
    public function index()
    {
        $mvc = \Controller::model("Mvc")->getAll();
        return \Controller::view("index", compact("mvc"));
    }
}