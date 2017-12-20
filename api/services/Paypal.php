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
 
namespace Pllano\ApiShop\Services\Payments;

use Pllano\ApiShop\Core\Settings;
use Pllano\ApiShop\Model\Payments;
 
/**
 * Пример промежуточного интерфейса для взаимодействия платежной системы и API Shop
 * Название класса должно начинаться с заглавной буквы, 
 * все остальные буквы должны быть в нижнем регистре.
*/
class Paypal
{
    public function get($resource = null, array $arr = array(), $id = null)
    {
        // тело GET
		// $resource - ресурс (модель)
		// $id - записи
		// $arr - массив с параметрами
    }
	
    public function post($resource = null, array $arr = array())
    {
        // тело POST
		// $resource - ресурс (модель)
		// $arr - массив с параметрами
    }
	
    public function put($resource = null, array $arr = array(), $id = null)
    {
        // тело PUT
		// $resource - ресурс (модель)
		// $id - записи
		// $arr - массив с параметрами
    }
	
    public function patch($resource = null, array $arr = array(), $id = null)
    {
        // тело PATCH
		// $resource - ресурс (модель)
		// $id - записи
		// $arr - массив с параметрами
    }

    public function delete($resource = null, array $arr = array(), $id = null)
    {
        // тело DELETE
		// $resource - ресурс (модель)
		// $id - записи
		// $arr - массив с параметрами
    }

    public function config()
    {
        // тело DELETE
    }

}
 
