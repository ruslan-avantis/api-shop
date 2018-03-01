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

use Psr\Http\Message\ServerRequestInterface as Request;
use Pllano\RouterDb\Router as RouterDb;
use Pllano\ApiShop\Utilities\Utility;
 
class Properties
{

    private $config;
	private $module;

    function __construct(array $config = [], $module = [])
    {
        $this->config = $config;
		$this->module = $module;
    }

    // Лимит товаров на страницу
    public function get(Request $request, $contentArr)
    {
        $response = [];
/*         foreach($contentArr as $key => $unit)
        {
            if (isset($this->offset)){$arr['offset'] = $this->offset;}
            if (isset($this->order)){$arr['order'] = $this->order;}
            if (isset($this->sort)){$arr['sort'] = $this->sort;}
            if (isset($key)){$arr['limit'] = $key;}
            if (isset($this->brand_id)){$arr['brand_id'] = $this->brand_id;}
            if (isset($this->type)){$arr['type'] = $this->type;}
            if (isset($this->brand)){$arr['brand'] = $this->brand;}
            if (isset($this->serie)){$arr['serie'] = $this->serie;}
            if (isset($this->articul)){$arr['articul'] = $this->articul;}
            if (isset($this->name)){$arr['search'] = $this->name;}
            if (isset($this->alias)){$arr['alias'] = $this->alias;}
            $resp["url"] = $this->url_path.'?'.http_build_query($arr);
            $resp["key"] = $key;
            $resp["name"] = $unit;
            $response[] = $resp;
        } */
        return $response;

    }

}
     