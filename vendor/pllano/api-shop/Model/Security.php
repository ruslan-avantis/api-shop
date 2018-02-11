<?php
/**
 * This file is part of the API SHOP
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/pllano/api-shop
 * @version 1.1.0
 * @package pllano.api-shop
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
namespace ApiShop\Model;
 
use Slim\Http\Request;
use Slim\Http\Response;
 
use ApiShop\Config\Settings;
 
class Security {
 
    function __construct()
    {
        $config = (new Settings())->get();
        $this->config = $config['hooks'];
    }
 
    // Сообщение об Атаке или подборе токена
    public function token(Request $request, Response $response)
    {
        // Подключаем сессию, берет название класса из конфигурации
        $session = new $this->config['vendor']['session']($this->config['settings']['session']['name']);
 
        // Отправляем сообщение администратору
    }
 
    // Сообщение об Атаке или подборе csrf
    public function csrf(Request $request, Response $response)
    {
        // Подключаем сессию, берет название класса из конфигурации
        $session = new $this->config['vendor']['session']($this->config['settings']['session']['name']);
 
        // Отправляем сообщение администратору
    }
 
}
 