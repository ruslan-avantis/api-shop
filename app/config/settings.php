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

namespace Pllano\ApiShop\Core;

use Defuse\Crypto\Key;

class Settings {
    
    public static function get() {
 
    $config = array();
 
    // Путь к папке шаблонов
    $config['settings']['themes']['dir'] = __DIR__ . '/../../themes';
    // Название папки с шаблонами
    $config['settings']['themes']['templates'] = 'templates';
    // Название шаблона
    $config['settings']['themes']['template'] = 'mini-mo';
 
    // Папка куда будет кешироваться Slim\Views\Twig
    $config['settings']['cache'] =  __DIR__ . '/../_cache/';
 
    // Конфигурация Slim
    $config['settings']['dir'] = "config";
    $config['settings']['displayErrorDetails'] = false;
    $config['settings']['addContentLengthHeader'] = false;
    $config['settings']['determineRouteBeforeAppMiddleware'] = true;
    $config['settings']['cookies.httponly'] = true;
    $config['settings']['phpSettings.session.cookie_httponly'] = true;
    $config['settings']['rebodys.session.cookie_httponly'] = true;
    $config['settings']['debug'] = true;
 
    // Конфигурация session
    $config['settings']['session']['name'] = "_session";
    $config['settings']['session']['lifetime'] = 48;
    $config['settings']['session']['path'] = "/";
    $config['settings']['session']['domain'] = '';
    $config['settings']['session']['secure'] = false;
    $config['settings']['session']['httponly'] = true;
 
    // Set session cookie path, domain and secure automatically
    $config['settings']['session']['cookie_autoset'] = true;
    // Path where session files are stored, PHP's default path will be used if set null
    $config['settings']['session']['save_path'] = null;
    // Session cache limiter
    $config['settings']['session']['cache_limiter'] = "nocache";
    // Extend session lifetime after each user activity
    $config['settings']['session']['autorefresh'] = false;
    // Encrypt session data if string is set
    $config['settings']['session']['encryption_key'] = null;
    // Session namespace
    $config['settings']['session']['namespace'] = "_user";
 
    // Папка куда будут писатся логи Monolog
    $config['settings']['logger']['path']   = isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../_logs/app.log';
    $config['settings']['logger']['name']   = "slim-app";
    $config['settings']['logger']['level'] = \Monolog\Logger::DEBUG;
 
    // Путь к ключам шифрования
    $key_session = __DIR__ . '/key/session.txt';
    $key_cookie = __DIR__ . '/key/cookie.txt';
    $key_token = __DIR__ . '/key/token.txt';
    $key_password = __DIR__ . '/key/password.txt';
    $key_user = __DIR__ . '/key/user.txt';
    $key_card = __DIR__ . '/key/card.txt';
	$key_db = __DIR__ . '/key/db.txt';
 
    // Устанавливаем ключи шифрования
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
    if (!file_exists($key_db)) {
        file_put_contents($key_db, (Key::createNewRandomKey())->saveToAsciiSafeString());
    }
 
    $config['key']['session'] = Key::loadFromAsciiSafeString(file_get_contents($key_session, true));
    $config['key']['token'] = Key::loadFromAsciiSafeString(file_get_contents($key_token, true));
    $config['key']['cookie'] = Key::loadFromAsciiSafeString(file_get_contents($key_cookie, true));
    $config['key']['password'] = Key::loadFromAsciiSafeString(file_get_contents($key_password, true));
    $config['key']['user'] = Key::loadFromAsciiSafeString(file_get_contents($key_user, true));
    $config['key']['card'] = Key::loadFromAsciiSafeString(file_get_contents($key_card, true));
    // Ключ шифрования базы данных. Отдаем в чистом виде.
    $config['key']['db'] = file_get_contents($key_db, true);
    // Динамический ключ шифрования для ajax
    $config['key']['ajax'] = (Key::createNewRandomKey())->saveToAsciiSafeString();
	
    return $config;
 
    }
 
}
 
