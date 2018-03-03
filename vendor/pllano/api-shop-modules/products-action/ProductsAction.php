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

class ProductsAction
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
        // Конфигурация пакета
		$moduleArr = [];
        $moduleArr['config'] = $this->modulVal;

        // Ресурс (таблица) к которому обращаемся
        $resource = "price";
		// Отдаем роутеру RouterDb конфигурацию
        $routerDb = new RouterDb($this->config, 'Apis');
        // Пингуем для ресурса указанную и доступную базу данных
        // Подключаемся к БД через выбранный Adapter: Sql, Pdo или Apis (По умолчанию Pdo)
        $db = $routerDb->run($routerDb->ping($resource));
        // Массив для запроса
        $query = [
            "limit" => $moduleArr['config']['limit'],
            "sort" => $moduleArr['config']['sort'],
            "order" => $moduleArr['config']['order'],
            "relations" => $moduleArr['config']['relations'],
            "state_seller" => 1
        ];
        // Отправляем запрос к БД в формате адаптера. В этом случае Apis
        $responseArr = $db->get($resource, $query);

        if (isset($responseArr["response"]['total'])) {
            $this->count = $responseArr["response"]['total'];
        }

		// Обработка картинок
        $image = new Image($this->config);

        $products = [];
        $product = [];
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
				
				if (isset($product['action_date'])) {
                    $date = $product['action_date'];
                } else {
                    $date = date_rand_min(1000, 5000);
                }

				$product = array_replace_recursive($product, date_arr($date));

                // Отдаем данные шаблонизатору 
                $products[] = $product;
            }
        }
        $resp['content'] = $products;
        
        $content = [];
        $content['content']['modules'][$this->modulKey] = $moduleArr + $resp;
        return $content;
    }
    
}
 