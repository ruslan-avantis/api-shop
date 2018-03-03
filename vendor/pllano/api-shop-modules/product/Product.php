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

namespace Pllano\ApiShop\Modules\Products;

use Psr\Http\Message\{ServerRequestInterface as Request, ResponseInterface as Response};
use Psr\Container\ContainerInterface as Container;
use Pllano\RouterDb\Router as RouterDb;
use Pllano\ApiShop\Adapters\Image;

class Product
{

    private $app;
    private $block;
    private $route;
    private $modulKey;
    private $modulVal;
    private $config;

    function __construct(Container $app, $route = null, $block = null, $modulKey = null, $modulVal = [])
    {
        $this->app = $app;
        $this->block = $block;
        $this->route = $route;
        $this->modulKey = $modulKey;
        $this->modulVal = $modulVal;
        $this->config = $app->get('config');
    }

    public function get(Request $request)
    {
        $host = $request->getUri()->getHost();
        // Получаем alias из url
        if ($request->getAttribute('alias')) {
            $alias = sanitize($request->getAttribute('alias'));
        }
        $name = null;
        // Получаем alias из url
        if ($request->getAttribute('name')) {
            $name = sanitize($request->getAttribute('name'));
        }
        // Конфигурация пакета
        $moduleArr['config'] = $this->modulVal;

        $content = [];
        $contentArr = [];
        $head = [];
        $heads = [];
        $render = [];
        $return = [];

        // Ресурс (таблица) к которому обращаемся
        $resource = "price";
        // Отдаем роутеру RouterDb конфигурацию
        $routerDb = new RouterDb($this->config, 'Apis');
        // Пингуем для ресурса указанную и доступную базу данных
        // Подключаемся к БД через выбранный Adapter: Sql, Pdo или Apis (По умолчанию Pdo)
        $db = $routerDb->run($routerDb->ping($resource));
        // Отправляем запрос к БД в формате адаптера. В этом случае Apis
        $responseArr = $db->get($resource, [], $alias);

        if (isset($responseArr["headers"]["code"]) && (int)$responseArr["headers"]["code"] == 200) {
            if(is_object($responseArr["body"]["items"]["0"]["item"])) {
                $item = (array)$responseArr["body"]["items"]["0"]["item"];
            } elseif (is_array($responseArr["body"]["items"]["0"]["item"])) {
                $item = $responseArr["body"]["items"]["0"]["item"];
            }

            // Если ответ не пустой
            // Обрабатываем картинки
            $images = [];
            $image = new Image($this->config);
            foreach($item['image'] as $value)
            {
                $img = $value['image_path'] ?? null;
                if (isset($img)) {
                    $images[] = $image->get($item['product_id'], $img, $moduleArr['config']["image_width"], $moduleArr['config']["image_height"]);
                } else {
                    $arr['images'] = $image->get(null, http_host().'/images/no_image.png', $moduleArr['config']["image_width"], $moduleArr['config']["image_height"]);
                }
            }
            $arr['images'] = $images;

            // Формируем URL страницы товара
            $path_url = pathinfo($item['url']);
            $basename = $path_url['basename'];
            $baseurl = str_replace('-'.$item['product_id'].'.html', '', $basename);
            //$arr['url'] = $scheme.'/product/'.$item['id'].'/'.$baseurl.'.html';
            $arr['url'] = '/product/'.$item['id'].'/'.$baseurl.'.html';
            $arr['name'] = (isset($item['name'])) ? clean($item['name']) : '';
            $item_name = $arr['name'];
            $arr['description'] = $item['description']['text'] ?? '';
            $arr['type'] = $item['type'] ?? '';
            $arr['brand'] = $item['brand'] ?? '';
            $arr['serie'] =  $item['serie'] ?? '';
            $arr['articul'] = $item['articul'] ?? '';
            if ($item['serie'] && $item['articul']) {$arr['name'] = $item['serie'].' '.$item['articul'];}
            $arr['oldprice'] = $item['oldprice'] ?? null;
            $arr['price'] = $item['price'] ?? '';
            $arr['available'] = $item['available'] ?? '';
            $arr['product_id'] = $item['product_id'] ?? '';
            $date = $item['action_date'] ?? date_rand_min(1000, 5000);
            $contentArr['content'] = $arr + date_arr($date);

            // Каждый товар может иметь свой уникальный шаблон
            // Если шаблон товара не установлен берем по умолчанию
            if (isset($item['template'])){
                $themes_dir = $this->config["settings"]["themes"]["dir"];
                $templates_dir = $this->config["template"]["front_end"]["themes"]["template"];
                $template_name = $this->config["settings"]["themes"]["template"];
                $templates_test = $themes_dir.'/'.$templates_dir.'/'.$template_name.'/layouts/'.$item['template'];
                if (file_exists($templates_test)) {
                    $render['render'] = $item['template'];
                }
            }

            $head["title"] = $item["title"] ?? $item_name;
            $head["keywords"] = $item["keywords"] ?? $item_name;
            $head["description"] = $item["description"] ?? $item_name;
            $head["seo_title"] = $item["seo_title"] ?? $item_name;
            $head["seo_keywords"] = $item["seo_keywords"] ?? $item_name;
            $head["seo_description"] = $item["seo_description"] ?? $item_name;
            $head["og_url"] = $item["og_url"] ?? $item_name;
            $head["og_title"] = $item["og_title"] ?? $item_name;
            $head["og_description"] = $item["og_description"] ?? $item_name;
            // Собираем данные в массив
            $heads['head'] = $head;
        }

        $content['content']['modules'][$this->modulKey] = $contentArr + $moduleArr;
        $return = array_replace_recursive($heads, $content, $render);
        return $return;
    }

}
 