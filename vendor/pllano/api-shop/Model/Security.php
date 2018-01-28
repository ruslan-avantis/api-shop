<?php
/**
 * This file is part of the API SHOP
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/pllano/api-shop
 * @version 1.0.1
 * @package pllano.api-shop
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
namespace ApiShop\Model;
 
use ApiShop\Config\Settings;

class Security {
 
    // Сообщение об Атаке или подборе токена
    public function token()
    {
        $config = (new Settings())->get();
        // Подключаем сессию, берет название класса из конфигурации
        $session = new $config['vendor']['session']($config['settings']['session']['name']);
        // Отправляем сообщение администратору
    }
 
    // Сообщение об Атаке или подборе csrf
    public function csrf()
    {
        $config = (new Settings())->get();
        // Подключаем сессию, берет название класса из конфигурации
        $session = new $config['vendor']['session']($config['settings']['session']['name']);
        // Отправляем сообщение администратору
    }
 
}
 