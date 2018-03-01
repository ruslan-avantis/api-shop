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

namespace Pllano\ApiShop\Modules;
 
use Psr\Http\Message\ServerRequestInterface as Request;
 
class Breadcrumbs
{
    private $config;
    private $package;
    private $template;
    private $route;
    private $block;
    private $module;
    private $lang = null;
    private $language = null;
 
    function __construct($config = [], $package = [], $template = [], $module, $block, $route, $lang = null, $language = null)
    {
        $this->config = $config;
        $this->package = $package;
        $this->template = $template;
        $this->route = $route;
        $this->block = $block;
        $this->module = $module;
        if (isset($lang)) {
            $this->lang = $lang;
        }
        if (isset($language)) {
            $this->language = $language;
        }
    }
 
    public function get(Request $request)
    {
        // Конфигурация пакета
        $moduleArr['config'] = $this->config['modules'][$this->route][$this->module];
        $layout['content']['modules'][$this->module]['content']['layout'] = $moduleArr['config']['view'];
        $content['content']['modules'][$this->module] = $moduleArr;
        $return = array_replace_recursive($layout, $content);
        return $return;
    }

}
 