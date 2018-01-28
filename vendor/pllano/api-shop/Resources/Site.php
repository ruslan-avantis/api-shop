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
 
class Site {
 
    private $settings = null;
    private $site = null;
    private $themes = null;
    private $resource;
    private $db_name = null;
    private $site_template;
    private $config;
 
    function __construct()
    {
        // Подключаем конфиг Settings\Config
        $config = (new Settings())->get();
        $this->config = $config;
        $this->site_template = $config["settings"]["themes"]["template"];
    }

    public function get()
    {
        // Ресурс к которому обращаемся
        $this->resource = "site";
        // Отдаем роутеру RouterDb конфигурацию.
        $router = new Router($this->config);
        // Получаем название базы для указанного ресурса
        $this->db_name = $router->ping($this->resource);

        if ($this->db_name != null) {
            // Подключаемся к базе
            $db = new Db($this->db_name, $this->config);
            // Отправляем запрос и получаем данные
            $response = $db->get($this->resource);
 
            // Получаем настройки сайта
            if ($response != null) {
                $this->site = $response["body"]["items"]["0"]["item"];
            } else {
                $this->site = null;
            }
            // Берем название по умолчанию, из конфигурации
 
            return $this->site;
 
        } else {
            return null;
        }
    }
 
    public function template()
    {
        return $this->site_template;
    }
 
}
 