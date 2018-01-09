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
    private $db_name;
    private $site_template;
    private $config;
 
    function __construct()
    {
 
        // Подключаем конфиг Settings\Config
        $config = (new Settings())->get();
        $this->config = $config;
 
        // Получаем название шаблона
        $this->themes = $this->config["settings"]["themes"];
 
        // Ресурс к которому обращаемся
        $this->resource = "site";
        // Отдаем роутеру RouterDb конфигурацию.
        $router = new Router($this->config);
        // Получаем название базы для указанного ресурса
        $this->db_name = $router->ping($this->resource);
 
    }

    public function get()
    {
        // Подключаемся к базе
        $db = new Db($this->db_name, $this->config);
        // Отправляем запрос и получаем данные
        $response = $db->get($this->resource);
 
        $this->site = $response["body"]["items"]["item"];
		if ($this->config["db"]["api"]["config"] == true) {
            $this->site_template = $response["body"]["items"]["item"]["template"];
        } else {
            $this->site_template = $this->themes["template"];
        }
        return $this->site;
    }
    
    public function template()
    {
        return $this->site_template;
    }
 
}
 