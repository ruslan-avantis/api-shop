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

use Psr\Http\Message\{ServerRequestInterface as Request, ResponseInterface as Response};
use Psr\Container\ContainerInterface as Container;
use Pllano\Interfaces\ModuleInterface;
use Pllano\Core\Module;

class ModuleCategory extends Module implements ModuleInterface
{

    public function __construct(Container $app, $route = null, $block = null, $modulKey = null, $modulVal = [])
    {
        $this->_table = 'category';
        $this->_idField = 'category_id';
		parent::__construct($app, $route, $block, $modulKey, $modulVal);
		$this->connectContainer();
    }

    public function get(Request $request)
    {
        $host = $request->getUri()->getHost();
        $path = $request->getUri()->getPath();
        // Получаем alias из url
        $alias = null;
        if ($request->getAttribute('alias')) {
            $alias = clean($request->getAttribute('alias'));
        }

        // Конфигурация пакета
		$moduleArr = [];
        $moduleArr['config'] = $this->modulVal;

        $content = [];
        $contentArr = [];
        $heads = [];
        $return = [];
        $render = '';
        $data = [];
 
        $render = $moduleArr['config']['view'] ? $moduleArr['config']['view'] : $this->template['layouts'][$this->route];
        
        $products_template = $moduleArr['config']['helper']['products_list'] ? $moduleArr['config']['helper']['products_list'] : 'helper/products.html';
        $head["products_limit"] = $moduleArr['config']['limit'];
        $head["products_order"] = $moduleArr['config']['order'];
        $head["products_sort"] = $moduleArr['config']['sort'];

		$product_type = null;

        if (isset($alias)) {
            

            $query = [
                "alias" => $alias,
			    "state" => 1
            ];
            $responseArr = $this->db->get($this->_table, $query) ?? [];

            if (isset($responseArr['0'])) {
                    $data = $responseArr['0'];
                    if(is_object($data)) {
                        $data = (array)$data;
                    }

                    $head["title"] = $data['seo_title'] ? $data['seo_title'] : $data['title'];
                    $head["keywords"] = $data['seo_keywords'] ? $data['seo_keywords'] : $data['title'];
                    $head["description"] = $data['seo_description'] ? $data['seo_description'] : $data['title'];
                    $head["og_title"] = $data['og_title'] ? $data['og_title'] : $data['title'];
                    $head["og_description"] = $data['og_description'] ? $data['og_description'] : $data['title'];
                    $head["og_image"] = $data['og_image'] ? $data['og_image'] : '';
                    $head["og_type"] = $data['og_type'] ? $data['og_type'] : '';
                    $head["robots"] = $data['robots'] ? $data['robots'] : 'index, follow';
                    $head["products_template"] = $data['products_template'] ? $data['products_template'] : $moduleArr['config']['helper']['products_list'];
                    $head["products_limit"] = $data['products_limit'] ? $data['products_limit'] : $moduleArr['config']['limit'];
                    $head["products_order"] = $data['products_order'] ? $data['products_order'] : $moduleArr['config']['order'];
                    $head["products_sort"] = $data['products_sort'] ? $data['products_sort'] : $moduleArr['config']['sort'];
 
                    if (isset($data['categories_template'])) {
                        $themes_dir = $this->config["template"]["front_end"]["themes"]["dir"];
                        $templates_dir = $this->config["template"]["front_end"]["themes"]["templates"];
                        $template_name = $this->config["template"]["front_end"]["themes"]["template"];
                        $templates_test = $themes_dir.'/'.$templates_dir.'/'.$template_name.'/layouts/'.$data['categories_template'];
                        if (file_exists($templates_test)) {
                            $render = $data['categories_template'] ? $data['categories_template'] : $moduleArr['config']['view'];
                        }
                    }
                    // Собираем данные в массив
                    $heads['head'] = $head;
            }
            if (isset($data['product_type'])) {
                //$product_type = explode(',', str_replace(['"', "'", " "], '', $data['product_type']));
                $product_type = $data['product_type'];
            }
        }

        // Получаем массив параметров uri
        $queryParams = $request->getQueryParams();
        $arr = [];
        $arr['state'] = 1;
        $arr['offset'] = 0;
        $arr['limit'] = $head["products_limit"];
        $arr['order'] = $head["products_order"];
        $arr['sort'] = $head["products_sort"];
        if (count($queryParams) >= 1) {
            foreach($queryParams as $key => $value)
            {
                if (isset($key) && isset($value)) {
                    $arr[$key] = clean($value);
                }
            }
        }
 
        // Собираем полученные параметры в url и отдаем шаблону
        $get_array = http_build_query($arr);
        // Вытягиваем URL_PATH для правильного формирования юрл
        //$url_path = parse_url($request->getUri(), PHP_URL_PATH);
        $url_path = $path;
        // Подключаем сортировки
        $filter = new $moduleArr['config']['vendors']['filter']($url_path, $arr);
 
        $orderArray = $filter->order();
 
        $limitArr = [
            "6" => "6",
            "15" => "15",
            "30" => "30",
            "60" => "60",
            "90" => "90",
            "120" => "120"
        ];
        $limitArray = $filter->limit($limitArr);

		$language = $this->languages->get($request);

        // Формируем массив по которому будем сортировать
		$sortArr = [];
		if (isset($language)) {
            $sortArr = [
            "name" => $language["51"],
            "type" => $language["46"],
            "brand" => $language["47"],
            "serie" => $language["48"],
            "articul" => $language["49"],
            "price" => $language["112"]
            ];
		}
        $sortArray = $filter->sort($sortArr);
 
        if (isset($product_type)) {
            $arrPlus['type'] = $product_type;
        }
        $arrPlus['relations'] = "image";
        $newArr = $arr + $arrPlus;
        
        // Получаем список товаров
        $vendorCategory = new $moduleArr['config']['vendors']['products']($this->app);
        $contentArr['content']['products'] = $vendorCategory->get($newArr, $moduleArr, $host);
 
        // Даем пагинатору колличество
        $count = $vendorCategory->count();
        $paginator = $filter->paginator($count);
        
        // Собираем данные в массив
        $contentArr['content']['products_template'] = $products_template;
        $contentArr['content']['paginator'] = $paginator;
        $contentArr['content']['order'] = $orderArray;
        $contentArr['content']['sort'] = $sortArray;
        $contentArr['content']['limit'] = $limitArray;
        $contentArr['content']['param'] = $arr;
        $contentArr['content']['count'] = $count;
        $contentArr['content']['get_array'] = $get_array;
        $contentArr['content']['url_path'] = $url_path;
        $contentArr['content']['render'] = $render;
        $contentArr['content']['template'] = $this->template;
		
        // Получаем свойства товаров
        $vendorProperties = new $moduleArr['config']['vendors']['properties']($this->app);
        $contentArr['content']['properties'] = $vendorProperties->get($request, $contentArr, $moduleArr);
 
        $content['content']['modules'][$this->modulKey] = array_replace_recursive($contentArr, $moduleArr);
        $return = array_replace_recursive($heads, $content);
 
        
        //print("<br>{$alias}<br>");
 
        return $return;
    }

    public function post(Request $request)
    {
		return null;
	}

}
 