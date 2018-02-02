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
 
use RouterDb\Db;
use RouterDb\Router;
 
use ApiShop\Config\Settings;
use ApiShop\Utilities\Utility;
use ApiShop\Adapter\Image;
 
class Products {
 
    private $config;
    private $count = 0;
 
    function __construct()
    {
        // Подключаем конфиг Settings\Config
        $config = (new Settings())->get();
        $this->config = $config;
    }
 
    public function get(array $arr = array(), array $template = array(), $host)
    {
        // Подключаем плагины
        $utility = new Utility();
        // Обработка картинок
        $image = new Image();
        // Ресурс (таблица) к которому обращаемся
        $resource = "price";
        // Отдаем роутеру RouterDb конфигурацию.
        $router = new Router($this->config);
        // Получаем название базы для указанного ресурса
        $name_db = $router->ping($resource);
        // Подключаемся к базе
        $db = new Db($name_db, $this->config);
        // Отправляем запрос и получаем данные
        $response = $db->get($resource, $arr);
 
        if (isset($response["response"]['total'])) {
            $this->count = $response["response"]['total'];
        }
        
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $protocol_uri = 'https://'.$host;
        } else {
            $protocol_uri = 'http://'.$host;
        }
 
        // Если ответ не пустой
        if (count($response['body']['items']) >= 1) {
            foreach($response['body']['items'] as $item)
            {
                // Обрабатываем картинки
                $product['no_image'] = $image->get(null, $protocol_uri.'/images/no_image.png', $template["products"]["image_width"], $template["products"]["image_height"]);
                $image_1 = '';
                $image_1 = (isset($item['item']['image']['0']['image_path'])) ? $utility->clean($item['item']['image']['0']['image_path']) : null;
                if (isset($image_1)) {$product['image']['1'] = $image->get($item['item']['product_id'], $image_1, $template["products"]["image_width"], $template["products"]["image_height"]);}
                $image_2 = '';
                $image_2 = (isset($item['item']['image']['1']['image_path'])) ? $utility->clean($item['item']['image']['1']['image_path']) : null;
                if (isset($image_2)) {$product['image']['2'] = $image->get($item['item']['product_id'], $image_2, $template["products"]["image_width"], $template["products"]["image_height"]);}
 
                $path_url = pathinfo($item['item']['url']);
                $basename = $path_url['basename']; // lib.inc.php
                $baseurl = str_replace('-'.$item['item']['product_id'].'.html', '', $basename);
 
                $product['url'] = '/product/'.$item['item']['id'].'/'.$baseurl.'.html';
                $product['name'] = (isset($item['item']['name'])) ? $utility->clean($item['item']['name']) : '';
                $product['type'] = (isset($item['item']['type'])) ? $utility->clean($item['item']['type']) : '';
                $product['brand'] = (isset($item['item']['brand'])) ? $utility->clean($item['item']['brand']) : '';
                $product['serie'] = (isset($item['item']['serie'])) ? $utility->clean($item['item']['serie']) : '';
                $product['articul'] = (isset($item['item']['articul'])) ? $utility->clean($item['item']['articul']) : '';
                if ($item['item']['serie'] && $item['item']['articul']) {$product['name'] = $item['item']['serie'].' '.$item['item']['articul'];}
                $product['oldprice'] = (isset($item['item']['oldprice_out'])) ? $utility->clean($item['item']['oldprice_out']) : '';
                $product['price'] = (isset($item['item']['price_out'])) ? $utility->clean($item['item']['price_out']) : '';
                $product['available'] = (isset($item['item']['available'])) ? $utility->clean($item['item']['available']) : '';
                $product['product_id'] = (isset($item['item']['product_id'])) ? $utility->clean($item['item']['product_id']) : '';
 
                $rand = rand(1000, 5000);
                $date = date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s")." +".$rand." minutes"));
                $date = strtotime($date);
                $product['y'] = date("Y", $date);
                $product['m'] = date("m", $date);
                $product['d'] = date("d", $date);
                $product['h'] = date("H", $date);
                $product['i'] = date("i", $date);
                $product['s'] = date("s", $date);
                // Отдаем данные шаблонизатору 
                $products[] = $product;
            }
        } else {
            $products = null;
        }
        
        return $products;
    }
    
    public function count()
    {
        return $this->count;
    }
 
}
 