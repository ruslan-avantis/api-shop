<?php
/**
 * This file is part of the {API}$hop
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/pllano/api-shop
 * @version 1.1.1
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
 
    function __construct($config, $template = null)
    {
        $this->config = $config;
		// Устанавливаем название шаблона
        if(isset($template)) {
            $this->template = $template;
        }
    }
 
    public function get()
    {
        if(isset($this->template)) {
            $json_dir = $this->config['template']['front_end']['themes']['dir'].'/'.$this->config['template']['front_end']['themes']['templates'].'/'.$this->template.'/config/';
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
 