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
 
use Slim\Http\Request;
use Slim\Http\Response;
use Pllano\RouterDb\Db;
use Pllano\RouterDb\Router;
use ApiShop\Config\Settings;
use ApiShop\Resources\Language;
 
$config = (new Settings())->get();
$cart = $config['routers']['cart'];
// Подключаем сессию, берет название класса из конфигурации
$session = new $config['vendor']['session']($config['settings']['session']['name']);
if(isset($session->post_id)) {
    $post_id = '/'.$session->post_id;
} else {
    $post_id = '/0';
}
 
$app->post($post_id.$cart.'new-order', function (Request $request, Response $response, array $args) {
 
    // Получаем конфигурацию \ApiShop\Config\Settings
    $config = (new Settings())->get();
    // Подключаем сессию, берет название класса из конфигурации
    $session = new $config['vendor']['session']($config['settings']['session']['name']);
    // Читаем ключи
    $session_key = $config['key']['session'];
    $cookie_key = $config['key']['cookie'];
    // Подключаем мультиязычность
    $languages = new Language($request, $config);
    $language = $languages->get();
    // Разбираем post
    $post = $request->getParsedBody();
    // Подключаем систему безопасности
    $security = new Security();
 
    try {
        // Получаем токен из сессии
        $token = $config['vendor']['crypto']::decrypt($session->token, $token_key);
    } catch (\Exception $ex) {
        $token = 0;
        // Сообщение об Атаке или подборе токена
        $security->token($request, $response);
    }
    try {
        // Получаем токен из POST
        $post_csrf = $config['vendor']['crypto']::decrypt(filter_var($post['csrf'], FILTER_SANITIZE_STRING), $token_key);
        // Чистим данные на всякий случай пришедшие через POST
        $csrf = $utility->clean($post_csrf);
    } catch (\Exception $ex) {
        $csrf = 1;
        // Сообщение об Атаке или подборе csrf
        $security->csrf($request, $response);
    }
 
    $id = filter_var($post['id'], FILTER_SANITIZE_STRING);
    $iname = filter_var($post['iname'], FILTER_SANITIZE_STRING);
    $fname = filter_var($post['fname'], FILTER_SANITIZE_STRING);
    $phone = filter_var($post['phone'], FILTER_SANITIZE_STRING);
    $email = filter_var($post['email'], FILTER_SANITIZE_STRING);
    $city_name = filter_var($post['city_name'], FILTER_SANITIZE_STRING);
    $street = filter_var($post['street'], FILTER_SANITIZE_STRING);
    $build = filter_var($post['build'], FILTER_SANITIZE_STRING);
    $apart = filter_var($post['apart'], FILTER_SANITIZE_STRING);
    $product_id = filter_var($post['product_id'], FILTER_SANITIZE_STRING);
    $price = filter_var($post['price'], FILTER_SANITIZE_STRING);
    $num = filter_var($post['num'], FILTER_SANITIZE_STRING);
    $description = filter_var($post['description'], FILTER_SANITIZE_STRING);
    $cookie = $config['vendor']['crypto']::decrypt($_COOKIE[$config['settings']['session']['name']], $cookie_key);
 
    if ($session->authorize == 1) {
        $user_id = $config['vendor']['crypto']::decrypt($session->user_id, $session_key);
    } else {
 
        $userArr = [
            "site_id" => 1,
            "cookie" => $cookie,
            "iname" => $iname,
            "fname" => $fname,
            "phone" => $phone,
            "email" => $email,
            "password" => ""
        ];
 
        // Ресурс (таблица) к которому обращаемся
        $resource = "user";
        // Отдаем роутеру RouterDb конфигурацию.
        $router = new Router($config);
        // Получаем название базы для указанного ресурса
        $name_db = $router->ping($resource);
        // Подключаемся к базе
        $db = new Db($name_db, $config);
        // Отправляем запрос и получаем данные
        $user = $db->post($resource, $userArr);
 
        if (isset($user['response']['id'])) {
            $session->user_id = $config['vendor']['crypto']::encrypt($user['response']['id'], $session_key);
            $user_id = $user['response']['id'];
        }
    }
 
    $callbackStatus = 400;
    $callbackTitle = 'Соообщение системы';
    $callbackText = '';
 
    if ($user_id >= 1) {
 
        $addressArr = [
            "table_name" => "user",
            "user_id" => $user_id,
            "city_id" => 1,
            "street_id" => 2,
            "number" => $build,
            "apartment" => $apart
        ];
 
        // Ресурс (таблица) к которому обращаемся
        $resource = "address";
        // Отдаем роутеру RouterDb конфигурацию.
        $router = new Router($config);
        // Получаем название базы для указанного ресурса
        $name_db = $router->ping($resource);
        // Подключаемся к базе
        $db = new Db($name_db, $config);
        // Отправляем запрос и получаем данные
        $address = $db->post($resource, $addressArr);
 
        if ($address >= 1) {
 
            $orderArr = [
                "site_id" => 1,
                "order_type" => 1,
                "user_id" => $user_id,
                "status_id" => 1,
                "delivery_id" => 1,
                "address_id" => $address,
                "note" => $description
            ];
            
            // Ресурс (таблица) к которому обращаемся
            $resource = "order";
            // Отдаем роутеру RouterDb конфигурацию.
            $router = new Router($config);
            // Получаем название базы для указанного ресурса
            $name_db = $router->ping($resource);
            // Подключаемся к базе
            $db = new Db($name_db, $config);
            // Отправляем запрос и получаем данные
            $order = $db->post($resource, $orderArr);
 
            if ($order >= 1) {
 
                $cartArr = [
                    'user_id' => $user_id,
                    'cookie' => $cookie,
                    'product_id' => $product_id,
                    'order_id' => $order,
                    'num' => $num,
                    'price' => $price,
                    'currency_id' => $config['settings']['site']['currency_id'],
                    'status_id' => 1,
                    'state' => 1
                ];
 
                // Ресурс (таблица) к которому обращаемся
                $resource = "cart";
                // Отдаем роутеру RouterDb конфигурацию.
                $router = new Router($config);
                // Получаем название базы для указанного ресурса
                $name_db = $router->ping($resource);
                // Подключаемся к базе
                $db = new Db($name_db, $config);
                // Отправляем запрос и получаем данные
                $cart = $db->post($resource, $cartArr);
 
                if ($cart >= 1) {
                    $callbackStatus = 200;
                    $callbackTitle = 'Спасибо за заказ';
                    $callbackText = '<div class="text-center">Копию заказа мы отправили вам на почту.</div>';
                }
            } else {
                $callbackText = 'Ошибка !';
            }
        } else {
            $callbackText = 'Ошибка !';
        }
    } else {
        $callbackText = 'Ошибка !';
    }
 
    $callback = ['status' => $callbackStatus, 'title' => $callbackTitle, 'text' => $callbackText];
    // Выводим заголовки
    $response->withStatus(200);
    $response->withHeader('Content-type', 'application/json');
    // Выводим json
    echo json_encode($callback);
 
});
 
$app->post($post_id.$cart.'add-to-cart', function (Request $request, Response $response, array $args) {
 
    // Получаем конфигурацию \ApiShop\Config\Settings
    $config = (new Settings())->get();
    // Подключаем сессию, берет название класса из конфигурации
    $session = new $config['vendor']['session']($config['settings']['session']['name']);
    // Читаем ключи
    $session_key = $config['key']['session'];
    $cookie_key = $config['key']['cookie'];
    // Подключаем мультиязычность
    $languages = new Language($request, $config);
    $language = $languages->get();
    // Разбираем post
    $post = $request->getParsedBody();
    $id = filter_var($post['id'], FILTER_SANITIZE_STRING);
    $product_id = filter_var($post['product_id'], FILTER_SANITIZE_STRING);
    $price = filter_var($post['price'], FILTER_SANITIZE_STRING);
    $num = filter_var($post['num'], FILTER_SANITIZE_STRING);
    $cookie = $config['vendor']['crypto']::decrypt($_COOKIE[$config['settings']['session']['name']], $cookie_key);
 
    $callbackStatus = 400;
    $callbackTitle = 'Соообщение системы';
    $callbackText = '';
 
    if ($session->authorize == 1) {
        $user_id = $session->user_id;
    } else {
        $user_id = 0;
    }
 
    $cartArr = [
        'user_id' => $user_id,
        'cookie' => $cookie,
        'product_id' => $product_id,
        'num' => $num,
        'price' => $price,
        'currency_id' => $config['seller']['currency_id'],
        'order_id' => null,
        'status_id' => 1,
        'state' => 1
    ];
 
    // Ресурс (таблица) к которому обращаемся
    $resource = "cart";
    // Отдаем роутеру RouterDb конфигурацию.
    $router = new Router($config);
    // Получаем название базы для указанного ресурса
    $name_db = $router->ping($resource);
    // Подключаемся к базе
    $db = new Db($name_db, $config);
    // Отправляем запрос в базу
    $dbState = $db->post($resource, $cartArr);
    if ($dbState >= 1) {
        $callbackStatus = 200;
        $callbackTitle = $language["23"];
        $callbackText = $language["126"]." ".$language["124"]."<br>".$language["194"]." ".$price;
    } else {
            $callbackText = 'Действие заблокировано';
    }
 
    $callback = ['status' => $callbackStatus, 'title' => $callbackTitle, 'text' => $callbackText];
    // Выводим заголовки
    $response->withStatus(200);
    $response->withHeader('Content-type', 'application/json');
    // Выводим json
    echo json_encode($callback);
 
});