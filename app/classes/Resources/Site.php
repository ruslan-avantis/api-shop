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
use ApiShop\Database\Router as Database;
use ApiShop\Database\Ping;

class Site {
 
    private $settings = null;
    private $site = null;
    private $themes = null;
    private $resource;
    private $db_name;
 
    function __construct()
    {
        // Ресурс к которому обращаемся
        $this->resource = "site";
        // Получаем конфигурацию
        $this->settings = (new Settings())->get();
        // Получаем название шаблона
        $this->themes = $this->settings["settings"]["themes"];
        // Database\Ping контролирует состояние master и slave
        // Если база указанная в конфигурации resource недоступна, подключит master или slave
        $ping = new Ping($this->resource); // return api
        $this->db_name = $ping->get();
    }

    public function get()
    {
        // Подключаемся к базе
        $db = new Database($this->db_name);
        // Отправляем запрос
        $response = $db->get($this->resource);
        $this->site = $response["items"]["item"];
        return $this->site;
    }
    
    public function template()
    {
        $this->get();
        if ($this->settings["db"]["api"]["config"] == true) {
            return $this->site["template"];
        } else {
            return $this->themes["template"];
        }
    }
 
}
 