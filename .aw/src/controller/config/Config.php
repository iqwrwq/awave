<?php

namespace awave\config;

use Symfony\Component\Dotenv\Dotenv;

class Config
{
    private Dotenv $dotEnv;
    private string $theme;

    public function __construct()
    {
        $this->dotEnv = new Dotenv();
        $this->dotEnv->load( getcwd() . '/.awave/src/.env');
        $this->theme = $_ENV['THEME'];
    }

    public function getTheme(){
        return $this->theme;
    }

    public function toggleTheme(){
        $_ENV['THEME'] = 'DARK';
    }

}

