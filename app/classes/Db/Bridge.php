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
 
namespace Pllano\ApiShop\Db;

use Pllano\ApiShop\Core\Settings;
 
/**
 * Bridge class
*/
class Bridge
{
    /**
     * @param $db
     * @var string
    */
    private $db = null;
    
    public function __construct($db = null)
    {
        if ($db !== null) {
            $this->db = $db;
        }
    }
    
    public function get($resource = null, array $arr = array(), $id = null)
    {
        if ($this->db !== null && $resource !== null) {
            
            $class = ucfirst($this->db."Db");
            $db = new $class();
            return $db->get($resource, $arr, $id);
        } else {
            return false;
        }
    }
    
    public function post($resource = null, array $arr = array(), $id = null)
    {
        if ($this->db !== null && $resource !== null) {
            
            $class = ucfirst($this->db."Db");
            $db = new $class();
            return $db->post($resource, $arr, $id);
        } else {
            return false;
        }
    }
    
    public function put($resource = null, array $arr = array(), $id = null)
    {
        if ($this->db !== null && $resource !== null) {
            
            $class = ucfirst($this->db."Db");
            $db = new $class();
            return $db->put($resource, $arr, $id);
        } else {
            return false;
        }
    }
	
    public function patch($resource = null, array $arr = array(), $id = null)
    {
        if ($this->db !== null && $resource !== null) {
            
            $class = ucfirst($this->db."Db");
            $db = new $class();
            return $db->patch($resource, $arr, $id);
        } else {
            return false;
        }
    }
    
    public function delete($resource = null, array $arr = array(), $id = null)
    {
        if ($this->db !== null && $resource !== null) {
            
            $class = ucfirst($this->db."Db");
            $db = new $class();
            return $db->delete($resource, $arr, $id);
        } else {
            return false;
        }
    }

}
 
