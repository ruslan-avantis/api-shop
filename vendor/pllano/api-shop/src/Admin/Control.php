<?php
/**
* This file is part of the REST API SHOP library
*
* @license http://opensource.org/licenses/MIT
* @link https://github.com/API-Shop/api-shop
* @version 1.0
* @package api-shop.api-shop
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
 
namespace ApiShop\Admin;
 
class Control {
 
    function __construct($config)
    {
        $this->config = $config;
    }
 
    // Проверяем разрешен ли этот тип запроса для данного ресурса
    public function test($resource) {
        $config = $this->config;
        // Если ресурс активен
        if (isset($config["settings"]["admin"]["resource"][$resource])) {
            if ($config["settings"]["admin"]["resource"][$resource] == true) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
 
    public function delete($dir)
    {
       $files = array_diff(scandir($dir), ['.','..']);
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->delete("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }
}
 