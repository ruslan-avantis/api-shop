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
		parent::__construct($app, $route, $block, $modulKey, $modulVal);
		//$this->connectContainer();
		$this->connectDatabases();
        $this->_table = 'price';
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
		
        $responseArr = [];
        // Отдаем роутеру RouterDb конфигурацию
        $this->routerDb->setConfig([], 'Pllano', 'Apis');
        // Пингуем для ресурса указанную и доступную базу данных
        $this->_database = $this->routerDb->ping($this->_table);
        // Подключаемся к БД через выбранный Adapter: Sql, Pdo или Apis (По умолчанию Pdo)
        $this->db = $this->routerDb->run($this->_database);
        // Массив c запросом
        $query = [
            "alias" => $alias,
			"state" => 1
        ];
        // Отправляем запрос к БД в формате адаптера. В этом случае Apis
        $responseArr = $this->db->get($this->_table, $query);

        if (isset($responseArr['0']) {
			$data = $responseArr['0'];
            if(is_object($data)) {
                $data = (array)$data;
            }

            // Если ответ не пустой
            // Обрабатываем картинки
            $images = [];
            $image = new Image($this->app);
            foreach($data['image'] as $value)
            {
                $img = $value['image_path'] ?? null;
                if (isset($img)) {
                    $images[] = $image->get($data['product_id'], $img, $moduleArr['config']["image_width"], $moduleArr['config']["image_height"]);
                } else {
                    $data['images'] = $image->get(null, http_host().'/images/no_image.png', $moduleArr['config']["image_width"], $moduleArr['config']["image_height"]);
                }
            }
            $data['images'] = $images;
			
			

            // Формируем URL страницы товара
            $path_url = pathinfo($data['url']);
            $basename = $path_url['basename'];
            $baseurl = str_replace('-'.$data['product_id'].'.html', '', $basename);
            //$data['url'] = $scheme.'/product/'.$data['id'].'/'.$baseurl.'.html';
            $data['url'] = '/product/'.$data['id'].'/'.$baseurl.'.html';
            $data['name'] = (isset($data['name'])) ? clean($data['name']) : '';
            $data_name = $data['name'];
            $data['description'] = $data['description']['text'] ?? '';
            $data['type'] = $data['type'] ?? '';
            $data['brand'] = $data['brand'] ?? '';
            $data['serie'] =  $data['serie'] ?? '';
            $data['articul'] = $data['articul'] ?? '';
            if ($data['serie'] && $data['articul']) {$data['name'] = $data['serie'].' '.$data['articul'];}
            $data['oldprice'] = $data['oldprice'] ?? null;
            $data['price'] = $data['price'] ?? '';
            $data['available'] = $data['available'] ?? '';
            $data['product_id'] = $data['product_id'] ?? '';
            $date = $data['action_date'] ?? date_rand_min(1000, 5000);
            $contentArr['content'] = $data + date_arr($date);

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
 