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
 
namespace ApiShop\Modules\Products;
 
use Psr\Http\Message\ServerRequestInterface as Request;
use Pllano\RouterDb\{Db, Router};
use ApiShop\Utilities\Utility;
use ApiShop\Adapter\Image;
 
class Product
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
 
    public function get(Request $request)
    {
        $config = $this->config;
        $host = $request->getUri()->getHost();
        // Подключаем утилиты
        $utility = new Utility();
        // Получаем alias из url
        if ($request->getAttribute('alias')) {
            $alias = $utility->clean($request->getAttribute('alias'));
        }
        $name = null;
        // Получаем alias из url
        if ($request->getAttribute('name')) {
            $name = $utility->clean($request->getAttribute('name'));
        }
        // Конфигурация пакета
        $moduleArr['config'] = $config['modules'][$this->route][$this->module];
        //$moduleArr = $template['modules'][$this->route][$this->module];
        $content = [];
        $contentArr = [];
        $heads = [];
        $return = [];
        $render = [];
 
        // Ресурс (таблица) к которому обращаемся
        $resource = "price";
        // Отдаем роутеру RouterDb конфигурацию.
        $router = new Router($config);
        // Получаем название базы для указанного ресурса
        $name_db = $router->ping($resource);
        // Подключаемся к базе
        $db = new Db($name_db, $config);
        // Отправляем запрос и получаем данные
        $resp = $db->get($resource, [], $alias);
 
        if (isset($resp["headers"]["code"])) {
            if ($resp["headers"]["code"] == 200 || $resp["headers"]["code"] == "200") {
 
                $image = new Image($config);
 
                $item = $resp["body"]['items']['0']['item'];
                $protocol_uri = $config["server"]["scheme"].'://'.$host;
 
                // Если ответ не пустой
                // Обрабатываем картинки
                foreach($item['image'] as $value)
                {
                    $img = '';
                    $img = (isset($value['image_path'])) ? $value['image_path'] : null;
                    if (isset($img)) {
                        $images[] = $image->get($item['product_id'], $img, $moduleArr['config']["image_width"], $moduleArr['config']["image_height"]);
                        } else {
                        $arr['images'] = $image->get(null, $protocol_uri.'/images/no_image.png', $moduleArr['config']["image_width"], $moduleArr['config']["image_height"]);
                    }
                }
                $arr['images'] = $images;
 
                // Формируем URL страницы товара
                $path_url = pathinfo($item['url']);
                $basename = $path_url['basename'];
                $baseurl = str_replace('-'.$item['product_id'].'.html', '', $basename);
                //$arr['url'] = $scheme.'/product/'.$item['id'].'/'.$baseurl.'.html';
                $arr['url'] = '/product/'.$item['id'].'/'.$baseurl.'.html';
 
                $arr['name'] = (isset($item['name'])) ? $utility->clean($item['name']) : '';
 
                $item_name = $arr['name'];
 
                $arr['description'] = (isset($item['description']['text'])) ? $utility->clean($item['description']['text']) : '';
                $arr['type'] = (isset($item['type'])) ? $utility->clean($item['type']) : '';
                $arr['brand'] = (isset($item['brand'])) ? $utility->clean($item['brand']) : '';
                $arr['serie'] = (isset($item['serie'])) ? $utility->clean($item['serie']) : '';
                $arr['articul'] = (isset($item['articul'])) ? $utility->clean($item['articul']) : '';
                if ($item['serie'] && $item['articul']) {$arr['name'] = $item['serie'].' '.$item['articul'];}
                $arr['oldprice'] = (isset($item['oldprice'])) ? $utility->clean($item['oldprice']) : '';
                $arr['price'] = (isset($item['price'])) ? $utility->clean($item['price']) : '';
                $arr['available'] = (isset($item['available'])) ? $utility->clean($item['available']) : '';
                $arr['product_id'] = (isset($item['product_id'])) ? $utility->clean($item['product_id']) : '';
 
                if (isset($item['action_date'])) {
                    $date = $item['action_date'];
                    } else {
                    $rand = rand(1000, 5000);
                    $date = date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s")." +".$rand." minutes"));
                }
 
                $date = strtotime($date);
                $arr['y'] = date("Y", $date);
                $arr['m'] = date("m", $date);
                $arr['d'] = date("d", $date);
                $arr['h'] = date("H", $date);
                $arr['i'] = date("i", $date);
                $arr['s'] = date("s", $date);
 
                // Собираем данные в массив
                $contentArr['content'] = $arr;
 
                // Каждый товар может иметь свой уникальный шаблон
                // Если шаблон товара не установлен берем по умолчанию
                if (isset($item['template'])){
                    $themes_dir = $config["settings"]["themes"]["dir"];
                    $templates_dir = $config["template"]["front_end"]["themes"]["template"];
                    $template_name = $config["settings"]["themes"]["template"];
                    $templates_test = $themes_dir.'/'.$templates_dir.'/'.$template_name.'/layouts/'.$item['template'];
                    if (file_exists($templates_test)) {
                        $render['render'] = $item['template'];
                    }
                }
 
                if (isset($item["title"])){$head["title"] = $item["title"];} else {$head["title"] = $arr['name'];}
                if (isset($item["keywords"])){$head["keywords"] = $item["keywords"];} else {$head["keywords"] = $arr['name'];}
                if (isset($item["description"])){$head["description"] = $item["description"];} else {$head["description"] = $arr['name'];}
                if (isset($item["seo_title"])){$head["seo_title"] = $item["seo_title"];} else {$head["seo_title"] = $arr['name'];}
                if (isset($item["seo_keywords"])){$head["seo_keywords"] = $item["seo_keywords"];} else {$head["seo_keywords"] = $arr['name'];}
                if (isset($item["seo_description"])){$head["seo_description"] = $item["seo_description"];} else {$head["seo_description"] = $arr['name'];}
                if (isset($item["og_url"])){$head["og_url"] = $item["og_url"];} else {$head["og_url"] = $arr['url'];}
                if (isset($item["og_title"])){$head["og_title"] = $item["og_title"];} else {$head["og_title"] = $arr['name'];}
                if (isset($item["og_description"])){$head["og_description"] = $item["og_description"];} else {$head["og_description"] = $arr['name'];}
 
                // Собираем данные в массив
                $heads['head'] = $head;
            }
        }
 
        $content['content']['modules'][$this->module] = $contentArr + $moduleArr;
        $return = array_replace_recursive($heads, $content, $render);
        return $return;
    }
 
}
 