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

namespace ApiShop\Model;

use Adbar\Session;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException as CryptoException;

use ApiShop\Config\Settings;
use ApiShop\Utilities\Utility;
use ApiShop\Database\Router;

class User {

    protected $db;

    function __construct()
    {

        list($x1,$x2) = explode('.',strrev($_SERVER['HTTP_HOST']));
        $xdomain = $x1.'.'.$x2;
        $this->domain =  strrev($xdomain);

    }
 
    // Запускаем сессию пользоваетеля
    public function run()
    {
        // Подключаем конфиг \ApiShop\Config\Settings
        $configs = new Settings();
        $conf = $configs->get();
 
        // Подключаем сессию \Adbar\Session
        $session = new Session($conf['settings']['session']['name']);
 
        // Читаем ключи
        $session_key = $conf['key']['session'];
        $cookie_key = $conf['key']['cookie'];
 
        // Читаем печеньку у юзера в браузере
        $read_code = isset($_COOKIE[$conf['settings']['session']['name']]) ? $_COOKIE[$conf['settings']['session']['name']] : null;
 
        // Если печеньки нет создаем новую
        if ($read_code) {
            try {
                $code = Crypto::decrypt($_COOKIE[$conf['settings']['session']['name']], $cookie_key);
                $identificator = $_COOKIE[$conf['settings']['session']['name']];
            } catch (CryptoException $ex) {
                $identificator = null;
            }
        } else {
            $identificator = null;
        }
 
        // Если cookie нет создаем новую
        if ($identificator === null) {
 
            // Чистим сессию на всякий случай
            $session->clear();
 
            // Подключаем утилиту
            $utility = new Utility();
            // Генерируем identificator
            $identificator = Crypto::encrypt($utility->random_token(), $cookie_key);
 
            // Записываем пользователю новый cookie
            setcookie($conf['settings']['session']['name'], $identificator, time() + 3600 * 24 * 31, '/', '.'.$this->domain, 1, true);

        }
        
        // Если cookie есть и дешифровка не дала ошибки
        if ($identificator != null) {

            // Пишем в сессию cookie
            $session->code = $identificator;
            // cookie в расшифрованном виде для записи в базу
            $code = Crypto::decrypt($identificator, $cookie_key);

            //$db = $conf['db']['name'];
            $db = $conf["resource"]["user"]["db"];
            // Подключаем ApiShop\Database\Router - Универсальный класс работы с базами данных
            $database = new Router($db);
            // Запрашиваем в базе данные пользователя по cookie
            $response = $database->getOne("user", "code", $code);

            // Если ничего не нашли вернет 0
            // Если мы ничего не нашли а у юзера cookie есть скорее всего это бот или идет потбор cookie
            // Здесь нужно запустить анализатор который должен принять меры
            // По сути у нас нет защиты от такого
            // Как вариат писать в сессию пользователя при регистрации или успешной авторизации еще одну cookie и проверять наличие не одной а двух
            // Подобрать две cookie будет намного сложнее

            // Если нашли пользователя в базе и получили его id
            if ($response != 0 && isset($response['0']['id'])) {
 
                // Пишем данные из базы в сессию
                $session->id = Crypto::encrypt($response['0']['id'], $session_key);
                $session->cart_id = Crypto::encrypt($response['0']['cart_id'], $session_key);
                $authorize = ($response['0']['authorized'] == '0000-00-00 00:00:00') ? 0 : 1;
                // Записываем authorize в сессию
                $session->authorize = Crypto::encrypt($authorize, $session_key);
 
                // Если пользователь есть мы на всякий случай обновим данные в его сессии
                $sessionUser = new SessionUser();
                $sessionUser->checking();
 
            } else {
 
                // Если нет то обнуляем данные в сессии на всякий случай
                $session->id = null;
                $session->cart_id = null;
                $session->authorize = null;
 
            }
 
        }
 
    }
 
}
 