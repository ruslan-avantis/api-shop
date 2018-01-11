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

namespace ApiShop\Resources;

use ApiShop\Config\Settings;
use RouterDb\Db;
use RouterDb\Router;

class Install {
 
    private $config;
 
    function __construct()
    {
        // Подключаем конфиг Settings\Config
        $config = (new Settings())->get();
        $this->config = $config;
    }

    public function stores_list()
    {
        // Ресурс к которому обращаемся
        $resource = "install_stores_list";
 
        // Отдаем роутеру RouterDb конфигурацию.
        $router = new Router($this->config);
        // Получаем название базы для указанного ресурса
        $db_name = $router->ping($resource);
        // Подключаемся к базе
        $db = new Db($db_name, $this->config);
        // Отправляем запрос и получаем данные
        $response = $db->get($resource);
 
        return $response["body"]["items"];
    }
 
    public function templates_list()
    {
        // Ресурс к которому обращаемся
        $resource = "install_templates_list";
 
        // Отдаем роутеру RouterDb конфигурацию.
        $router = new Router($this->config);
        // Получаем название базы для указанного ресурса
        $db_name = $router->ping($resource);
        // Подключаемся к базе
        $db = new Db($db_name, $this->config);
        // Отправляем запрос и получаем данные
        $response = $db->get($resource);
 
        return $response["body"]["items"];
    }
 
}
 