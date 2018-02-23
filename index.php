<?php 
/**
    * This file is part of the {API}$hop
    *
    * @license http://opensource.org/licenses/MIT
    * @link https://github.com/pllano/api-shop
    * @version 1.0.1
    * @package pllano.api-shop
    *
    * For the full copyright and license information, please view the LICENSE
    * file that was distributed with this source code.
*/
/**
    * API Shop дает полную свободу с выбора классов обработки страниц
    * При установке пекетов или шаблонов вы можете перезаписать в конфиге класс и функцию обработки
    * Вы можете использовать контроллеры по умолчанию и вносить изменения с помощью \Pllano\Hooks\Hook
    * Вы можете использовать ApiShop\Adapter\ и менять vendor в конфигурации
*/

declare(strict_types = 1);

$startTime = microtime(true);

if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

define("BASE_PATH", dirname(__FILE__));

// Запускаем сессию PHP
session_start();

$vendor_dir = '';
// Ищем путь к папке vendor
if (file_exists(BASE_PATH . '/vendor')) {
    $vendor_dir = BASE_PATH . '/vendor';
} elseif (BASE_PATH . '/../vendor') {
    $vendor_dir = BASE_PATH . '/../vendor';
}

// Указываем путь к AutoRequire
$autoRequire = $vendor_dir.'/AutoRequire.php';
// Указываем путь к auto_require.json
$auto_require = $vendor_dir.'/auto_require.json';

if (file_exists($autoRequire) && file_exists($auto_require)) {

    // Connect \Pllano\AutoRequire\Autoloader
    require $autoRequire;
    // instantiate the loader
    $require = new \Pllano\AutoRequire\Autoloader();
    // Запускаем Автозагрузку
    $require->run($vendor_dir, $auto_require);

    // Подключаем файл конфигурации системы
    require BASE_PATH . '/app/settings.php';
    $config = \ApiShop\Config\Settings::get();

    // Получаем список и конфигурацию пакетов
    $package = json_decode(file_get_contents($auto_require), true);
    $routingSettings = $package['require']['slim.slim']['settings'];

    $routingConfig = [];

    $routingConfig['debug'] = true;
    $routingConfig['displayErrorDetails'] = true;
    $routingConfig['addContentLengthHeader'] = false;
    $routingConfig['determineRouteBeforeAppMiddleware'] = false;

    if (isset($routingSettings)) {
        foreach($routingSettings as $key => $val)
        {
            if((int)$val == 1){
                $routingConfig[$key] = true;
            } elseif((int)$val == 0) {
                $routingConfig[$key] = false;
            } else {
                $routingConfig[$key] = $val;
            }
        }
    }
    if ($routingConfig['debug'] === true) {
        error_reporting(E_ALL ^ E_NOTICE);
    }

    // Подключаем Slim и отдаем ему конфигурацию
    $routing = new \Slim\App($routingConfig);

    // Run User Session
    // Запускаем сессию пользователя
    (new \ApiShop\Model\User())->run();

    // Получаем конфигурацию роутеров
    $router = $config['routers']['site'];
    // Подключаем Routers и Containers
    require BASE_PATH . '/app/run.php';

    $container['time'] = microtime(true) - $startTime;
	// теперь в контейнере $time содержится float со значением выполнения скрипта в секундах. дело осталось за банальными number_format() и echo.

    // Routing Run
    $routing->run();

} else {
    error_reporting(E_ALL ^ E_NOTICE);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
}
 