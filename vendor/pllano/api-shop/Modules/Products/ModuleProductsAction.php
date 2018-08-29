<?php 
/**
 * Pllano {API}$hop (https://pllano.com)
 *
 * @link https://github.com/pllano/api-shop
 * @version 1.2.1
 * @copyright Copyright (c) 2017-2018 PLLANO
 * @license http://opensource.org/licenses/MIT (MIT License)
 */
namespace Pllano\ApiShop\Modules\Products;

use Psr\Http\Message\{ServerRequestInterface as Request, ResponseInterface as Response};
use Psr\Container\ContainerInterface as Container;
use Pllano\Interfaces\{ModuleInterface, ModelInterface};
use Pllano\Core\{Module, Model};
use Pllano\Core\Adapters\Image;

class ModuleProductsAction extends Module implements ModuleInterface, ModelInterface
{

    public function __construct(Container $app, $route = null, $block = null, $modulKey = null, $modulVal = [])
    {
		$this->_table = 'price';
		parent::__construct($app, $route, $block, $modulKey, $modulVal);
		$this->connectContainer();
    }

    public function get(Request $request)
    {
        $host = $request->getUri()->getHost();
		$moduleArr = [];
        $moduleArr['config'] = $this->modulVal;
		
		$query["limit"] = $moduleArr['config']['limit'];
		$query["sort"] = $moduleArr['config']['sort'];
		$query["order"] = $moduleArr['config']['order'];
		$query["relations"] = $moduleArr['config']['relations'];
		$query["state_seller"] = 1;
		
		$query["name"] = $moduleArr['config']['name'] ?? null;
		$query["type"] = $moduleArr['config']['type'] ?? null;
		$query["brand"] = $moduleArr['config']['brand'] ?? null;
		$query["serie"] = $moduleArr['config']['serie'] ?? null;
		$query["articul"] = $moduleArr['config']['articul'] ?? null;
		

		// Database GET
        $responseArr = $this->db->get($this->_table, $query) ?? [];

        $products = [];
		$product = [];
        // Если ответ не пустой
        if (isset($responseArr)) {
			$this->count = count($responseArr);
            foreach($responseArr as $data)
            {
                $image = new Image($this->app);
				// Обрабатываем картинки
                $product['no_image'] = $image->get(null, http_host().'/images/no_image.png', $moduleArr['config']["image_width"], $moduleArr['config']["image_height"]);
                $image_1 = '';
                $image_1 = (isset($data['image']['0']['image_path'])) ? clean($data['image']['0']['image_path']) : null;
                if (isset($image_1)) {$product['image']['1'] = $image->get($data['product_id'], $image_1, $moduleArr['config']["image_width"], $moduleArr['config']["image_height"]);}
                $image_2 = '';
                $image_2 = (isset($data['image']['1']['image_path'])) ? clean($data['image']['1']['image_path']) : null;
                if (isset($image_2)) {$product['image']['2'] = $image->get($data['product_id'], $image_2, $moduleArr['config']["image_width"], $moduleArr['config']["image_height"]);}
                
                $product['url'] = '/product/'.$data['id'].'/'.$data['alias'].'.html';
                $product['name'] = (isset($data['name'])) ? clean($data['name']) : '';
                $product['type'] = (isset($data['type'])) ? clean($data['type']) : '';
                $product['brand'] = (isset($data['brand'])) ? clean($data['brand']) : '';
                $product['serie'] = (isset($data['serie'])) ? clean($data['serie']) : '';
                $product['articul'] = (isset($data['articul'])) ? clean($data['articul']) : '';
                if ($data['serie'] && $data['articul']) {$product['name'] = $data['serie'].' '.$data['articul'];}
                $product['oldprice'] = (isset($data['oldprice_out'])) ? clean($data['oldprice_out']) : '';
                $product['price'] = (isset($data['price_out'])) ? clean($data['price_out']) : '';
                $product['available'] = (isset($data['available'])) ? clean($data['available']) : '';
                $product['product_id'] = (isset($data['product_id'])) ? clean($data['product_id']) : '';
                $product['shortname'] = 'грн.';
                $product['currency'] = 'UAH';
				
				if (isset($product['action_date'])) {
                    $date = $product['action_date'];
                } else {
                    $date = date_rand_min(1000, 5000);
                }

				$product = array_replace_recursive($product, date_arr($date)); 
                $products[] = $product;
            }
        }
        $resp['content'] = $products;

        $content = [];
        $content['content']['modules'][$this->modulKey] = $moduleArr + $resp;
        return $content;
    }

    public function post(Request $request)
    {
		return null;
	}

}
 