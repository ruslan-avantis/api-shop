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

namespace ApiShop\Modules\Articles;

use Psr\Http\Message\ServerRequestInterface as Request;
use Pllano\RouterDb\{Db, Router};
use ApiShop\Utilities\Utility;
use ApiShop\Adapter\Image;

class ArticleCategory
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
        $template = $this->template;
        $host = $request->getUri()->getHost();
        // Подключаем утилиты
        $utility = new Utility();
        // Получаем alias из url
        if ($request->getAttribute('alias')) {
            $alias = $utility->clean($request->getAttribute('alias'));
        } else {
            $alias = null;
        }
 
        $moduleArr = $template['modules'][$this->route][$this->module];
 
        // Ресурс (таблица) к которому обращаемся
        $resource = "article";
        // Отдаем роутеру RouterDb конфигурацию.
        $router = new Router($config);
        // Получаем название базы для указанного ресурса
        $name_db = $router->ping($resource);
        // Подключаемся к базе
        $db = new Db($name_db, $config);
        // Отправляем запрос и получаем данные
        $resp = $db->get($resource, ["alias" => $alias]);
 
        $content = [];
        $contentArr = [];
        $heads = [];
        $return = [];
 
        if (isset($resp["headers"]["code"])) {
            if ($resp["headers"]["code"] == 200 || $resp["headers"]["code"] == "200") {
                // Если данные в виде объекта переводим в массив
                if(is_object($resp["body"]["items"]["0"]["item"])) {
                    $arr = (array)$resp["body"]["items"]["0"]["item"];
                } elseif (is_array($resp["body"]["items"]["0"]["item"])) {
                    $arr = $resp["body"]["items"]["0"]["item"];
                }
 
                $arr["text"] = htmlspecialchars_decode($arr["text"]);
                $arr["text_ru"] = htmlspecialchars_decode($arr["text_ru"]);
                $arr["text_ua"] = htmlspecialchars_decode($arr["text_ua"]);
                $arr["text_en"] = htmlspecialchars_decode($arr["text_en"]);
                $arr["text_de"] = htmlspecialchars_decode($arr["text_de"]);
                $contentArr['content'] = $arr;
 
                $head["title"] = $arr["title"];
                $head["seo_title"] = $arr["seo_title"];
                $head["seo_keywords"] = $arr["seo_keywords"];
                $head["seo_description"] = $arr["seo_description"];
                $head["og_url"] = $arr["og_url"];
                $head["og_title"] = $arr["og_title"];
                $head["og_description"] = $arr["og_description"];
                $heads['head'] = $head;

            }
        }
 
        $content['content']['modules'][$this->module] = $contentArr + $moduleArr;
        $return = array_replace_recursive($heads, $content);
        return $return;
    }
    
}
 