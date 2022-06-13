<?php

namespace awave\config;

use Symfony\Component\Dotenv\Dotenv;

class Config
{
    private Dotenv $dotEnv;

    public function __construct()
    {
        //$this->dotEnv = new Dotenv();
        //$this->dotEnv->load( getcwd() . '/.aw/src/.env');
    }
}

