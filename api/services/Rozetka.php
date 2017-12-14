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
 
namespace Pllano\ApiShop\Services\Marketplace;

use Pllano\ApiShop\Core\Settings;
use Pllano\ApiShop\Model\Payments;
use Pllano\ApiShop\Model\Delivery;
use Pllano\ApiShop\Model\Marketplace;
 
/**
 * Пример промежуточного интерфейса для взаимодействия платежной системы и API Shop
 * Название класса должно начинаться с заглавной буквы, 
 * все остальные буквы должны быть в нижнем регистре.
*/
class Rozetka
{
    public function get($resource = null, $id = null, array $arr = array())
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
	
    public function put($resource = null, $id = null, array $arr = array())
    {
        // тело PUT
		// $resource - ресурс (модель)
		// $id - записи
		// $arr - массив с параметрами
    }
	
    public function patch($resource = null, $id = null, array $arr = array())
    {
        // тело PATCH
		// $resource - ресурс (модель)
		// $id - записи
		// $arr - массив с параметрами
    }

    public function delete($resource = null, $id = null, array $arr = array())
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
 