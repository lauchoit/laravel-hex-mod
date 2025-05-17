<?php

namespace Lauchoit\LaravelHexMod;

class Prueba {
    public function __construct()
    {
        // Constructor code here
    }

    public static function test(): string
    {
        dd('prueba');
        return "Hello from Prueba class!";
    }
}
