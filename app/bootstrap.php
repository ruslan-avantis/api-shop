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
$classes_subdir = glob(__DIR__ . '/classes/*/*.php');
foreach ($classes_subdir as $class) {
require $class;
}
 
$classes = glob(__DIR__ . '/classes/*.php');
foreach ($classes as $class) {
require $class;
}
 
/**
 * Запускаем сессию PHP
 */
session_start();
 
/**
 * Run User Session
 * Запускаем сессию пользователя
 */
(new Model\User())->run();
 
/**
 * Automatically register routers
 * Автоматическое подключение роутеров
 */
$routers = glob(__DIR__ . '/routers/*.php');
foreach ($routers as $router) {
require $router;
}
 
