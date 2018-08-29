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
use Pllano\Interfaces\ModuleInterface;
use Pllano\Core\Module;
use Pllano\Core\Adapters\Image;

class ModuleProduct extends Module implements ModuleInterface
{

    public function __construct(Container $app, $route = null, $block = null, $modulKey = null, $modulVal = [])
    {
		$this->_table = 'price';
		parent::__construct($app, $route, $block, $modulKey, $modulVal);
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

        $query = [
            "alias" => $alias,
			"state" => 1
        ];
        $responseArr = $this->db->get($this->_table, $query) ?? [];

        if (isset($responseArr['0'])) {

			$product = $responseArr['0'];
            if(is_object($product)) {
                $product = (array)$product;
            }

            // Если ответ не пустой
            // Обрабатываем картинки
            $images = [];
            $image = new Image($this->app);
			$img = null;
            foreach($product['image'] as $value)
            {
                $img = $value['image_path'] ?? null;
                if (isset($img)) {
                    $images[] = $image->get($product['product_id'], $img, $moduleArr['config']["image_width"], $moduleArr['config']["image_height"]);
                } else {
                    $product['images'] = $image->get(null, http_host().'/images/no_image.png', $moduleArr['config']["image_width"], $moduleArr['config']["image_height"]);
                }
            }
            $data['images'] = $images;
			$data['url'] = '/product/'.$product['id'].'/'.$product['alias'].'.html';
            $data['name'] = (isset($product['name'])) ? clean($product['name']) : '';
            $data_name = $data['name'];
            $data['description'] = $product['description']['text'] ?? '';
            $data['type'] = $product['type'] ?? '';
            $data['brand'] = $product['brand'] ?? '';
            $data['serie'] =  $product['serie'] ?? '';
            $data['articul'] = $product['articul'] ?? '';
            if ($product['serie'] && $product['articul']) {$data['name'] = $product['type'].' '.$product['brand'].' '.$product['serie'].' '.$product['articul'];}
            $data['oldprice'] = $product['oldprice_out'] ?? null;
            $data['price'] = $product['price_out'] ?? 0.00;
            $data['available'] = $product['available'] ?? '';
            $data['product_id'] = $product['product_id'] ?? '';
            $date = $product['action_date'] ?? date_rand_min(1000, 5000);
            $contentArr['content'] = $data + date_arr($date);
			
			//print_r($contentArr);

            // Каждый товар может иметь свой уникальный шаблон
            // Если шаблон товара не установлен берем по умолчанию
            if (isset($data['template'])){
                $themes_dir = $this->config["settings"]["themes"]["dir"];
                $templates_dir = $this->config["template"]["front_end"]["themes"]["template"];
                $template_name = $this->config["settings"]["themes"]["template"];
                $templates_test = $themes_dir.'/'.$templates_dir.'/'.$template_name.'/layouts/'.$data['template'];
                if (file_exists($templates_test)) {
                    $render['render'] = $data['template'];
                }
            }

            $head["title"] = $data["title"] ?? $data_name;
            $head["keywords"] = $data["keywords"] ?? $data_name;
            $head["description"] = $data["description"] ?? $data_name;
            $head["seo_title"] = $data["seo_title"] ?? $data_name;
            $head["seo_keywords"] = $data["seo_keywords"] ?? $data_name;
            $head["seo_description"] = $data["seo_description"] ?? $data_name;
            $head["og_url"] = $data["og_url"] ?? $data_name;
            $head["og_title"] = $data["og_title"] ?? $data_name;
            $head["og_description"] = $data["og_description"] ?? $data_name;
            // Собираем данные в массив
            $heads['head'] = $head;
        }

        $content['content']['modules'][$this->modulKey] = $contentArr + $moduleArr;
        $return = array_replace_recursive($heads, $content, $render);
        return $return;
    }

    public function post(Request $request)
    {
		return null;
	}

}
 