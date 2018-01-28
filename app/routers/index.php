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
use ApiShop\Resources\Language;
use ApiShop\Resources\Site;
use ApiShop\Resources\Template;
use ApiShop\Resources\Install;
use ApiShop\Resources\User;
use ApiShop\Resources\Products;
use ApiShop\Model\SessionUser;

use ApiShop\Admin\Packages;
 
$config = (new Settings())->get();
$index_router = $config['routers']['index'];
 
$app->get($index_router, function (Request $request, Response $response, array $args) {
    $host = $request->getUri()->getHost();
    $path = $request->getUri()->getPath();
    // Получаем конфигурацию \ApiShop\Config\Settings
    $config = (new Settings())->get();
    $routers = $config['routers'];
 
    // Подключаем сессию, берет название класса из конфигурации
    $session = new $config['vendor']['session']($config['settings']['session']['name']);
 
    // Данные пользователя из сессии
    $sessionUser =(new SessionUser())->get();
    // Читаем ключи
    $session_key = $config['key']['session'];
    $token_key = $config['key']['token'];
 
    // Получаем параметры из URL
    $getParams = $request->getQueryParams();
 
    // Подключаем мультиязычность
    $language = (new Language($getParams))->get();
 
    // Подключаем плагины
    $utility = new Utility();
    // Генерируем токен
    $token = $utility->random_token();
    // Записываем токен в сессию
    $session->token = $config['vendor']['crypto']::encrypt($token, $token_key);
 
    // Что бы не давало ошибку присваиваем пустое значение
    $content = '';
    // Меню, берет название класса из конфигурации
    $menu = (new $config['vendor']['menu']())->get();
 
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
 
    if ($config["settings"]["install"]["status"] != null) {
 
        $site = new Site();
        $site_config = $site->get();
 
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
        $products = $productsList->get($arr, $template);
 
        //$packages = new Packages();
        //$putArr = $template['install']['packages'];
        //print_r($putArr);
        //$packages->delete($putArr);
        //print_r($packages->get());
 
        // Запись в лог
        $this->logger->info("home");
 
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
 
        $view = [
            "head" => $head,
            "routers" => $routers,
            "site" => $site_config,
            "config" => $config['settings']['site'],
            "language" => $language,
            "template" => $template,
            "token" => $session->token,
            "session" => $sessionUser,
            "content" => $content,
            "menu" => $menu,
            "products" => $products
        ];
 
        $render = $template['layouts']['index'] ? $template['layouts']['index'] : 'index.html';
 
        return $this->view->render($render, $view);
 
    }
    else {
        // Если ключа доступа у нет, значит сайт еще не активирован
        $content = '';
        $render = 'index';
        //$session->install = null;
 
        if (isset($session->install)) {
            if ($session->install == 1) {
                $render = "stores";
                $content = (new Install())->stores_list();
            } elseif ($session->install == 2) {
                $render = "templates";
                if (isset($session->install_store)) {
                    $install_store = $session->install_store;
                } else {
                    $install_store = null;
                }
                $content = (new Install())->templates_list($install_store);
            } elseif ($session->install == 3) {
                $render = "welcome";
            } elseif ($session->install == 10) {
                $render = "templates";
                $content = (new Install())->templates_list(null);
            } elseif ($session->install == 11) {
                $render = "key";
            }
        }
 
        $head = [
                "page" => 'install',
                "title" => "install",
                "keywords" => "install",
                "description" => "install",
                "og_title" => "install",
                "og_description" => "install",
                "host" => $host,
                "path" => $path
        ];
 
        $view = [
            "template" => "install",
            "routers" => $routers,
            "head" => $head,
            "config" => $config['settings']['site'],
            "language" => $language,
            "token" => $session->token,
            "session" => $sessionUser,
            "content" => $content
        ];
 
        return $this->view->render($render.'.html', $view);
 
    }
 
});
 