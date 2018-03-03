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

namespace Pllano\ApiShop\Modules\Categories;

use Psr\Container\ContainerInterface as Container;
use Pllano\RouterDb\Router as RouterDb;

use Pllano\ApiShop\Adapters\Image;

class Products
{
    private $count = 0;
    private $app;
	private $config;
	
    function __construct(Container $app)
    {
        $this->app = $app;
		$this->config = $app->get('config');
	}
	
    public function get(array $query = [], array $moduleArr = [], $host)
    {
        // Обработка картинок
        $image = new Image($this->config);
        // Ресурс (таблица) к которому обращаемся
        $resource = "price";
		// Отдаем роутеру RouterDb конфигурацию
		$routerDb = new RouterDb($this->config, 'Apis');
		// Пингуем для ресурса указанную и доступную базу данных
		// Подключаемся к БД через выбранный Adapter: Sql, Pdo или Apis (По умолчанию Pdo)
		$db = $routerDb->run($routerDb->ping($resource));
		// Отправляем запрос к БД в формате адаптера. В этом случае Apis
		$responseArr = $db->get($resource, $query);
		
        if (isset($responseArr["response"]['total'])) {
            $this->count = $responseArr["response"]['total'];
		}
		
        // Если ответ не пустой
        if (count($responseArr['body']['items']) >= 1) {
            foreach($responseArr['body']['items'] as $item)
            {
                // Обрабатываем картинки
                $product['no_image'] = $image->get(null, http_host().'/images/no_image.png', $moduleArr['config']["image_width"], $moduleArr['config']["image_height"]);
                $image_1 = '';
                $image_1 = (isset($item['item']['image']['0']['image_path'])) ? clean($item['item']['image']['0']['image_path']) : null;
                if (isset($image_1)) {$product['image']['1'] = $image->get($item['item']['product_id'], $image_1, $moduleArr['config']["image_width"], $moduleArr['config']["image_height"]);}
                $image_2 = '';
                $image_2 = (isset($item['item']['image']['1']['image_path'])) ? clean($item['item']['image']['1']['image_path']) : null;
                if (isset($image_2)) {$product['image']['2'] = $image->get($item['item']['product_id'], $image_2, $moduleArr['config']["image_width"], $moduleArr['config']["image_height"]);}
				
                $path_url = pathinfo($item['item']['url']);
                $basename = $path_url['basename']; // lib.inc.php
                $baseurl = str_replace('-'.$item['item']['product_id'].'.html', '', $basename);

                $product['url'] = '/product/'.$item['item']['id'].'/'.$baseurl.'.html';
                $product['name'] = (isset($item['item']['name'])) ? clean($item['item']['name']) : '';
                $product['type'] = (isset($item['item']['type'])) ? clean($item['item']['type']) : '';
                $product['brand'] = (isset($item['item']['brand'])) ? clean($item['item']['brand']) : '';
                $product['serie'] = (isset($item['item']['serie'])) ? clean($item['item']['serie']) : '';
                $product['articul'] = (isset($item['item']['articul'])) ? clean($item['item']['articul']) : '';
                if ($item['item']['serie'] && $item['item']['articul']) {$product['name'] = $item['item']['serie'].' '.$item['item']['articul'];}
                $product['oldprice'] = (isset($item['item']['oldprice_out'])) ? clean($item['item']['oldprice_out']) : '';
                $product['price'] = (isset($item['item']['price_out'])) ? clean($item['item']['price_out']) : '';
                $product['available'] = (isset($item['item']['available'])) ? clean($item['item']['available']) : '';
                $product['product_id'] = (isset($item['item']['product_id'])) ? clean($item['item']['product_id']) : '';
                $product['shortname'] = 'грн.';
                $product['currency'] = 'UAH';
				
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
 