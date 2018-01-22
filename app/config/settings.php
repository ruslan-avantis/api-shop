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
 
namespace ApiShop\Config;
 
use Defuse\Crypto\Key;
 
class Settings {
 
    public static function get() {
 
        $config = array();
 
        // Папка файла конфигурации settings.json
        $settings =  __DIR__ .'/settings.json';
        $json = '';
 
        if (file_exists($settings)) {
            $json = json_decode(file_get_contents($settings), true);
            if (isset($json["update"])) {
                $config = $config + $json;
            }
        }
 
        $config["settings"]["json"] = $settings;
		
		// Папка куда будут писатся логи Monolog
        $config["settings"]["logger"]["path"]   = isset($_ENV["docker"]) ? "php://stdout" : __DIR__ . "/../_logs/app.log";
        $config["settings"]["logger"]["name"]   = "slim-app";
        $config["settings"]["logger"]["level"] = \Monolog\Logger::DEBUG;
 
        $copyYear = 2017; // Set your website start date
        $curYear = date('Y'); // Keeps the second year updated
        $config['settings']['site']['copyright']['date'] = $copyYear . (($copyYear != $curYear) ? '-' . $curYear : '');
 
        // Папка куда будет кешироваться Slim\Views\Twig
        $config["settings"]["cache"] =  __DIR__ . "/../_cache/";
 
        // Папка с шаблонами
	    $config["settings"]["themes"]["dir"] = __DIR__ .''.$json["settings"]["themes"]["dir_name"];
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
            file_put_contents($key_session, (Key::createNewRandomKey())->saveToAsciiSafeString());
        }
        if (!file_exists($key_cookie)) {
            file_put_contents($key_cookie, (Key::createNewRandomKey())->saveToAsciiSafeString());
        }
        if (!file_exists($key_token)) {
            file_put_contents($key_token, (Key::createNewRandomKey())->saveToAsciiSafeString());
        }
        if (!file_exists($key_password)) {
            file_put_contents($key_password, (Key::createNewRandomKey())->saveToAsciiSafeString());
        }
        if (!file_exists($key_user)) {
            file_put_contents($key_user, (Key::createNewRandomKey())->saveToAsciiSafeString());
        }
        if (!file_exists($key_card)) {
            file_put_contents($key_card, (Key::createNewRandomKey())->saveToAsciiSafeString());
        }
 
        $config["key"]["session"] = Key::loadFromAsciiSafeString(file_get_contents($key_session, true));
        $config["key"]["token"] = Key::loadFromAsciiSafeString(file_get_contents($key_token, true));
        $config["key"]["cookie"] = Key::loadFromAsciiSafeString(file_get_contents($key_cookie, true));
        $config["key"]["password"] = Key::loadFromAsciiSafeString(file_get_contents($key_password, true));
        $config["key"]["user"] = Key::loadFromAsciiSafeString(file_get_contents($key_user, true));
        $config["key"]["card"] = Key::loadFromAsciiSafeString(file_get_contents($key_card, true));
        // Динамический ключ шифрования для ajax
        $config["key"]["ajax"] = (Key::createNewRandomKey())->saveToAsciiSafeString();
	
        $key_db = $key."/db.txt";
        if (!file_exists($key_db)) {
            file_put_contents($key_db, (Key::createNewRandomKey())->saveToAsciiSafeString());
        }
        // Ключ шифрования в базах данных. Отдаем в чистом виде.
        $config["db"]["key"] = file_get_contents($key_db, true);
 
        // Длина ключа public_key - колличество символов
        $config["settings"]["install"]["strlen"] = 64;
 
        if(isset($json["seller"]["public_key"])) {
            $public_key = $json["seller"]["public_key"];
        } else {
		    $public_key = null;
		}
        // Статус активации сайта null или public_key
        $config["settings"]["install"]["status"] = $public_key;
 
        return $config;
 
    }
 
}
 