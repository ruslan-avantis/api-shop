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
 
use RouterDb\Db;
use RouterDb\Router;
    
use ApiShop\Config\Settings;
use ApiShop\Utilities\Utility;
use ApiShop\Hooks\Hook;
use ApiShop\Resources\Language;
use ApiShop\Resources\Site;
use ApiShop\Resources\Template;
use ApiShop\Resources\Install;
use ApiShop\Resources\User;
use ApiShop\Resources\Products;
use ApiShop\Model\SessionUser;
 
$config = (new Settings())->get();
$index_router = $config['routers']['index'];
 
$app->get($index_router, function (Request $request, Response $response, array $args) {
 
    // Передаем данные Hooks для обработки ожидающим классам
    $hook = new Hook();
    $hook->http($request, $response, $args, 'GET');
    $request = $hook->request();
    $args = $hook->args();
 
    // Подключаем плагины
    $utility = new Utility();
    // Получаем параметры из URL
    $getParams = $request->getQueryParams();
    $host = $request->getUri()->getHost();
    $path = $request->getUri()->getPath();
    // Получаем конфигурацию
    $config = (new Settings())->get();
    // Конфигурация роутинга
    $routers = $config['routers'];
    // Подключаем мультиязычность
    $language = (new Language($getParams))->get();
    // Меню, берет название класса из конфигурации
    $menu = (new $config['vendor']['menu']())->get();
    // Подключаем сессию, берет название класса из конфигурации
    $session = new $config['vendor']['session']($config['settings']['session']['name']);
    // Данные пользователя из сессии
    $sessionUser =(new SessionUser())->get();
    // Подключаем временное хранилище
    $session_temp = new $config['vendor']['session']("_temp");
    // Читаем ключи
    $token_key = $config['key']['token'];
    // Генерируем токен
    $token = $utility->random_token();
    // Записываем токен в сессию
    $session->token = $config['vendor']['crypto']::encrypt($token, $token_key);
    // Контент по умолчанию
    $content = '';
    $render = '';
 
    if ($config["settings"]["install"]["status"] != null) {
        // Настройки сайта
        $site = new Site();
        $site_config = $site->get();
        // Получаем название шаблона
        $site_template = $site->template();
        // Конфигурация шаблона
        $templateConfig = new Template($site_template);
        $template = $templateConfig->get();
        // Шаблон по умолчанию 404
        $render = $template['layouts']['404'] ? $template['layouts']['404'] : '404.html';
    }
 
    // Заголовки по умолчанию из конфигурации
    $title = $config['settings']['site']['title'];
    $keywords = $config['settings']['site']['keywords'];
    $description = $config['settings']['site']['description'];
    $robots = $config['settings']['site']['robots'];
    $og_title = $config['settings']['site']['og_title'];
    $og_description = $config['settings']['site']['og_description'];
    $og_image = $config['settings']['site']['og_image'];
    $og_type = $config['settings']['site']['og_type'];
    $og_locale = $config['settings']['site']['og_locale'];
    $og_url = $config['settings']['site']['og_url'];
 
    $head = [
        "page" => 'home',
        "title" => $title,
        "keywords" => $keywords,
        "description" => $description,
        "robots" => $robots,
        "og_title" => $og_title,
        "og_description" => $og_description,
        "og_image" => $og_image,
        "og_type" => $og_type,
        "og_locale" => $og_locale,
        "og_url" => $og_url,
        "host" => $host,
        "path" => $path
    ];
 
    if ($config["settings"]["install"]["status"] != null) {
 
        $templateConfig = new Template($config["settings"]["themes"]["template"]);
        $template = $templateConfig->get();
 
        // Эти параметры должны браться из конфигурации шаблона
        $arr = array(
            "limit" => $template['products']['home']['limit'],
            "sort" => $template['products']['home']['sort'],
            "order" => $template['products']['home']['order'],
            "relations" => $template['products']['home']['relations'],
            "state_seller" => 1
        );
 
        // Получаем список товаров
        $productsList = new $config['vendor']['products_home']();
        $content = $productsList->get($arr, $template, $host);
        
        if (count($content) >= 1) {
            $render = $template['layouts']['index'] ? $template['layouts']['index'] : 'index.html';
        }
        
        $view = [
            "head" => $head,
            "routers" => $routers,
            "site" => $site_config,
            "config" => $config['settings']['site'],
            "language" => $language,
            "template" => $template,
            "token" => $session->token,
            "session" => $sessionUser,
            "menu" => $menu,
            "content" => $content
        ];
 
    }
    else {
        // Если ключа доступа у нет, значит сайт еще не активирован
        $content = '';
        $render = 'index.html';
        //$session->install = null;
 
        if (isset($session->install)) {
            if ($session->install == 1) {
                $render = "stores.html";
                $content = (new Install())->stores_list();
            } elseif ($session->install == 2) {
                $render = "templates.html";
                if (isset($session->install_store)) {
                    $install_store = $session->install_store;
                } else {
                    $install_store = null;
                }
                $content = (new Install())->templates_list($install_store);
            } elseif ($session->install == 3) {
                $render = "welcome.html";
            } elseif ($session->install == 10) {
                $render = "templates.html";
                $content = (new Install())->templates_list(null);
            } elseif ($session->install == 11) {
                $render = "key.html";
            }
        }
 
        $view = [
            "head" => $head,
            "template" => "install",
            "routers" => $routers,
            "config" => $config['settings']['site'],
            "language" => $language,
            "token" => $session->token,
            "session" => $sessionUser,
            "content" => $content
        ];
 
    }
 
    // Запись в лог
    $this->logger->info($render);
 
    // Передаем данные Hooks для обработки ожидающим классам
    $hook->get($view, $render);
    // Отдаем данные шаблонизатору
    return $this->view->render($hook->render(), $hook->view());
 
});
 