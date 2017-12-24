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

namespace ApiShop\Resources;

use Adbar\Session;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException as CryptoException;

use ApiShop\Config\Settings;
use ApiShop\Utilities\Utility;
use ApiShop\Database\Router;
use ApiShop\Database\Ping;

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
        $config = (new Settings())->get();
 
        // Подключаем сессию \Adbar\Session
        $session = new Session($config['settings']['session']['name']);
 
        // Читаем ключи
        $session_key = $config['key']['session'];
        $cookie_key = $config['key']['cookie'];
 
        // Читаем печеньку у юзера в браузере
        $read_code = isset($_COOKIE[$config['settings']['session']['name']]) ? $_COOKIE[$config['settings']['session']['name']] : null;
 
        // Если печеньки нет создаем новую
        if ($read_code) {
            try {
                $code = Crypto::decrypt($_COOKIE[$config['settings']['session']['name']], $cookie_key);
                $identificator = $_COOKIE[$config['settings']['session']['name']];
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
            setcookie($config['settings']['session']['name'], $identificator, time() + 3600 * 24 * 31, '/', '.'.$this->domain, 1, true);

        }
        
        // Если cookie есть и дешифровка не дала ошибки
        if ($identificator != null) {

            // Пишем в сессию identificator cookie
            $session->code = $identificator;
            // cookie в расшифрованном виде для записи в базу
            $code = Crypto::decrypt($identificator, $cookie_key);
            // Подключаем ApiShop\Database\Router - Универсальный класс работы с базами данных
            $database = new Router((new Ping("user"))->get());
            // Запрашиваем в базе данные пользователя по cookie
            $response = $database->get("user", ["code" => $code]);
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
 