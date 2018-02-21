<?php
/**
 * This file is part of the {API}$hop
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/pllano/api-shop
 * @version 1.1.1
 * @package pllano.api-shop
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
namespace ApiShop\Model;
 
use Pllano\RouterDb\Db;
use Pllano\RouterDb\Router;
 
class Install {
 
    private $config;
 
    function __construct($config)
    {
        $this->config = $config;
    }

    public function stores_list()
    {
        // Ресурс к которому обращаемся
        $resource = "stores_list";
 
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
 
    public function templates_list($store = null)
    {
        // Ресурс к которому обращаемся
        $resource = "templates_list";
        // Отдаем роутеру RouterDb конфигурацию.
        $router = new Router($this->config);
        // Получаем название базы для указанного ресурса
        $db_name = $router->ping($resource);
        // Подключаемся к базе
        $db = new Db($db_name, $this->config);
        // Отправляем запрос и получаем данные
        if (isset($store)) {
            $response = $db->get($resource, ["store_id" => $store]);
        } else {
            $response = $db->get($resource);
        }
        return $response["body"]["items"];
    }
 
}
 