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
use Adbar\Session;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException as CryptoEx;
use Sinergi\BrowserDetector\Language as Langs;
use GuzzleHttp\Client as Guzzle;
use RouterDb\Db;
use RouterDb\Router;
use ApiShop\Config\Settings;
use ApiShop\Resources\Language;
 
$app->post('/cart/new-order', function (Request $request, Response $response, array $args) {
    // Получаем конфигурацию \ApiShop\Config\Settings
    $config = (new Settings())->get();
    // Подключаем сессию
    $session = new Session($config['settings']['session']['name']);
    // Читаем ключи
    $session_key = $config['key']['session'];
    $cookie_key = $config['key']['cookie'];
    // Разбираем post
    $post = $request->getParsedBody();
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
    $cookie = Crypto::decrypt($_COOKIE[$config['settings']['session']['name']], $cookie_key);
    
    // Получаем массив данных из таблицы language на языке из $session->language
    $langs = new Langs();
    // Получаем массив данных из таблицы language на языке из $session->language
    if (isset($session->language)) {
        $lang = $session->language;
    } elseif ($langs->getLanguage()) {
        $lang = $langs->getLanguage();
    } else {
        $lang = $site_config["language"];
    }
    // Подключаем мультиязычность
    $language = (new Language())->get($lang);

    if ($session->authorize == 1) {
        $user_id = Crypto::decrypt($session->user_id, $session_key);
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
            $session->user_id = Crypto::encrypt($user['response']['id'], $session_key);
            $user_id = $user['response']['id'];
        }
    }

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
                $callback = array(
                    'status' => 200,
                    'title' => '<div class="text-center">Спасибо за заказ. Копию заказа мы отправили вам на почту.</div>',
                    'text' => ''
                );
                // Выводим заголовки
                $response->withStatus(200);
                $response->withHeader('Content-type', 'application/json');
                // Выводим json
                echo json_encode($callback);
                }
            } else {
                $callback = array(
                    'status' => 400,
                    'title' => "Сообщение системы",
                    'text' => "3"
                );
                // Выводим заголовки
                $response->withStatus(200);
                $response->withHeader('Content-type', 'application/json');
                // Выводим json
                echo json_encode($callback);
            }
        } else {
            $callback = array(
                'status' => 400,
                'title' => "Сообщение системы",
                'text' => "2"
            );
            // Выводим заголовки
            $response->withStatus(200);
            $response->withHeader('Content-type', 'application/json');
            // Выводим json
            echo json_encode($callback);
        }
    } else {
        $callback = [
            'status' => 400,
            'title' => "Сообщение системы",
            'text' => "1"
        ];
        // Выводим заголовки
        $response->withStatus(200);
        $response->withHeader('Content-type', 'application/json');
        // Выводим json
        echo json_encode($callback);
    }
 
    });
 
$app->post('/cart/add-to-cart', function (Request $request, Response $response, array $args) {
    // Получаем конфигурацию \ApiShop\Config\Settings
    $config = (new Settings())->get();
    // Подключаем сессию
    $session = new Session($config['settings']['session']['name']);
    // Читаем ключи
    $session_key = $config['key']['session'];
    $cookie_key = $config['key']['cookie'];
    // Разбираем post
    $post = $request->getParsedBody();
    $id = filter_var($post['id'], FILTER_SANITIZE_STRING);
    $product_id = filter_var($post['product_id'], FILTER_SANITIZE_STRING);
    $price = filter_var($post['price'], FILTER_SANITIZE_STRING);
    $num = filter_var($post['num'], FILTER_SANITIZE_STRING);
    $cookie = Crypto::decrypt($_COOKIE[$config['settings']['session']['name']], $cookie_key);

    // Получаем массив данных из таблицы language на языке из $session->language
    $langs = new Langs();
    // Получаем массив данных из таблицы language на языке из $session->language
    if (isset($session->language)) {
        $lang = $session->language;
    } elseif ($langs->getLanguage()) {
        $lang = $langs->getLanguage();
    } else {
        $lang = $site_config["language"];
    }
    // Подключаем мультиязычность
    $language = (new Language())->get($lang);
 
    if ($session->authorize == 1) {
        $user_id = Crypto::decrypt($session->user_id, $session_key);
    } else {
        $user_id = 0;
    }
 
    $cartArr = [
        'user_id' => $user_id,
        'cookie' => $cookie,
        'product_id' => $product_id,
        'num' => $num,
        'price' => $price,
        'currency_id' => $config['settings']['site']['currency_id'],
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
    // Отправляем запрос и получаем данные
    $cart = $db->post($resource, $cartArr);
 
    if ($cart >= 1) {
        $callback = [
            'status' => 200,
            'title' => $language["23"],
            'text' => $language["126"]." ".$language["124"]."<br>".$language["194"]." ".$price
        ];
        $response->withStatus(200);
        $response->withHeader('Content-type', 'application/json');
        echo json_encode($callback);
    }
 
});