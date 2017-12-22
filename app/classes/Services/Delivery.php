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
 
namespace ApiShop\Services;

use ApiShop\Core\Settings;
use ApiShop\Services\Payment;
use ApiShop\Services\Marketplace;
 
/**
 * Оплаты
*/
class Delivery
{
	
    public function __construct($service = null)
    {
        if ($service !== null) {
            $this->service = $service;
        }
    }
	
    public function run($id = null, array $arr = array())
    {
        // тело GET
		// $id - записи
		// $arr - массив с параметрами
    }
}
 