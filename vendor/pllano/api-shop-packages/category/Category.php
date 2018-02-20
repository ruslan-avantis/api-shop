<?php /**
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

namespace ApiShop\Modules\Categories;

use Psr\Http\Message\{ServerRequestInterface as Request, ResponseInterface as Response};
use Pllano\RouterDb\{Db, Router};
use Pllano\RouterDb\{Filter, Pagination};
use ApiShop\Utilities\Utility;

class Category
{
    private $config;
    private $package;
    private $template;
    private $route;
    private $block;
    private $module;
    private $lang = null;
    private $language = null;
    private $count = 0;
    
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
    
    public function get(Request $request, Response $response, array $args)
    {
        $config = $this->config;
        $template = $this->template;
        $language = $this->language;
        $host = $request->getUri()->getHost();
        $path = $request->getUri()->getPath();
        $getParams = $request->getQueryParams();
        // Подключаем утилиты
        $utility = new Utility();
        // Получаем alias из url
        $alias = null;
        if ($request->getAttribute('alias')) {
            $alias = $utility->clean($request->getAttribute('alias'));
        }
        // Конфигурация пакета
        $moduleArr['config'] = $config['modules'][$this->route][$this->module];
        //$moduleArr = $template['modules'][$this->route][$this->module];
 
        $content = [];
        $contentArr = [];
        $heads = [];
        $return = [];
        $render = '';
        $category = [];
 
        $render = $moduleArr['config']['view'] ? $moduleArr['config']['view'] : $template['layouts'][$this->route];
        
        $products_template = $moduleArr['config']['helper']['products_list'] ? $moduleArr['config']['helper']['products_list'] : 'helper/products.html';
        $head["products_limit"] = $moduleArr['config']['limit'];
        $head["products_order"] = $moduleArr['config']['order'];
        $head["products_sort"] = $moduleArr['config']['sort'];
 
        if (isset($alias)) {
            // Ресурс (таблица) к которому обращаемся
            $category_resource = "category";
            // Отдаем роутеру RouterDb конфигурацию.
            $router = new Router($config);
            // Получаем название базы для указанного ресурса
            $category_db = $router->ping($category_resource);
            // Подключаемся к базе
            $db = new Db($category_db, $config);
            // Отправляем запрос и получаем данные
            $resp = $db->get($category_resource, ['alias' => $alias]);
 
            if (isset($resp["headers"]["code"])) {
                if ($resp["headers"]["code"] == 200 || $resp["headers"]["code"] == '200') {
                    $category = $resp['body']['items']['0']['item'];
 
                    if(is_object($category)) {
                        $category = (array)$category;
                    }
 
                    $head["title"] = $category['seo_title'] ? $category['seo_title'] : $category['title'];
                    $head["keywords"] = $category['seo_keywords'] ? $category['seo_keywords'] : $category['title'];
                    $head["description"] = $category['seo_description'] ? $category['seo_description'] : $category['title'];
                    $head["og_title"] = $category['og_title'] ? $category['og_title'] : $category['title'];
                    $head["og_description"] = $category['og_description'] ? $category['og_description'] : $category['title'];
                    $head["og_image"] = $category['og_image'] ? $category['og_image'] : '';
                    $head["og_type"] = $category['og_type'] ? $category['og_type'] : '';
                    $head["robots"] = $category['robots'] ? $category['robots'] : 'index, follow';
                    $head["products_template"] = $category['products_template'] ? $category['products_template'] : $moduleArr['config']['helper']['products_list'];
                    $head["products_limit"] = $category['products_limit'] ? $category['products_limit'] : $moduleArr['config']['limit'];
                    $head["products_order"] = $category['products_order'] ? $category['products_order'] : $moduleArr['config']['order'];
                    $head["products_sort"] = $category['products_sort'] ? $category['products_sort'] : $moduleArr['config']['sort'];
 
                    if (isset($category['categories_template'])) {
                        $themes_dir = $config["settings"]["themes"]["dir"];
                        $templates_dir = $config["settings"]["themes"]["templates"];
                        $template_name = $config["settings"]["themes"]["template"];
                        $templates_test = $themes_dir.'/'.$templates_dir.'/'.$template_name.'/layouts/'.$category['categories_template'];
                        if (file_exists($templates_test)) {
                            $render = $category['categories_template'] ? $category['categories_template'] : $moduleArr['config']['view'];
                        }
                    }
                    // Собираем данные в массив
                    $heads['head'] = $head;
                }
            }
            if (isset($category['product_type'])) {
                //$product_type = explode(',', str_replace(['"', "'", " "], '', $category['product_type']));
                $product_type = $category['product_type'];
                } else {
                $product_type = null;
            }
        }
        
        // Получаем массив параметров uri
        $queryParams = $request->getQueryParams();
        $arr = [];
        $arr['state'] = 1;
        $arr['offset'] = 0;
        $arr['limit'] = $head["products_limit"];
        $arr['order'] = $head["products_order"];
        $arr['sort'] = $head["products_sort"];
        if (count($queryParams) >= 1) {
            foreach($queryParams as $key => $value)
            {
                if (isset($key) && isset($value)) {
                    $arr[$key] = $utility->clean($value);
                }
            }
        }
 
        // Собираем полученные параметры в url и отдаем шаблону
        $get_array = http_build_query($arr);
        // Вытягиваем URL_PATH для правильного формирования юрл
        //$url_path = parse_url($request->getUri(), PHP_URL_PATH);
        $url_path = $path;
        // Подключаем сортировки
        $filter = new $moduleArr['config']['vendors']['filter']($url_path, $arr);
 
        $orderArray = $filter->order();
 
        $limitArr = [
            "6" => "6",
            "15" => "15",
            "30" => "30",
            "60" => "60",
            "90" => "90",
            "120" => "120"
        ];
        $limitArray = $filter->limit($limitArr);

        // Формируем массив по которому будем сортировать
        $sortArr = [
            "name" => $language["51"],
            "type" => $language["46"],
            "brand" => $language["47"],
            "serie" => $language["48"],
            "articul" => $language["49"],
            "price" => $language["112"]
        ];
        $sortArray = $filter->sort($sortArr);
 
        if (isset($product_type)) {
            $arrPlus['type'] = $product_type;
        }
        $arrPlus['relations'] = "image";
        $newArr = $arr + $arrPlus;
        
        // Получаем список товаров
        $vendorCategory = new $moduleArr['config']['vendors']['products']($config);
        $contentArr['content']['products'] = $vendorCategory->get($newArr, $moduleArr, $host);
 
        // Даем пагинатору колличество
        $count = $vendorCategory->count();
        $paginator = $filter->paginator($count);
        
        // Собираем данные в массив
        $contentArr['content']['products_template'] = $products_template;
        $contentArr['content']['paginator'] = $paginator;
        $contentArr['content']['order'] = $orderArray;
        $contentArr['content']['sort'] = $sortArray;
        $contentArr['content']['limit'] = $limitArray;
        $contentArr['content']['param'] = $arr;
        $contentArr['content']['count'] = $count;
        $contentArr['content']['get_array'] = $get_array;
        $contentArr['content']['url_path'] = $url_path;
        $contentArr['content']['render'] = $render;
        $contentArr['content']['template'] = $template;
        
        $content['content']['modules'][$this->module] = array_replace_recursive($contentArr, $moduleArr);
        $return = array_replace_recursive($heads, $content);
 
        
        //print("<br>{$alias}<br>");
 
        return $return;
    }
 
}
 