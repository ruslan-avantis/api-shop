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
use ApiShop\Model\SessionUser;
use ApiShop\Resources\Language;
use ApiShop\Resources\Menu;
use ApiShop\Resources\Site;

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
    if ($session->language) {
        $lang = $session->language;
    } else {
        $lang = $site_config["language"];
    }
    // Подключаем мультиязычность
	$language = (new Language())->get($lang);
    // Подключаем плагины
    $utility = new Utility();
    // Генерируем токен
    $token = $utility->random_token();
    // Записываем токен в сессию
    $session->token = Crypto::encrypt($token, $token_key);
    // Если запись об авторизации есть расшифровываем
    if ($session->authorize) {
        $authorize = Crypto::decrypt($session->authorize, $session_key);
    } else {
        $session->authorize = Crypto::encrypt('0', $session_key);
        $authorize = 0;
    }
    // Отдаем информацию о станице для правильной работы шаблонов
    $page = ["page" => 'home'];
    // Получаем данные Menu пользователя которые отдадим шаблонизатору Twig
    $menu = (new Menu())->get();
 
    // Что бы не давало ошибку присваиваем пустое значение
    $content = '';
    // print_r($content);

    // Запись в лог
    $this->logger->info("home");
 
    return $this->twig->render('index.html', [
        "template" => $site->template(),
        "pages" => $page,
        "site" => $site_config,
        "config" => $config['settings']['site'],
        "language" => $language,
        "token" => $session->token,
        "session_temp" => $session_temp,
        "menu" => $menu,
        "content" => $content
    ]);
 
});
 