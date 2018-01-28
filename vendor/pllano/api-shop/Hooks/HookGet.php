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
 
use ApiShop\Config\Settings;
 
class HookGet {
 
    private $config;
    private $args = array();
    private $view;
    private $render;
	private $request;
 
    function __construct()
    {
        $config = (new Settings())->get();
        $this->config = $config;
    }
 
    public function run($request, array $args, $view, $render)
    {
        $this->args = $args;
        $this->view = $view;
        $this->render = $render;
        $this->request = $request;
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
 