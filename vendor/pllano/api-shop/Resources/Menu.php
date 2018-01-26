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
 
class Menu {
    
    private $db_name;
    private $resource = "category";
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
 
    // Ресурс category доступен только на чтение
    public function get()
    {
        // Подключаемся к базе
        $db = new Db($this->db_name, $this->config);
        // Отправляем запрос и получаем данные
        $response = $db->get($this->resource, ["menu" => 1, "state" => 1, "sort" => "sort", "order" => "ASC", "offset" => 0, "limit" => 5]);
 
        if (isset($response['headers']['code'])) {
			if ($response['headers']['code'] == 200 || $response['headers']['code'] == '200') {
				
				$resp = array();
				foreach($response['body']['items'] as $value)
                {
					$val = $value['item'];
					$item['alias'] = $val->alias;
					$item['title'] = $val->title;
					$item['title_ru'] = $val->title_ru;
					$item['title_ua'] = $val->title_ua;
					$item['title_en'] = $val->title_en;
					$item['title_de'] = $val->title_de;
					$item['url'] = $val->url;
					$resp[] = $item;
				}

                return $resp;
            } else {
                return null;
            }
		} else {
            return null;
        }
    }
 
}
 