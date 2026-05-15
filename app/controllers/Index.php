<?php

class Index extends \Controller
{
    public function index()
    {
        return \Controller::view("welcome");
    }
}