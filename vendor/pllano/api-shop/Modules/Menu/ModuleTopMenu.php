<?php 
/**
 * Pllano {API}$hop (https://pllano.com)
 *
 * @link https://github.com/pllano/api-shop
 * @version 1.2.1
 * @copyright Copyright (c) 2017-2018 PLLANO
 * @license http://opensource.org/licenses/MIT (MIT License)
 */
namespace Pllano\ApiShop\Modules\Menu;

use Psr\Http\Message\{ServerRequestInterface as Request, ResponseInterface as Response};
use Psr\Container\ContainerInterface as Container;
use Pllano\Interfaces\ModuleInterface;
use Pllano\Core\{Module, Model};

class ModuleTopMenu extends Module implements ModuleInterface
{

    public function __construct(Container $app, string $route = null, string $block = null, string $modulKey = null, array $modulVal = [])
    {
		$this->_table = 'category';
        $this->_idField = 'category_id';
		parent::__construct($app, $route, $block, $modulKey, $modulVal);
    }

    public function get(Request $request)
    {
        $return = [];
        $moduleArr = [];
        $moduleArr['config'] = $this->modulVal;

		$query = ["menu" => 1, "state" => 1, "sort" => "sort", "order" => "ASC", "offset" => 0, "limit" => 5];
		// Database GET
        $responseArr = $this->db->get($this->_table, $query) ?? [];

        if (isset($responseArr)) {
            $resp = [];
            $data = [];
            $submenu = [];
            foreach($responseArr as $val)
            {
                $data['submenu'] = null;
                if ($val->parent_id == 0) {
                    $data['alias'] = $val->alias;
                    $data['title'] = $val->title;
                    $data['title_ru'] = $val->title_ru;
                    $data['title_ua'] = $val->title_ua;
                    $data['title_en'] = $val->title_en;
                    $data['title_de'] = $val->title_de;
                    $data['url'] = $val->url;

					$parent = $this->db->get($this->_table, ["menu" => 1, "state" => 1, "parent_id" => $val->id]);
                    if (isset($parent)) {
						$submenu['subsubmenu'] = null;
						foreach($parent as $sub)
						{
							$submenu['alias'] = $sub->alias;
							$submenu['title'] = $sub->title;
							$submenu['title_ru'] = $sub->title_ru;
							$submenu['title_ua'] = $sub->title_ua;
							$submenu['title_en'] = $sub->title_en;
							$submenu['title_de'] = $sub->title_de;
							$submenu['url'] = $sub->url;
							
							$subparent = $this->db->get($this->_table, ["menu" => 1, "state" => 1, "parent_id" => $sub->id]);
							if (isset($subparent)) {
								$submenu['subsubmenu'] = [];
								$subsubmenu = [];
								foreach($subparent as $subsub)
								{
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
							$data['submenu'][] = $submenu;
						}
					}
                    $resp[] = $data;
				}
			}
            $content['content'] = $resp;
            $return['content']['modules'][$this->modulKey] = array_replace_recursive($content, $moduleArr);
		}
        return $return;
	}
	
	public function post(Request $request)
    {
		return null;
	}
}
 