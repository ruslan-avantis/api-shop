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

// Вывод ошибок. Что бы выключить закоментируйте эти строки
// ini_set('error_reporting', E_ALL);
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    
    if (is_file($file)) {
        return false;
    }
}

// Проверяем наличие файлов API Shop
if (!file_exists(__DIR__ . '/app/test.php')) {

}

// Подключаем autoloader
require __DIR__ . '/app/autoloader.php';
// instantiate the loader
$loader = new \Psr4\Autoloader;
// register the autoloader
$loader->register();
// register the base directories for the namespace prefix
$loader->addNamespace('Pllano\ApiShop', __DIR__ . '/app/classes');

// Подключаем Composer
if (file_exists(__DIR__ . '/../vendor/autoload.php')){
    require __DIR__ . '/../vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/vendor/autoload.php')){
    require __DIR__ . '/vendor/autoload.php';
} else {
    // Если autoload.php не найден - подключаем загрузчик пакетов
    require __DIR__ . '/app/installer.php';
    
    $loaders = new \Pllano\ApiShop\Loader();
    // Запускаем загрузку пакетов и указываем директорию
    $load = $loaders->run(__DIR__ . '/../vendor');
    
    if ($load == true){
        require __DIR__ . '/vendor/autoload.php';
    } else {
        $error = new \Pllano\ApiShop\Error();
        $error->permission();
    }
}

require __DIR__ . '/app/config/settings.php';
// Подключаем файл конфигурации системы
$settings = new \Pllano\ApiShop\Core\Settings();
$config = $settings->get();

// Подключаем Slim и отдаем ему Конфиг
$app = new \Slim\App($config);

// Запускаем сессию PHP
session_start();
// Run User Session
// Запускаем сессию пользователя
(new \Pllano\ApiShop\Model\User())->run();

$cores = glob(__DIR__ . '/app/core/*.php');
foreach ($cores as $core) {
    require $core;
}
 
// Automatically register routers
// Автоматическое подключение роутеров
$routers = glob(__DIR__ . '/app/routers/*.php');
foreach ($routers as $router) {
    require $router;
}

// Slim Run
$app->run();
 
