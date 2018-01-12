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
$container['twig'] = function ($themes) {
 
    $config = $themes->get('settings')['themes'];
 
    if ($themes->get('settings')["install"]["status"] != null) {
        // Получаем название шаблона
        $template = $config["template"]; // По умолчанию mini-mo
        $site = new Site();
        if ($site->template()) {
            $template = $site->template();
        }
 
        $loader = new \Twig_Loader_Filesystem($config['dir']."/".$config['templates']."/".$template."/layouts");
        $twig = new \Twig_Environment($loader, array(
            'cache' => false,
            'strict_variables' => false
        ));
 
	} else {
        $loader = new \Twig_Loader_Filesystem($config['dir']."/".$config['templates']."/install");
        $twig = new \Twig_Environment($loader, array(
            'cache' => false,
            'strict_variables' => false
        ));
	}
 
    return $twig;
 
	};
 