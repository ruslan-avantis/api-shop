<?php
/**
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
 
namespace Pllano\ApiShop\Models;
 
use Psr\Http\Message\ServerRequestInterface as Request;
 
class Security {
 
    function __construct($config)
    {
        $this->config = $config;
    }
 
    // Сообщение об Атаке или подборе токена
    public function token(Request $request)
    {
        // Отправляем сообщение администратору
    }
 
    // Сообщение об Атаке или подборе csrf
    public function csrf(Request $request)
    {
        // Отправляем сообщение администратору
    }
 
}
 