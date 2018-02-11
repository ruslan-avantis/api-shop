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
 
namespace ApiShop\Model;
 
use ApiShop\Config\Settings;
use ApiShop\Model\Site;
 
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
 
}
 