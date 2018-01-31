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
    private $request;
    private $response;
    private $args = array();
    private $view = array();
    private $render = null;
    private $resource = null;
    private $name_db = null;
    private $query = null;
	private $coverage = null;
    private $postArr = array();
    private $postQuery = null;
    private $id = null;
    private $callback = null;
    private $hooks = null;
 
    function __construct()
    {
        $config = (new Settings())->get();
        $this->config = $config['hooks'];
    }
 
    public function http(Request $request, Response $response, array $args, $query = null, $coverage = null)
    {
        $this->args = $args;
        $this->request = $request;
        $this->response = $response;
        $this->query = $query;
		$this->coverage = $coverage;
        $this->set();
    }
 
    public function set()
    {
        $hooks = $this->hooks($this->query);
        if(isset($hooks[0])) {
            foreach($hooks as $value)
            {
                if(isset($value['vendor'])) {
                    $vendor = $value['vendor'];
                    $hook = new $vendor();
                    $hook->http($this->request, $this->response, $this->args, $this->query);
                    $this->request = $hook->request();
                    $this->response = $hook->response();
                    $this->args = $hook->args();
                }
            }
            return true;
        } else {
            return false;
        }
    }
 
    public function get($view = null, $render = null)
    {
        $this->view = $view;
        $this->render = $render;
        $this->run();
    }
 
    public function post($resource = null, $name_db = null, $postQuery = null, array $postArr = array(), $id = null)
    {
        $this->resource = $resource;
        $this->name_db = $name_db;
        $this->postQuery = $postQuery;
        $this->postArr = $postArr;
        $this->id = $id;
        $this->run();
    }
 
    public function run()
    {
        $hooks = $this->hooks($this->query);
        if(isset($hooks[0])) {
            foreach($hooks as $value)
            {
                if(isset($value['vendor'])) {
                    $vendor = $value['vendor'];
                    $hook = new $vendor();
                    if ($this->query == 'GET') {
                        $hook->get($this->view, $this->render);
                        $this->view = $hook->view();
                        $this->render = $hook->render();
                    } elseif ($this->query == 'POST') {
                        $hook->post($this->resource, $this->name_db, $this->postQuery, $this->postArr, $this->id);
                        $this->callback = $hook->callback($this->callback);
                    }
                }
            }
            return true;
        } else {
            return false;
        }
    }
 
    public function hooks($query = null)
    {
        $hooks = array();
        $hook = null;
        foreach($this->config as $key => $value)
        {
            if (isset($value['state']) && $value['state'] == '1') {
				if ($value['coverage'] == $this->coverage || $value['coverage'] == 'all') {
			        if (isset($value['render']) && $value['render'] != '' && $value['render'] != ' ') {
                        if($value['query'] == $query && $value['render'] == $this->render) {
                            $hook['vendor'] = $value['vendor'];
                            $hooks[] = $hook;
                        } elseif ($value['query'] == $query && $value['render'] == 'all') {
                            $hook['vendor'] = $value['vendor'];
                            $hooks[] = $hook;
                        } elseif ($value['query'] == 'all' && $value['render'] == 'all') {
                            $hook['vendor'] = $value['vendor'];
                            $hooks[] = $hook;
                        }
                    } else {
                        if($value['query'] == $query) {
                            $hook['vendor'] = $value['vendor'];
                            $hooks[] = $hook;
                        } elseif ($value['query'] == 'all') {
                            $hook['vendor'] = $value['vendor'];
                            $hooks[] = $hook;
                        }
                    }
                }
			}
		}
 
        return $hooks;
 
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
 
    public function query()
    {
        return $this->query;
    }
 
    public function coverage()
    {
        return $this->coverage;
	}
 
    public function view()
    {
        return $this->view;
    }
 
    public function render()
    {
        return $this->render;
    }
 
    public function resource()
    {
        return $this->resource;
    }
 
    public function name_db()
    {
        return $this->name_db;
    }
 
    public function postArr()
    {
        return $this->postArr;
    }
 
    public function postQuery()
    {
        return $this->postQuery;
    }
 
    public function id()
    {
        return $this->id;
    }
 
    public function callback($callback = null)
    {
        if(isset($this->callback)) {
            return $this->callback;
        } else {
            return $callback;
        }
    }
 
}
 