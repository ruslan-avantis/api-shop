<?php
/**
    * This file is part of the API SHOP
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/pllano/api-shop
 * @version 1.1.0
 * @package pllano.api-shop
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
namespace ApiShop\Admin;
 
use ApiShop\Config\Settings;
 
class Packages {
 
    private $config;
 
    function __construct()
    {
        $config = (new Settings())->get();
        $this->config = $config;
    }
 
    public function get()
    {
        $vendor_dir = $this->config["dir"]["vendor"].'/';
        if (file_exists($vendor_dir."auto_require.json")) {
            return json_decode(file_get_contents($vendor_dir."auto_require.json"), true);
        } else {
            return null;
        }
    }
    
    public function getOne($name = null)
    {
        $getArr = $this->get();
        if (isset($name)) {
            $arr = array();
            $require = '';
            foreach($getArr['require'] as $get_key => $get_val)
            {
                if (strtolower($get_val['name']) == strtolower($name)) {
                    foreach($get_val as $key => $val)
                    {
                        $arr[$key] = $val;
                    }
                    $require = $arr;
                }
            }

            return $require;
        } else {
            return null;
        } 
    }
 
    public function post(array $param = array())
    {
        $getArr = $this->get();
        $count = count($getArr['require']);
        $new_count=$count;
        $newParam['require'] = array();
 
        if (count($param) >= 1) {
            foreach($param as $param_key => $param_val)
            {
                $i=0;
                foreach($getArr['require'] as $get_key => $get_val)
                {
                    if (strtolower($param_val['name']) == strtolower($get_val['name'])) {
                        $newParam['require'][$get_key] = $param_val;
                    } else {
                        $i+=1;
                    }
                    if($i == $count) {
                        $new_count+=1;
                        $newParam['require'][$new_count] = $param_val;
                    }
                }
            }
        }
 
        $arr = array_replace_recursive($getArr, $newParam);
        $newArr = json_encode($arr);
        $vendor_dir = $this->config["dir"]["vendor"].'/';
        file_put_contents($vendor_dir."auto_require.json", $newArr);
        return true;
    }
 
    public function put(array $param = array())
    {
        $getArr = $this->get();
        $count = count($getArr['require']);
        $new_count=$count;
        $newParam['require'] = array();
        $return = false;
 
        if (count($param) >= 1) {
            foreach($param as $param_key => $param_val)
            {
                $i=0;
                foreach($getArr['require'] as $get_key => $get_val)
                {
                    if (strtolower($param_val['name']) == strtolower($get_val['name'])) {
                        $newParam['require'][$get_key] = $param_val;
                        $return = true;
                    } else {
                        $i+=1;
                    }
                    if($i == $count) {
                        $new_count+=1;
                        $newParam['require'][$new_count] = $param_val;
                        $return = 'new';
                    }
                }
            }
        }
 
        $arr = array_replace_recursive($getArr, $newParam);
        $newArr = json_encode($arr);
        $vendor_dir = $this->config["dir"]["vendor"].'/';
        file_put_contents($vendor_dir."auto_require.json", $newArr);
        return $return;
    }
 
    public function del($name = null)
    {
        if (isset($name)) {
            $getArr = $this->get();
            foreach($getArr['require'] as $get_key => $get_val)
            {
                if (strtolower($name) == strtolower($get_val['name'])) {
                    $directory = $this->config["dir"]["vendor"].''.$dir;
                    // Подключаем класс
                    $admin = new \ApiShop\Admin\Control();
                    // Удаляем директорию
                    $admin->delete($directory);
                    // Удалить переменую в массиве
                    unset($getArr['require'][$get_key]);
                }
            }
 
            $newArr = json_encode($getArr);
            $vendor_dir = $this->config["dir"]["vendor"].'/';
            file_put_contents($vendor_dir."auto_require.json", $newArr);
            return true;
        } else {
            return false;
        }
    }
 
    public function delete(array $param = array())
    {
        $getArr = $this->get();
        if (count($param) >= 1) {
            foreach($param as $param_key => $param_val)
            {
                foreach($getArr['require'] as $get_key => $get_val)
                {
                    if (strtolower($param_val['name']) == strtolower($get_val['name'])) {
                        unset($getArr['require'][$get_key]);
                    }
                }
            }
        }
 
        $newArr = json_encode($getArr);
        $vendor_dir = $this->config["dir"]["vendor"].'/';
        file_put_contents($vendor_dir."auto_require.json", $newArr);
        return true;
    }
 
    public function state($name = null, $state = null)
    {
        $return = false;
 
        if (isset($name) && isset($state)) {
            $getArr = $this->get();
            $newParam['require'] = array();
            foreach($getArr['require'] as $get_key => $get_val)
            {
                if (strtolower($name) == strtolower($get_val['name'])) {
                    $get_val['state'] = $state;
                    $newParam['require'][$get_key] = $get_val;
                    $return = true;
                }
            }
 
            $arr = array_replace_recursive($getArr, $newParam);
            $newArr = json_encode($arr);
            $vendor_dir = $this->config["dir"]["vendor"].'/';
            file_put_contents($vendor_dir."auto_require.json", $newArr);
        }
 
        return $return;
 
    }
 
}
 