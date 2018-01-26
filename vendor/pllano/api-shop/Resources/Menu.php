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
                    if ($val->parent_id == 0) {
                        $item['alias'] = $val->alias;
                        $item['title'] = $val->title;
                        $item['title_ru'] = $val->title_ru;
                        $item['title_ua'] = $val->title_ua;
                        $item['title_en'] = $val->title_en;
                        $item['title_de'] = $val->title_de;
                        $item['url'] = $val->url;
 
                        $parent = $db->get($this->resource, ["menu" => 1, "state" => 1, "parent_id" => $val->id]);
                        if (isset($parent['headers']['code'])) {
                            if ($parent['headers']['code'] == 200 || $parent['headers']['code'] == '200') {
                                foreach($parent['body']['items'] as $subvalue)
                                {
                                    $sub = $subvalue['item'];
                                    $submenu['alias'] = $sub->alias;
                                    $submenu['title'] = $sub->title;
                                    $submenu['title_ru'] = $sub->title_ru;
                                    $submenu['title_ua'] = $sub->title_ua;
                                    $submenu['title_en'] = $sub->title_en;
                                    $submenu['title_de'] = $sub->title_de;
                                    $submenu['url'] = $sub->url;
 
                                    $subparent = $db->get($this->resource, ["menu" => 1, "state" => 1, "parent_id" => $sub->id]);
                                    if (isset($subparent['headers']['code'])) {
                                        if ($subparent['headers']['code'] == 200 || $subparent['headers']['code'] == '200') {
                                            $submenu['subsubmenu'] = '';
                                            foreach($subparent['body']['items'] as $subsubvalue)
                                            {
                                                $subsub = $subsubvalue['item'];
                                                $subsubmenu['alias'] = $subsub->alias;
                                                $subsubmenu['title'] = $subsub->title;
                                                $subsubmenu['title_ru'] = $subsub->title_ru;
                                                $subsubmenu['title_ua'] = $subsub->title_ua;
                                                $subsubmenu['title_en'] = $subsub->title_en;
                                                $subsubmenu['title_de'] = $subsub->title_de;
                                                $subsubmenu['url'] = $subsub->url;
                                                
                                                $submenu['subsubmenu'][] = $subsubmenu;
                                            }
                                        } else {
                                            $submenu['subsubmenu'] = null;
                                        }
                                    } else {
                                        $submenu['subsubmenu'] = null;
                                    }

                                    $item['submenu'][] = $submenu;
                                }
                            } else {
                            $item['submenu'] = null;
                        }
                        } else {
                            $item['submenu'] = null;
                        }
                        
                        $resp[] = $item;
                    }
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
 