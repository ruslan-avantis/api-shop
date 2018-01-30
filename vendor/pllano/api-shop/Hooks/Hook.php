<?php
/**
 * This file is part of the API SHOP
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/pllano/api-shop
 * @version 1.0.1
 * @package pllano.api-shop
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
namespace ApiShop\Hooks;
 
use Slim\Http\Request;
use Slim\Http\Response;
 
use ApiShop\Config\Settings;
 
class Hook {
 
    private $config;
    private $args = array();
    private $view;
    private $render;
    private $request;
    private $response;
 
    function __construct()
    {
        $config = (new Settings())->get();
        $this->config = $config['hooks'];
    }
 
    public function setResponse(Request $request, Response $response, array $args, $view, $render)
    {
        $this->args = $args;
        $this->view = $view;
        $this->render = $render;
        $this->request = $request;
        $this->response = $response;
        $this->runResponse();
    }
 
    public function runResponse()
    {
        $hooks = $this->getHooks('GET');
        if(isset($hooks[0])) {
            foreach($hooks as $value)
            {
                try {
                    $vendor = $value['vendor'];
                    $hook = new $vendor();
                    $hook->run($this->request, $this->response, $this->args, $this->view, $this->render);
                    $this->view = $hook->view();
                    $this->render = $hook->render();
                } catch (\Exception $ex) {
                    return false;
                }
            }
            return true;
        } else {
            return false;
        }
    }
 
    public function setRequest(Request $request, Response $response, array $args)
    {
        $this->args = $args;
        $this->request = $request;
        $this->response = $response;
        $this->runRequest();
    }
 
    public function runRequest()
    {
        $hooks = $this->getHooks('POST');
        foreach($hooks as $value)
        {
            try {
                $vendor = $value['vendor'];
                $hook = new $vendor();
                $hook->run($this->request, $this->response, $this->args);
                $this->request = $hook->request();
                $this->response = $hook->response();
                $this->args = $hook->args();
            } catch (\Exception $ex) {
                return false;
            }
        }
        return true;
    }
 
    public function view()
    {
        return $this->view;
    }
 
    public function render()
    {
        return $this->render;
    }
 
    public function request()
    {
        return $this->request;
    }
 
    public function response()
    {
        return $this->response;
    }
 
    public function args()
    {
        return $this->args;
    }
    
    public function getHooks($request)
    {
        $hooks = array();
        $hook = null;
        foreach($this->config as $key => $value)
        {
            if (isset($value['render'])) {
                if($value['request'] == $request && $value['render'] == $this->render) {
                    $hook['vendor'] = $value['vendor'];
                } elseif ($value['request'] == $request && $value['render'] == 'all') {
                    $hook['vendor'] = $value['vendor'];
                }
            } else {
                if($value['request'] == $request) {
                    $hook['vendor'] = $value['vendor'];
                }
            }
        }
        $hooks[] = $hook;
 
        return $hooks;
 
    }
 
}
 