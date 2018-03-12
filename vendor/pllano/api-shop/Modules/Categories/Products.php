<?php 
/**
 * Pllano {API}$hop (https://pllano.com)
 *
 * @link https://github.com/pllano/api-shop
 * @version 1.2.1
 * @copyright Copyright (c) 2017-2018 PLLANO
 * @license http://opensource.org/licenses/MIT (MIT License)
 */
namespace Pllano\ApiShop\Modules\Categories;

use Psr\Container\ContainerInterface as Container;
use Pllano\Core\Adapters\Image;

class Products
{
    private $counts = 0;
    private $app;
	private $config;

    private $db;
    private $_routerDb;
    private $_database;
    private $_table;
	private $_idField;
	private $_driver;
	private $_adapter;
	private $_format;
	

    function __construct(Container $app)
    {
        $this->app = $app;
		$this->config = $this->app->get('config');
		$this->_table = 'price';
		$this->_routerDb = $this->app->get('routerDb');
		$this->_database = $this->_routerDb->ping($this->_table);
		if (isset($this->config['db']['resource'][$this->_database])) {
			$configDatabase = $this->config['db']['resource'][$this->_database];
		    if (isset($configDatabase['driver'])) {
			    $this->_driver = $configDatabase['driver'];
		    }
		    if (isset($configDatabase['adapter'])) {
			    $this->_adapter = $configDatabase['adapter'];
		    }
		    if (isset($configDatabase['format'])) {
			    $this->_format = $configDatabase['format'];
		    }
		}
        $this->_routerDb->setConfig([], $this->_driver, $this->_adapter, $this->_format);
        $this->db = $this->_routerDb->run($this->_database);
	}

    public function get(array $query = [], array $moduleArr = [], $host)
    {
		// Database GET
		$responseArr = $this->db->get($this->_table, $query) ?? [];

        if (isset($responseArr)) {
			$this->counts = count($responseArr);
            foreach($responseArr as $data)
            {
                $image = new Image($this->app);
                $product['no_image'] = $image->get(null, http_host().'/images/no_image.png', $moduleArr['config']["image_width"], $moduleArr['config']["image_height"]);
                $image_1 = '';
                $image_1 = (isset($data['image']['0']['image_path'])) ? clean($data['image']['0']['image_path']) : null;
                if (isset($image_1)) {$product['image']['1'] = $image->get($data['product_id'], $image_1, $moduleArr['config']["image_width"], $moduleArr['config']["image_height"]);}
                $image_2 = '';
                $image_2 = (isset($data['image']['1']['image_path'])) ? clean($data['image']['1']['image_path']) : null;
                if (isset($image_2)) {$product['image']['2'] = $image->get($data['product_id'], $image_2, $moduleArr['config']["image_width"], $moduleArr['config']["image_height"]);}
				
                $path_url = pathinfo($data['url']);
                $basename = $path_url['basename']; // lib.inc.php
                $baseurl = str_replace('-'.$data['product_id'].'.html', '', $basename);

                $product['url'] = '/product/'.$data['id'].'/'.$baseurl.'.html';
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
        return $this->counts;
	}
	
}
 