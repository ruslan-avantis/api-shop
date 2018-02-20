<?php /**
    * This file is part of the Hooks
    *
    * @license http://opensource.org/licenses/MIT
    * @link https://github.com/pllano/hooks
    * @version 1.0.1
    * @package pllano.hooks
    *
    * For the full copyright and license information, please view the LICENSE
    * file that was distributed with this source code.
*/
 
namespace ApiShop;
 
use Psr\Http\Message\{ServerRequestInterface as Request, ResponseInterface as Response};
 
class ModuleManager
{
    private $config;
    private $package;
    private $template;
    private $route;
    private $block;
    private $lang = null;
    private $language = null;
    private $modules = [];
 
    function __construct($config = [], $package = [], $template = [], $block, $route, $lang = null, $language = null)
    {
        $this->config = $config;
        $this->package = $package;
        $this->template = $template;
        $this->block = $block;
        $this->route = $route;
        if (isset($lang)) {
            $this->lang = $lang;
        }
        if (isset($language)) {
            $this->language = $language;
        }
        if (isset($this->config['modules'][$this->block])) {
            $this->modules = $this->config['modules'][$this->block];
        }
    }
 
    public function get(Request $request, Response $response, array $args = [])
    {
        $resp = [];
        if (isset($this->modules)) {
            foreach($this->modules as $key => $val)
            {
                if($val['state'] == 1) {
                    if (isset($val['vendor'])) {
                        $package = [];
                        //print_r($this->package['require'][$val['package']]);
                        if (isset($this->package['require'][$val['package']])) {
                            $package = $this->package['require'][$val['package']];
                        }
                        //$conf = array_replace_recursive($this->config, $package, $this->template, $key, $this->block, $this->route, $this->lang, $this->language);
                        $plugin = new $val['vendor']($this->config, $package, $this->template, $key, $this->block, $this->route, $this->lang, $this->language);
                        $function = $val['function'];
                        $arr = $plugin->$function($request, $response, $args);
                    } else {
                        if ($this->block == $this->route) {
                            $arr['content']['modules'][$key]['config'] = $this->modules[$key];
                        } else {
                            $arr[$this->block]['modules'][$key]['config'] = $this->modules[$key];
                        }
                    }
                    $resp = array_replace_recursive($resp, $arr);
                }
            }
        }
        return $resp;
    }
 
    public function post(Request $request, Response $response, array $args = [])
    {
        $resp = [];
        if (isset($this->modules[$this->block])) {
            if($this->modules[$this->block]['state'] == 1) {
                if (isset($this->modules[$this->block]['vendor'])) {
                    $package = [];
                    //print_r($this->package['require'][$val['package']]);
                    if (isset($this->package['require'][$this->block])) {
                        $package = $this->package['require'][$this->block];
                    }
                    $plugin = new $this->modules[$this->block]['vendor']($this->config, $package, $this->template, $this->block, $this->block, $this->route, $this->lang, $this->language);
                    $function = $this->modules[$this->block]['function'];
                    $arr = $plugin->$function($request, $response, $args);
                }
                $resp = array_replace_recursive($resp, $arr);
            }
        }
        return $resp;
    }
 
}
 