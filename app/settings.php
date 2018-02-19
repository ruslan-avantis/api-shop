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
 
namespace ApiShop\Config;
 
class Settings {
 
    public static function get() {
 
        $config = [];
        // Папка файла конфигурации settings.json
        $settings =  __DIR__ .'/settings.json';
        $json = '';
 
        if (file_exists($settings)) {
            $json = json_decode(file_get_contents($settings), true);
            if (isset($json["update"])) {
                $config = $config + $json;
            }
        }
 
        $config["dir"]["config"] = __DIR__ .'/..'.$json["dir"]["config_dir"];
        $config["dir"]["routers"] = __DIR__ .'/..'.$json["dir"]["routers_dir"];
        $config["dir"]["vendor"] = __DIR__ .'/..'.$json["dir"]["vendor_dir"];
        $config["dir"]["plugins"] = __DIR__ .'/..'.$json["dir"]["plugins_dir"];
        $config["dir"]["images"] = __DIR__ .'/..'.$json["dir"]["images_dir"];
        $config["dir"]["www"] = __DIR__ .'/..';
        
        $config["settings"]["json"] = $settings;
 
        $config["settings"]["keys"] = 'null';
 
        // Папка куда будут писатся логи Monolog
        $config["settings"]["logger"]["path"] = isset($_ENV["docker"]) ? "php://stdout" : __DIR__ . "/_logs/app.log";
        $config["settings"]["logger"]["name"] = "slim-app";
        $config["settings"]["logger"]["level"] = \Monolog\Logger::DEBUG;
 
        $copyYear = 2017; // Set your website start date
        $curYear = date('Y'); // Keeps the second year updated
        $config['settings']['site']['copyright']['date'] = $copyYear . (($copyYear != $curYear) ? '-' . $curYear : '');
        
        // Папка куда будет кешироваться Slim\Views\Twig
        $config["settings"]["cache"] =  __DIR__ . "/_cache/";
        
        // Папка с шаблонами
        $config["settings"]["themes"]["dir"] = __DIR__ .''.$json['template']['front_end']['themes']["dir_name"];
        $config["settings"]["themes"]["back_end_dir"] = __DIR__ .''.$json['template']['back_end']['themes']["dir_name"];
        $config["settings"]["themes"]["front_end_dir"] = __DIR__ .''.$json['template']['front_end']['themes']["dir_name"];
        // Директория хранения файлов базы данных json
        $config["db"]["json"]["dir"] = __DIR__ .''.$json["db"]["json"]["dir_name"];
        // Если директории нет создать
        if (!file_exists($config["db"]["json"]["dir"])) {
            mkdir($config["db"]["json"]["dir"], 0777, true);
        }
 
        // Путь к ключам шифрования
        $key = __DIR__ . "/key";
        // Создаем директорию если ее еще нет.
        if (!file_exists($key)) {
            mkdir($key, 0777, true);
        }
 
        // Директория где хранятся ключи шифрования
        $key_session = $key."/session.txt";
        $key_cookie = $key."/cookie.txt";
        $key_token = $key."/token.txt";
        $key_password = $key."/password.txt";
        $key_user = $key."/user.txt";
        $key_card = $key."/card.txt";
 
        // Генерируем ключи шифрования, если их нет
        if (!file_exists($key_session)) {
            file_put_contents($key_session, ($json['vendor']['crypto']['random_key']())->saveToAsciiSafeString());
        }
        if (!file_exists($key_cookie)) {
            file_put_contents($key_cookie, ($json['vendor']['crypto']['random_key']())->saveToAsciiSafeString());
        }
        if (!file_exists($key_token)) {
            file_put_contents($key_token, ($json['vendor']['crypto']['random_key']())->saveToAsciiSafeString());
        }
        if (!file_exists($key_password)) {
            file_put_contents($key_password, ($json['vendor']['crypto']['random_key']())->saveToAsciiSafeString());
        }
        if (!file_exists($key_user)) {
            file_put_contents($key_user, ($json['vendor']['crypto']['random_key']())->saveToAsciiSafeString());
        }
        if (!file_exists($key_card)) {
            file_put_contents($key_card, ($json['vendor']['crypto']['random_key']())->saveToAsciiSafeString());
        }
 
        $config["key"]["session"] = $json['vendor']['crypto']['load_key'](file_get_contents($key_session, true));
        $config["key"]["token"] = $json['vendor']['crypto']['load_key'](file_get_contents($key_token, true));
        $config["key"]["cookie"] = $json['vendor']['crypto']['load_key'](file_get_contents($key_cookie, true));
        $config["key"]["password"] = $json['vendor']['crypto']['load_key'](file_get_contents($key_password, true));
        $config["key"]["user"] = $json['vendor']['crypto']['load_key'](file_get_contents($key_user, true));
        $config["key"]["card"] = $json['vendor']['crypto']['load_key'](file_get_contents($key_card, true));
        // Динамический ключ шифрования для ajax
        $config["key"]["ajax"] = ($json['vendor']['crypto']['random_key']())->saveToAsciiSafeString();
 
        $key_db = $key."/db.txt";
        if (!file_exists($key_db)) {
            file_put_contents($key_db, ($json['vendor']['crypto']['random_key']())->saveToAsciiSafeString());
        }
        // Ключ шифрования в базах данных. Отдаем в чистом виде.
        $config["db"]["key"] = file_get_contents($key_db, true);
 
        // Длина ключа public_key - колличество символов
        $config["settings"]["install"]["strlen"] = 64;
 
        if(isset($json["seller"]["public_key"])) {
            if($json["seller"]["public_key"] != '' && $json["seller"]["public_key"] != 'null' && $json["seller"]["public_key"] != null) {
                $public_key = $json["seller"]["public_key"];
            } else {
                $public_key = null;
            }
        } else {
            $public_key = null;
        }
 
        // Определяем протокол
        $config["server"]["scheme"] = 'http';
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $config["server"]["scheme"] = 'https';
        }
 
        // Статус активации сайта null или public_key
        $config["settings"]["install"]["status"] = $public_key;
 
        return $config;
 
    }
 
}
 