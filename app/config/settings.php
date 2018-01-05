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
 
    $config['settings']['site']['title'] = "API Shop";
    $config['settings']['site']['description'] = "Работает через RESTful API";
    $copyYear = 2016; // Set your website start date
    $curYear = date('Y'); // Keeps the second year updated
    $config['settings']['site']['copyright']['date'] = $copyYear . (($copyYear != $curYear) ? '-' . $curYear : '');
    $config['settings']['site']['copyright']['text'] = "API Shop";
 
    // Путь к папке шаблонов
    $config["settings"]["themes"]["dir"] = __DIR__ . "/../../themes";
    // Название папки с шаблонами
    $config["settings"]["themes"]["templates"] = "templates";
    // Название шаблона. По умолчанию mini-mo
    // Если работает через api будет брать название шаблона с конфигурации api
    $config["settings"]["themes"]["template"] = "mini-mo";
 
    // Папка куда будет кешироваться Slim\Views\Twig
    $config["settings"]["cache"] =  __DIR__ . "/../_cache/";

    $config["settings"]['http-codes'] = "https://github.com/pllano/APIS-2018/tree/master/http-codes/";
 
    // Конфигурация Slim
    $config["settings"]["dir"] = "config";
    $config["settings"]["displayErrorDetails"] = true;
    $config["settings"]["addContentLengthHeader"] = false;
    $config["settings"]["determineRouteBeforeAppMiddleware"] = true;
    $config["settings"]["cookies.httponly"] = true;
    $config["settings"]["phpSettings.session.cookie_httponly"] = true;
    $config["settings"]["rebodys.session.cookie_httponly"] = true;
    $config["settings"]["debug"] = true;
 
    // Конфигурация session
    $config["settings"]["session"]["name"] = "_session";
    $config["settings"]["session"]["lifetime"] = 48;
    $config["settings"]["session"]["path"] = "/";
    $config["settings"]["session"]["domain"] = "";
    $config["settings"]["session"]["secure"] = false;
    $config["settings"]["session"]["httponly"] = true;
 
    // Set session cookie path, domain and secure automatically
    $config["settings"]["session"]["cookie_autoset"] = true;
    // Path where session files are stored, PHP"s default path will be used if set null
    $config["settings"]["session"]["save_path"] = null;
    // Session cache limiter
    $config["settings"]["session"]["cache_limiter"] = "nocache";
    // Extend session lifetime after each user activity
    $config["settings"]["session"]["autorefresh"] = false;
    // Encrypt session data if string is set
    $config["settings"]["session"]["encryption_key"] = null;
    // Session namespace
    $config["settings"]["session"]["namespace"] = "_user";
 
    // Папка куда будут писатся логи Monolog
    $config["settings"]["logger"]["path"]   = isset($_ENV["docker"]) ? "php://stdout" : __DIR__ . "/../_logs/app.log";
    $config["settings"]["logger"]["name"]   = "slim-app";
    $config["settings"]["logger"]["level"] = \Monolog\Logger::DEBUG;
 
    // Путь к ключам шифрования
    $key_session = __DIR__ . "/key/session.txt";
    $key_cookie = __DIR__ . "/key/cookie.txt";
    $key_token = __DIR__ . "/key/token.txt";
    $key_password = __DIR__ . "/key/password.txt";
    $key_user = __DIR__ . "/key/user.txt";
    $key_card = __DIR__ . "/key/card.txt";
    $key_db = __DIR__ . "/key/db.txt";
 
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
 
    $config["key"]["session"] = Key::loadFromAsciiSafeString(file_get_contents($key_session, true));
    $config["key"]["token"] = Key::loadFromAsciiSafeString(file_get_contents($key_token, true));
    $config["key"]["cookie"] = Key::loadFromAsciiSafeString(file_get_contents($key_cookie, true));
    $config["key"]["password"] = Key::loadFromAsciiSafeString(file_get_contents($key_password, true));
    $config["key"]["user"] = Key::loadFromAsciiSafeString(file_get_contents($key_user, true));
    $config["key"]["card"] = Key::loadFromAsciiSafeString(file_get_contents($key_card, true));
    // Динамический ключ шифрования для ajax
    $config["key"]["ajax"] = (Key::createNewRandomKey())->saveToAsciiSafeString();

    // Глобальные установки баз данных
    // Доступные значения: api, json, jsonapi, mysql, elasticsearch
    // Название основной базы данных. По умолчанию api
    $config["db"]["master"] = "api";
    // Название резервной базы данных. По умолчанию json
    $config["db"]["slave"] = "json";
 	// Лимит выполнения запросов из очереди queue за один раз. По умолчанию 5
	$config["db"]["queue"]["limit"] = 5;
 
    // Ключ шифрования в базах данных. Отдаем в чистом виде.
    $config["db"]["key"] = file_get_contents($key_db, true);

    // Настройки подключения к jsonDb напрямую
    // Директория для хранения файлов json базы данных.
    $config["db"]["json"]["dir"] = __DIR__ . "/../../json-db/db/";
    // Кеширование запросов
    $config["db"]["json"]["cached"] = false; // true|false
    // Время жизни кеша
    $config["db"]["json"]["cache_lifetime"] = 60;
    // Очередь на запись
    $config["db"]["json"]["temp"] = false;
    // Работает через API
    $config["db"]["json"]["api"] = false;
    // Шифруем базу
    $config["db"]["json"]["crypt"] = false;

    // Настройки подключения к jsonDb через API
    // URL API jsonDb
    $config["db"]["jsonapi"]["url"] = "https://pllano.eu/json-db/";
    // Доступные методы аутентификации: null, CryptoAuth, QueryKeyAuth, HttpTokenAuth, LoginPasswordAuth
    $config["db"]["jsonapi"]["auth"] = null;
    // Публичный ключ аутентификации
    $config["db"]["jsonapi"]["public_key"] = "";
    // Приватный ключ шифрования
    $config["db"]["jsonapi"]["private_key"] = "";
 
    // Если работает через API будет брать часть конфигурации из api
    $config["db"]["api"]["config"] = true; // true|false
    // URL API
    $config["db"]["api"]["url"] = "https://ua.pllano.com/api/v1/json/";
    // Доступные методы аутентификации: CryptoAuth, QueryKeyAuth, HttpTokenAuth, LoginPasswordAuth
    $config["db"]["api"]["auth"] = "QueryKeyAuth";
    // Публичный ключ аутентификации
    $config["db"]["api"]["public_key"] = "3903f7b3fb82c2e609b3f07ccfa119352f1d26c55723c3f7f8fb36a0d0e31dae";
    // Приватный ключ шифрования
    $config["db"]["api"]["private_key"] = "";
 
    // Настройки подключения к базе MySQL
    $config["db"]["mysql"]["host"] = "";
    $config["db"]["mysql"]["basename"] = "";
    $config["db"]["mysql"]["port"] = "";
    $config["db"]["mysql"]["charset"] = "utf8";
    $config["db"]["mysql"]["connect_timeout"] = 15;
    $config["db"]["mysql"]["user"] = "";
    $config["db"]["mysql"]["password"] = "";
 
    // Настройки подключения к Elasticsearch
    // По умолчанию http://localhost:9200/
    $config["db"]["elasticsearch"]["host"] = "localhost";
    $config["db"]["elasticsearch"]["port"] = 9200;
    // Учитывая то что в следующих версиях Elasticsearch не будет type
    // вы можете отключить type поставив false
    // в этом случае index будет формироватся так index_type
    $config["db"]["elasticsearch"]["type"] = true; // true|false
    $config["db"]["elasticsearch"]["index"] = "apishop";
    // Если подключение к elasticsearch требует логин и пароль установите auth=true
    $config["db"]["elasticsearch"]["auth"] = false; // true|false
    $config["db"]["elasticsearch"]["user"] = "elastic";
    $config["db"]["elasticsearch"]["password"] = "elastic_password";
 
    // API Shop позволяет одновременно работать с любым количеством баз данных
    // Название базы данных для каждого ресурса. По умолчанию api
    $config["resource"]["site"]["db"] = "api";
    $config["resource"]["language"]["db"] = "jsonapi";
    $config["resource"]["user"]["db"] = "json";
    
    $config["resource"]["price"]["db"] = "api";
    $config["resource"]["category"]["db"] = "api";
    $config["resource"]["product"]["db"] = "api";
    $config["resource"]["type"]["db"] = "api";
    $config["resource"]["brand"]["db"] = "api";
    $config["resource"]["serie"]["db"] = "api";
    $config["resource"]["images"]["db"] = "api";
    $config["resource"]["seo"]["db"] = "api";
    $config["resource"]["description"]["db"] = "api";
    $config["resource"]["params"]["db"] = "api";
    $config["resource"]["role"]["db"] = "api";
    $config["resource"]["contact"]["db"] = "api";
    $config["resource"]["address"]["db"] = "api";
    $config["resource"]["currency"]["db"] = "api";
    $config["resource"]["cart"]["db"] = "api";
    $config["resource"]["order"]["db"] = "api";
    $config["resource"]["pay"]["db"] = "api";
    $config["resource"]["article"]["db"] = "api";
    $config["resource"]["article_category"]["db"] = "api";
 
    return $config;
 
    }
 
}
 
