<?php

class Index extends Controller
{
    public function index()
    {
        return static::view("welcome");
    }
}