<?php

namespace ApiShop\Model;

use Adbar\Session;
use ApiShop\Config\Settings;

class Security {
 
    // Сообщение об Атаке или подборе токена
    public function token()
    {
        $config = (new Settings())->get();
        // Подключаем сессию
        $session = new Session($config['settings']['session']['name']);
        // Отправляем сообщение администратору
    }
 
    // Сообщение об Атаке или подборе csrf
    public function csrf()
    {
        $config = (new Settings())->get();
        // Подключаем сессию
        $session = new Session($config['settings']['session']['name']);
        // Отправляем сообщение администратору
    }
 
}
 