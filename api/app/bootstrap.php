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
 
/**
 * Automatically register models
 * Автоматическое подключение классов
 */
$classes = glob(__DIR__ . '/classes/*.php');
foreach ($classes as $class) {
require $class;
}
 
/**
 * Automatically register routers
 * Автоматическое подключение роутеров
 */
$routers = glob(__DIR__ . '/routers/*.php');
foreach ($routers as $router) {
require $router;
}

/**
 * Automatically register services
 * Автоматическое подключение сервисов
 */
$services = glob(__DIR__ . '/../services/*.php');
foreach ($services as $service) {
require $service;
}
