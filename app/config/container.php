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
    $config = (new Settings())->get();
    $settings = $config['settings']['logger'];
    $logger = new \Monolog\Logger($settings['name']);
    $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
    $logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};
 
// Register \Pllano\Adapter\TemplateEngine
$container['view'] = function () {
    $config = (new Settings())->get();
    // Получаем название шаблона
    $template = $config['template']['front_end']['themes']["template"]; // По умолчанию mini-mo
    $site = new Site($config);
    $site->get();
    if ($site->template()) {
        $template = $site->template();
    }
    return new $config['vendor']['template_engine']($config, $template);
};
 
// Register Original Twig View
$container['twig'] = function () {
    $config = (new Settings())->get();
    $themes = $config['settings']['themes'];
 
    if ($config['settings']["install"]["status"] != null) {
        // Получаем название шаблона
        $template = $themes["template"]; // По умолчанию mini-mo
        $site = new Site($config);
        $site->get();
        if ($site->template()) {
            $template = $site->template();
        }
        $cache = false;
        $strict_variables = false;
        $loader = new \Twig_Loader_Filesystem($themes['dir']."/".$themes['templates']."/".$template."/layouts");
        if (isset($config['cache']['twig']['state'])) {
            if ((int)$config['cache']['twig']['state'] == 1) {
                $cache = __DIR__ .''.$config['cache']['twig']['cache_dir'];
                $strict_variables = $config['cache']['twig']['strict_variables'];
            }
        }
        $twig = new \Twig_Environment($loader, ['cache' => $cache, 'strict_variables' => $strict_variables]);
 
    } else {
 
        $loader = new \Twig_Loader_Filesystem($themes['dir']."/".$themes['templates']."/install");
        $twig = new \Twig_Environment($loader, ['cache' => false, 'strict_variables' => false]);
    }
 
    return $twig;
 
};
 
// Register Original Twig View Admin Panel
$container['admin'] = function () {
 
    $config = (new Settings())->get();
    // Получаем название шаблона
    $template = $config['admin']["template"];
 
    $loader = new \Twig_Loader_Filesystem($config['settings']['themes']['dir']."/".$config['settings']['themes']['templates']."/".$template."/layouts");
    $admin = new \Twig_Environment($loader, ['cache' => false, 'strict_variables' => false]);
 
    return $admin;
 
};
 