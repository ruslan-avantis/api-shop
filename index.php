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
 
if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}
 
// Запускаем сессию PHP
session_start();
 
$vendor_dir = '';
// Указываем путь к папке vendor
if (file_exists(__DIR__ . '/vendor')) {
    $vendor_dir = __DIR__ . '/vendor';
} elseif (__DIR__ . '/../vendor') {
    $vendor_dir = __DIR__ . '/../vendor';
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
    require __DIR__ . '/app/settings.php';
    $config = \ApiShop\Config\Settings::get();
 
    // Получаем список и конфигурацию пакетов
    $package = json_decode(file_get_contents($auto_require), true);
	$slimSettings = $package['require']['slim.slim']['settings'];
 
	$slim = [];
 
	$slim['debug'] = false;
	$slim['displayErrorDetails'] = false;
	$slim['addContentLengthHeader'] = false;
	$slim['determineRouteBeforeAppMiddleware'] = false;
 
    if (isset($slimSettings['displayErrorDetails'])) {if ($slimSettings['displayErrorDetails'] == 1) {
        ini_set('error_reporting', E_ALL);
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
		$slim['displayErrorDetails'] = true;
    }}
	if (isset($slimSettings['debug'])) {if ($slimSettings['debug'] == 1) {
	    $slim['debug'] = true;
	}}
	if (isset($slimSettings['addContentLengthHeader'])) {if ($slimSettings['addContentLengthHeader'] == 1) {
	    $slim['addContentLengthHeader'] = true;
	}}
	if (isset($slimSettings['determineRouteBeforeAppMiddleware'])) {if ($slimSettings['determineRouteBeforeAppMiddleware'] == 1) {
	    $slim['determineRouteBeforeAppMiddleware'] = true;
	}}
 
    // Подключаем Slim и отдаем ему конфигурацию
    $app = new \Slim\App($slim);
 
    // Для POST запросов вначале url генерируем post_id
    // Если у пользователя нет сессии он не сможет отправлять POST запросы
    $session = new $config['vendor']['session']['session']($config['settings']['session']['name']);
    $post_id = '/_'; if(isset($session->post_id)){$post_id = '/'.$session->post_id;}
 
    // Run User Session
    // Запускаем сессию пользователя
    (new \ApiShop\Model\User())->run();
 
    // Получаем конфигурацию роутеров
    $router = $config['routers']['site'];
    // Подключаем Routers и Containers
    require __DIR__ . '/app/run.php';
    // Slim Run
    $app->run();
 
} else {
    ini_set('error_reporting', E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
}
 