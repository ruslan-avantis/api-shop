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

use RouterDb\Db;
use RouterDb\Router;
use ApiShop\Config\Settings;

class Language {

    private $db_name;
    private $language = "en";
    private $resource = "language";
    private $config;
 
    function __construct()
    {
    // Подключаем конфиг Settings\Config
    $config = (new Settings())->get();
    $this->config = $config;

    // Отдаем роутеру RouterDb конфигурацию.
    $router = new Router($this->config);
    // Получаем название базы для указанного ресурса
    $this->db_name = $router->ping($this->resource);
 
    }

    // Ресурс language доступен только на чтение
    public function get($language = null)
    {
        if (isset($language)){$this->language = $language;}
 
        // Подключаемся к базе
        $db = new Db($this->db_name, $this->config);
        // Отправляем запрос и получаем данные
        $response = $db->get($this->resource);
 
        if ($response != null) {
            foreach($response['body']['items'] as $value)
            {
                $array = (array)$value['item'];
                $arr[$array["id"]] = $array[$this->language];
            }
            return $arr;
        } else {
            return null;
        }
    }
 
}
 