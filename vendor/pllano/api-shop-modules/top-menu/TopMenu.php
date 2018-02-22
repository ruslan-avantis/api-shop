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
 
namespace ApiShop\Modules\Menu;
 
use Psr\Http\Message\ServerRequestInterface as Request;
use Pllano\RouterDb\{Db, Router};
 
class TopMenu {
 
    private $config;
    private $package;
    private $template;
    private $route;
    private $block;
    private $module;
    private $lang = null;
    private $language = null;
    private $count = 0;
    private $resource = null;
 
    function __construct($config = [], $package = [], $template = [], $module, $block, $route, $lang = null, $language = null)
    {
        $this->config = $config;
        $this->package = $package;
        $this->template = $template;
        $this->route = $route;
        $this->block = $block;
        $this->module = $module;
        if (isset($lang)) {
            $this->lang = $lang;
        }
        if (isset($language)) {
            $this->language = $language;
        }
    }
 
    public function get(Request $request)
    {
        $this->resource = 'category';
        $config = $this->config;
        $template = $this->template;
        // Конфигурация пакета
        $moduleArr['config'] = $config['modules']['nav'][$this->module];
        //$moduleArr = $template['modules']['nav'][$this->module];
        // Отдаем роутеру RouterDb конфигурацию.
        $router = new Router($config);
        // Получаем название базы для указанного ресурса
        $this->db_name = $router->ping($this->resource);
        // Подключаемся к базе
        $db = new Db($this->db_name, $this->config);
        // Отправляем запрос и получаем данные
        $menuArr = $db->get($this->resource, ["menu" => 1, "state" => 1, "sort" => "sort", "order" => "ASC", "offset" => 0, "limit" => 5]);
 
        if (isset($menuArr['headers']['code'])) {
            if ($menuArr['headers']['code'] == 200 || $menuArr['headers']['code'] == '200') {
                $resp = [];
                $item = [];
                $submenu = [];
                foreach($menuArr['body']['items'] as $value)
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
                                            $submenu['subsubmenu'] = [];
                                            $subsubmenu = [];
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
 
                $content['content'] = $resp;
                $return['content']['modules'][$this->module] = array_replace_recursive($content, $moduleArr);
                return $return;
            } else {
                return [];
            }
        } else {
            return [];
        }
    
    }
}
 