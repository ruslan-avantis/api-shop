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
 
use ApiShop\Resources\Site;
use ApiShop\Config\Settings;

//use Twig_Loader_Filesystem;
//use Twig_Environment;
 
$container = $app->getContainer();

// monolog
$container['logger'] = function ($logger) {
    $settings = $logger->get('settings')['logger'];
    $logger = new \Monolog\Logger($settings['name']);
    $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
    $logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

// Register Original Twig View
$container['view'] = function ($conf) {
 
    $config = $conf->get('settings')['themes'];
 
    if ($conf->get('settings')["install"]["status"] != null) {
        // Получаем название шаблона
        $template = $config["template"]; // По умолчанию mini-mo
        $site = new Site();
        $site->get();
        if ($site->template()) {
            $template = $site->template();
        }
 
        $loader = new \Twig_Loader_Filesystem($config['dir']."/".$config['templates']."/".$template."/layouts");
        $view = new \Twig_Environment($loader, array(
            'cache' => false,
            'strict_variables' => false
        ));
 
    } else {
 
        $loader = new \Twig_Loader_Filesystem($config['dir']."/".$config['templates']."/install");
        $view = new \Twig_Environment($loader, array(
            'cache' => false,
            'strict_variables' => false
        ));
    }
 
    return $view;
 
};

// Register Original Twig View Admin Panel
$container['admin'] = function () {
 
    $config = (new Settings())->get();
    // Получаем название шаблона
    $template = $config['admin']["template"];
 
    $loader = new \Twig_Loader_Filesystem($config['settings']['themes']['dir']."/".$config['settings']['themes']['templates']."/".$template."/layouts");
    $admin = new \Twig_Environment($loader, array(
        'cache' => false,
        'strict_variables' => false
    ));
 
    return $admin;
 
};
 