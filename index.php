<?php
/**
 * This file is part of the REST API SHOP library
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
 * Вывод ошибок. Что бы выключить закоментируйте эти строки
 */
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

/**
 * Подключаем пакеты
 */
if (file_exists(__DIR__ . '/../vendor/autoload.php';)){
    require __DIR__ . '/../vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/vendor/autoload.php';)){
    require __DIR__ . '/vendor/autoload.php';
} else {
    // Подключаем загрузчик пакетов
    require __DIR__ . '/app/installer.php';
    $loader = new Installer\Loader();
    // Запускаем загрузку пакетов
    $load = $loader->run();
    if ($load == true){
	    require_once __DIR__ . '/vendor/autoload.php';
	} else {
	require_once __DIR__ . '/app/error.php';
	$error = new Core\Error();
	$error->permission();
	}
}

/**
 * Подключаем файл конфигурации системы
 */
require_once __DIR__ . '/app/conf/settings.php';

/**
 * Подключаем Slim и отдаем ему Конфиг
 */
$settings = new Core\Settings();
$config = $settings->get();
$app = new Slim\App($config);

/**
 * Подключаем bootstrap
 */
require __DIR__ . '/app/bootstrap.php';

/**
 * Запускаем Slim
 */
$app->run();

