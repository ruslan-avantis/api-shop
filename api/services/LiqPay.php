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
 
namespace Services\Payments;

use Core\Settings;
use Core\Payment;
 
/**
 * Пример промежуточного интерфейса для взаимодействия платежной системы и API Shop
 * Название класса должно начинаться с заглавной буквы, 
 * все остальные буквы должны быть в нижнем регистре.
*/
class Liqpay
{
    public function get($id = null, array $arr = array())
    {
        // тело GET
		// $id - записи
		// $arr - массив с параметрами
    }
	
    public function post(array $arr = array())
    {
        // тело POST
		// $arr - массив с параметрами
    }
	
    public function put($id = null, array $arr = array())
    {
        // тело PUT
		// $id - записи
		// $arr - массив с параметрами
    }
	
    public function patch($id = null, array $arr = array())
    {
        // тело PATCH
		// $id - записи
		// $arr - массив с параметрами
    }

    public function delete($id = null, array $arr = array())
    {
        // тело DELETE
		// $id - записи
		// $arr - массив с параметрами
    }
}
 