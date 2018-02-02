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
 
class HookAll {
 
    private $request;
    private $response;
    private $args = [];
    private $view = [];
    private $render = null;
    private $resource = null;
    private $name_db = null;
    private $query = null;
    private $coverage = null;
    private $postArr = [];
    private $postQuery = null;
    private $id = null;
    private $callback = null;
 
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
        // Обрабатываем данные
        // Получаем GET параметры
        $getParams = $this->request->getQueryParams();
        // print_r($getParams);
        // Получаем данные отправленные нам через POST
        $postParams = $this->request->getParsedBody();
    }
 
    public function get($view = null, $render = null)
    {
        $this->view = $view;
        $this->render = $render;
        $this->run();
    }
 
    public function post($resource = null, $name_db = null, $postQuery = null, array $postArr = [], $id = null)
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
        // Обрабатываем данные
        $this->render = '404.html';
    }
 
    public function state()
    {
        return true;
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
 