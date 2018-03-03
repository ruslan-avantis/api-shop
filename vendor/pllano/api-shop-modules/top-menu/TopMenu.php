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

namespace Pllano\ApiShop\Modules\Menu;

use Psr\Http\Message\{ServerRequestInterface as Request, ResponseInterface as Response};
use Psr\Container\ContainerInterface as Container;
use Pllano\RouterDb\Router as RouterDb;

class TopMenu
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
        // Конфигурация пакета
        $moduleArr = [];
        $moduleArr['config'] = $this->modulVal;
        
        $resource = 'category';
        // Отдаем роутеру RouterDb конфигурацию
        $routerDb = new RouterDb($this->config, 'Apis');
        // Пингуем для ресурса указанную и доступную базу данных
        // Подключаемся к БД через выбранный Adapter: Sql, Pdo или Apis (По умолчанию Pdo)
        $db = $routerDb->run($routerDb->ping($resource));
        // Отправляем запрос к БД в формате адаптера. В этом случае Apis
        $menuArr = $db->get($resource, ["menu" => 1, "state" => 1, "sort" => "sort", "order" => "ASC", "offset" => 0, "limit" => 5]);
        
        $return = [];
        
        if (isset($menuArr['headers']['code']) && (int)$menuArr['headers']['code'] == 200) {
            $resp = [];
            $item = [];
            $submenu = [];
            foreach($menuArr['body']['items'] as $value)
            {
                $item['submenu'] = null;
				$val = $value['item'];
                if ($val->parent_id == 0) {
                    $item['alias'] = $val->alias;
                    $item['title'] = $val->title;
                    $item['title_ru'] = $val->title_ru;
                    $item['title_ua'] = $val->title_ua;
                    $item['title_en'] = $val->title_en;
                    $item['title_de'] = $val->title_de;
                    $item['url'] = $val->url;

					$parent = $db->get($resource, ["menu" => 1, "state" => 1, "parent_id" => $val->id]);
                    
                    if (isset($parent['headers']['code']) && (int)$parent['headers']['code'] == 200) {
						$submenu['subsubmenu'] = null;
						
						foreach($parent['body']['items'] as $subvalue)
						{
							$sub = $subvalue['item'];
							$submenu['alias'] = $sub->alias;
							$submenu['title'] = $sub->title;
							$submenu['title_ru'] = $sub->title_ru;
							$submenu['title_ua'] = $sub->title_ua;
							$submenu['title_en'] = $sub->title_en;
							$submenu['title_de'] = $sub->title_de;
							$submenu['url'] = $sub->url;
							
							$subparent = $db->get($resource, ["menu" => 1, "state" => 1, "parent_id" => $sub->id]);
							if (isset($subparent['headers']['code']) && (int)$subparent['headers']['code'] == 200) {
								$submenu['subsubmenu'] = [];
								$subsubmenu = [];
								foreach($subparent['body']['items'] as $subsubvalue)
								{
									$subsub = $subsubvalue['item'];
									$subsubmenu['alias'] = $subsub->alias;
									$subsubmenu['title'] = $subsub->title;
									$subsubmenu['title_ru'] = $subsub->title_ru;
									$subsubmenu['title_ua'] = $subsub->title_ua;
									$subsubmenu['title_en'] = $subsub->title_en;
									$subsubmenu['title_de'] = $subsub->title_de;
									$subsubmenu['url'] = $subsub->url;
									$submenu['subsubmenu'][] = $subsubmenu;
								}
							}
							$item['submenu'][] = $submenu;
						}
					}
                    $resp[] = $item;
				}
			}
            $content['content'] = $resp;
            $return['content']['modules'][$this->modulKey] = array_replace_recursive($content, $moduleArr);
		}
        return $return;
	}
}
 