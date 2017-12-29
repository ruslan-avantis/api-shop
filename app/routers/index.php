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
use ApiShop\Config\Settings;
use ApiShop\Utilities\Utility;
use ApiShop\Resources\Language;
use ApiShop\Resources\Site;
use ApiShop\Database\Router;
use ApiShop\Database\Ping;
use ApiShop\Model\SessionUser;

$app->get('/', function (Request $request, Response $response, array $args) {
    // Получаем конфигурацию \ApiShop\Config\Settings
    $config = (new Settings())->get();
    $site = new Site();
    $site_config = $site->get();
    // Подключаем сессию
    $session = new Session($config['settings']['session']['name']);
    // Подключаем временное хранилище
    $session_temp = new Session("_temp");
    // Читаем ключи
    $session_key = $config['key']['session'];
    $token_key = $config['key']['token'];
    // Получаем массив данных из таблицы language на языке из $session->language
    if (isset($session->language)) {
        $lang = $session->language;
    } else {
        $lang = $site_config["language"];
    }
    // Подключаем мультиязычность
    $language = (new Language())->get($lang);
    //print_r($language);
    // Подключаем плагины
    $utility = new Utility();
    // Генерируем токен
    $token = $utility->random_token();
    // Записываем токен в сессию
    $session->token = Crypto::encrypt($token, $token_key);
    // Если запись об авторизации есть расшифровываем
    if ($session->authorize) {
        $authorize = $session->authorize;
    } else {
        $session->authorize = 0;
        $authorize = 0;
    }
    // Отдаем информацию для шаблонизатора
    // Информацию о странице
    $page = ["page" => 'home'];
    // Данные пользователя из сессии
    $session_user_data =(new SessionUser())->get();
    // Что бы не давало ошибку присваиваем пустое значение
    $content = '';
    // print_r($content);
 
    // Формируем параметры запроса
    $arr = array(
        "limit"            => 9,
        "brand_id"        => '66',
        "articul"        => '80x190'
    );
    // Подключаем ApiShop\Database\Router
    $database = new Router((new Ping("price"))->get());
    // Отправляем запрос и получаем данные
    $response = $database->get("price", $arr);
    // Если ответ не пустой
    if (count($response['items']) >= 1) {
        foreach($response['items'] as $item)
        {
            // Обрабатываем картинки
            $product['image']['no_image'] = $utility->get_image(null, 'https://life24.com.ua/images/no_image.png', 360, 360);
            $image_1 = '';
            $image_1 = (isset($item['item']['image']['1'])) ? $utility->clean($item['item']['image']['1']) : null;
            if (isset($image_1)) {$product['image']['1'] = $utility->get_image($item['item']['product_id'], $image_1, 360, 360);}
            $image_2 = '';
            $image_2 = (isset($item['item']['image']['2'])) ? $utility->clean($item['item']['image']['2']) : null;
            if (isset($image_2)) {$product['image']['2'] = $utility->get_image($item['item']['product_id'], $image_2, 360, 360);}

            $path_url = pathinfo($item['item']['url']);
            $basename = $path_url['basename']; // lib.inc.php
            $baseurl = str_replace('-'.$item['item']['product_id'].'.html', '', $basename);

            $product['url'] = '/product/'.$item['item']['id'].'/'.$baseurl.'.html';
            $product['name'] = (isset($item['item']['name'])) ? $utility->clean($item['item']['name']) : '';
            $product['type'] = (isset($item['item']['type'])) ? $utility->clean($item['item']['type']) : '';
            $product['brand'] = (isset($item['item']['brand'])) ? $utility->clean($item['item']['brand']) : '';
            $product['serie'] = (isset($item['item']['serie'])) ? $utility->clean($item['item']['serie']) : '';
            $product['articul'] = (isset($item['item']['articul'])) ? $utility->clean($item['item']['articul']) : '';
            if ($item['item']['serie'] && $item['item']['articul']) {$product['name'] = $item['item']['serie'].' '.$item['item']['articul'];}
            $product['oldprice'] = (isset($item['item']['oldprice'])) ? $utility->clean($item['item']['oldprice']) : '';
            $product['price'] = (isset($item['item']['price'])) ? $utility->clean($item['item']['price']) : '';
            $product['available'] = (isset($item['item']['available'])) ? $utility->clean($item['item']['available']) : '';

            $rand = rand(1000, 5000);
            $date = date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s")." +".$rand." minutes"));
            $date = strtotime($date);
            $product['y'] = date("Y", $date);
            $product['m'] = date("m", $date);
            $product['d'] = date("d", $date);
            $product['h'] = date("H", $date);
            $product['i'] = date("i", $date);
            $product['s'] = date("s", $date);
            // Отдаем данные шаблонизатору 
            $products['product'][] = $product;
        }
    } else {
        $products = null;
    }
 
    // Запись в лог
    $this->logger->info("home");
 
    return $this->twig->render('index.html', [
        "template" => $site->template(),
        "pages" => $page,
        "site" => $site_config,
        "config" => $config['settings']['site'],
        "language" => $language,
        "token" => $session->token,
        "session" => $session_user_data,
        "session_temp" => $session_temp,
        "content" => $content,
        "products" => $products
    ]);
 
});
 