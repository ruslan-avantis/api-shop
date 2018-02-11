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
 
class Template {
 
    private $template = null;
    private $config;
 
    function __construct($template = null)
    {
        // Устанавливаем название шаблона
        if ($template != null) {
            $this->template = $template;
        }
        // Подключаем конфиг Settings\Config
        $config = (new Settings())->get();
        $this->config = $config;
    }
 
    public function get()
    {
        $resp["templates"] = array();
        $templates = array();
        $directory = $this->config["settings"]["themes"]["dir"]."/".$this->config["settings"]["themes"]["templates"];
        $scanned = array_diff(scandir($directory), array('..', '.'));
        if (count($scanned) >= 1) {
            foreach($scanned as $dir)
            {
                if (is_dir($directory.'/'.$dir)) {
                    $json_dir = $this->config["settings"]["themes"]["dir"].'/'.$this->config["settings"]["themes"]["templates"].'/'.$dir.'/config/';
                    if (file_exists($json_dir."config.json")) {
                        $json = json_decode(file_get_contents($json_dir."config.json"), true);
                         $template = $json;
                         $templates["alias"] = $template["alias"];
                         $templates["name"] = $template["name"];
                         $templates["dir"] = $dir;
                         $templates["version"] = $template["version"];
                         $templates["url"] = $template["url"];
                         if(isset($template["demo"])){
                             $templates["demo"] = $template["demo"];
                         } else {
                             $templates["demo"] = "https://".$dir.".pllano.com/";
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
        if ($this->template != null) {
            $json_dir = $this->config["settings"]["themes"]["dir"].'/'.$this->config["settings"]["themes"]["templates"].'/'.$this->template.'/config/';
            if (file_exists($json_dir."config.json")) {
                return json_decode(file_get_contents($json_dir."config.json"), true);
            } else {
                return null;
            }
        } else {
            return null;
        }
    }
 
    public function put($param)
    {
        $arr = array_replace_recursive($this->get(), $param);
        $newArr = json_encode($arr);
        $json_dir = $this->config["settings"]["themes"]["dir"].'/'.$this->config["settings"]["themes"]["templates"].'/'.$this->template.'/config/';
        file_put_contents($json_dir."config.json", $newArr);
        return true;
    }
 
}
 