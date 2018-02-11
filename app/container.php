<?php 
/**
    * This file is part of the API SHOP
    *
    * @license http://opensource.org/licenses/MIT
    * @link https://github.com/pllano/api-shop
    * @version 1.1.0
    * @package pllano.api-shop
    *
    * For the full copyright and license information, please view the LICENSE
    * file that was distributed with this source code.
*/
 
use ApiShop\Model\Site;
use ApiShop\Config\Settings;
 
$container = $app->getContainer();
 
// Конфигурация доступна внутри и вне роутеров
// Получить внутри роутера $name = $this->config['name']; всю = $this->config
// Получить вне роутеров $name = $config['name']; всю = $config
$container['config'] = Settings::get();
 
// monolog
$container['logger'] = function ($config) {
 
    $settings = $config['settings']['logger'];
    $logger = new \Monolog\Logger($settings['name']);
    $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
    $logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
 
    return $logger;
 
};
 
// Register \Pllano\Adapter\TemplateEngine
$container['view'] = function ($config) {
 
    // Получаем название шаблона из конфигурации
    $template = $config['config']['template']['front_end']['themes']["template"]; // По умолчанию mini-mo
 
    $site = new Site($config['config']);
    $site->get();
    // Получаем название шаблона из конфигурации сайта
    if ($site->template()) {$template = $site->template();}
 
    return new $config['config']['vendor']['template_engine']($config['config'], $template);
 
};
 
// Register Original Twig View Admin Panel
$container['admin'] = function ($config) {
 
    // Получаем название шаблона
    $template = $config['config']['admin']["template"];
    $loader = new \Twig_Loader_Filesystem($config['config']['settings']['themes']['dir']."/".$config['config']['settings']['themes']['templates']."/".$template."/layouts");
    $admin = new \Twig_Environment($loader, ['cache' => false, 'strict_variables' => false]);
 
    return $admin;
 
};
 