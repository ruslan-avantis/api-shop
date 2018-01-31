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
 
class HookIndexGet {
 
    private $args;
    private $request;
    private $response;
    private $query;
    private $view;
    private $render;
 
    public function http(Request $request, Response $response, array $args, $query = null)
    {
        $this->args = $args;
        $this->request = $request;
        $this->response = $response;
        $this->query = $query;
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
        // print_r($postParams);
    }
 
    public function get($view = null, $render = null)
    {
        $this->view = $view;
        $this->render = $render;
        $this->run();
    }
 
    public function run()
    {
        // Обрабатываем данные
        // $this->render = '404.html';
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
 
    public function view()
    {
        return $this->view;
    }
 
    public function render()
    {
        return $this->render;
    }
 
}
 