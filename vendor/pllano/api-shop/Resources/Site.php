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

            $json_dir = $this->config["db"]["json"]["dir"];
            if (file_exists($json_dir."db.data.json")) {
                $json = json_decode(file_get_contents($json_dir."db.data.json"), true);
                if (isset($json["0"]["template"])) {
                    $db_template = $json["0"]["template"];
                } else {
                    $db_template = null;
                }
            } else {
                $db_template = null;
            }
            
            // Определяем откуда брать название шаблона
            if ($db_template != null) {
                // Если название шаблона есть в базе json берем его
                $this->site_template = $db_template;
 
                // Проверяем название шаблона в настройках сайта и если он отличается от дефолтного записываем его в базу
                if (isset($response["body"]["items"]["0"]["item"]["template"])) {
                    if ($response["body"]["items"]["0"]["item"]["template"] != $this->config["settings"]["themes"]["template"]) {
                        // Подключаемся к базе json
                        $db_json = new Db("json", $this->config);
                        // Обновляем название шаблона в базе
                        $db_json->put("db", ["template" => $response["body"]["items"]["0"]["item"]["template"]], 1);
                    }
                }
 
            } elseif (isset($response["body"]["items"]["0"]["item"]["template"])) {
                // Если название шаблона есть в настройках сайта, берем его
                $this->site_template = $response["body"]["items"]["0"]["item"]["template"];
                
            } else {
                // Берем название по умолчанию, из конфигурации
                $this->site_template = $this->config["settings"]["themes"]["template"];
            }
 
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
 