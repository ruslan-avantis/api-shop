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
use ApiShop\Admin\Packages;
 
class Config {
 
    private $config;
 
    function __construct()
    {
        // Подключаем конфиг Settings\Config
        $config = (new Settings())->get();
        $this->config = $config;
    }
 
    public function get()
    {
        if (isset($this->config["settings"]["json"])) {
            if (file_exists($this->config["settings"]["json"])) {
                return json_decode(file_get_contents($this->config["settings"]["json"]), true);
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
        $newArr = str_replace('"1"', 1, $newArr);
        $newArr = str_replace('"0"', 0, $newArr);
        file_put_contents($this->config["settings"]["json"], $newArr);
        return true;
    }
 
    public function template_activate($name)
    {
        $param['settings']['themes']['template'] = $name;
        $arr = array_replace_recursive($this->get(), $param);
        $newArr = json_encode($arr);
        $newArr = str_replace('"1"', 1, $newArr);
        $newArr = str_replace('"0"', 0, $newArr);
        file_put_contents($this->config["settings"]["json"], $newArr);
        return true;
    }
 
    public function template_install($name, $dir, $uri)
    {
        $template_dir = $this->config["settings"]["themes"]["dir"].'/'.$this->config["settings"]["themes"]["templates"].'/'.$dir;
 
        if (!file_exists($template_dir)) {
            mkdir($template_dir, 0777, true);
            file_put_contents($template_dir.'/template.zip', file_get_contents($uri));
 
            $zip = new \ZipArchive;
            if ($zip->open($template_dir.'/template.zip') === true) {
                $zip->extractTo($template_dir);
                $zip->close();
                                    
                if (file_exists($template_dir."/template.zip")) {
                    unlink($template_dir."/template.zip");
                }
                
                $config_install = json_decode(file_get_contents($template_dir."/config/config.json"), true);
                $install = $config_install['install'];
 
                // Записываем в файл конфигурации
                if (isset($install['config'])) {
                        $this->put($install['config']);
                }
 
                // Добавляем файлы роутеров
                if (isset($install['routers'])) {
                    foreach($install['routers'] as $install_routers)
                    {
                        $routers_dir = $this->config["dir"]["routers"];
                        $file_routers = $template_dir.'/install/routers/'.$install_routers['file'];
                        if (file_exists($file_routers)) {
                            // Копируем файл роутера в папку routers
                            file_put_contents($routers_dir.'/'.$install_routers['file'], file_get_contents($file_routers));
                            // Удаляем файлы роутеров в папке шаблона после копирования
                            unlink($file_routers);
                        }
                    }
                }
 
                // Добавляем пакеты для AutoRequire в файл auto_require.json
                if (isset($install['packages'])) {
                        $packages = new Packages();
                        $packages->put($install['packages']);
                }
 
                // Активируем шаблон
                $param['settings']['themes']['template'] = $name;
                $this->put($param);
 
                return true;
 
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
 
}
 