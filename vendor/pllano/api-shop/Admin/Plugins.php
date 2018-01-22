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
 
namespace ApiShop\Admin;
 
use ApiShop\Config\Settings;
 
class Plugins {
 
    private $plugin = null;
    private $config;
 
    function __construct($plugin = null)
    {
        // Устанавливаем название шаблона
		if ($plugin != null) {
			$this->plugin = $plugin;
		}
		// Подключаем конфиг Settings\Config
        $config = (new Settings())->get();
        $this->config = $config;
    }
 
    public function get()
    {
		$resp["templates"] = array();
		$templates = array();
		$plugins_dir = $this->config["settings"]["plugins"]["dir"].'/';
		$scanned = array_diff(scandir($plugins_dir), array('..', '.'));
		if (count($scanned) >= 1) {
            foreach($scanned as $dir)
            {
				if (is_dir($directory.'/'.$dir)) {
					$json_dir = $this->config["settings"]["themes"]["dir"].'/'.$this->config["settings"]["themes"]["templates"].'/'.$dir.'/config/';
					if (file_exists($json_dir."config.json")) {
					    $json = json_decode(file_get_contents($json_dir."config.json"), true);
					     $plugin = $json;
						 $templates["alias"] = $plugin["alias"];
						 $templates["name"] = $plugin["name"];
						 $templates["dir"] = $dir;
						 $templates["version"] = $plugin["version"];
						 $templates["url"] = $plugin["url"];
						 if(isset($plugin["demo"])){
							 $templates["demo"] = $plugin["demo"];
					     } else {
						     $templates["demo"] = "https://plugin.pllano.com/";
						 }
 
						 $resp["templates"][] = $templates;
					}
				}
			}
		}
		return $resp;

    }
	
    public function getOne()
    {
        if ($this->plugin != null) {
            $plugins_dir = $this->config["settings"]["plugins"]["dir"].'/';
            if (file_exists($plugins_dir.''.$this->plugin.'.json')) {
                return json_decode(file_get_contents($plugins_dir.''.$this->plugin.'.json'), true);
            } else {
                return null;
            }
        } else {
            return null;
        }
    }
	
    public function put($arr)
    {
		$newArr = json_encode($arr);
		$plugins_dir = $this->config["settings"]["plugins"]["dir"].'/';
		file_put_contents($plugins_dir.''.$this->plugin.'.json', $newArr);
        return true;
    }
 
}
 