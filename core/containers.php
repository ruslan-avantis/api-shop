<?php /**
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

use Pimple\Container;
use Pimple\Psr11\Container as PsrContainer;
use Pllano\ApiShop\Models\{
    Language, 
    Template
};

$container = new Container();
// Создаем контейнер с глобальной конфигурацией
$container['config'] = $config;
// Создаем контейнер нача работы скрипта
$container['time_start'] = $time_start;
// Создаем контейнер с конфигурацией пакетов
$container['package'] = $package;

// monolog
$container['logger'] = function ($c) {
    $settings = $c['config']['settings']['logger'];
    $logger = new \Monolog\Logger($settings['name']);
    $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
    $logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

// session
$container['session'] = function ($c) {
    return new $c['config']['vendor']['session']['session']($c['config']['settings']['session']['name']);
};

// Конфигурация шаблона
$container['template'] = function ($c) {
    return (new Template($c['config'], $c['config']['template']['front_end']['themes']['template']))->get();
};

// Конфигурация шаблона
$container['admin_template'] = function ($c) {
    return (new Template($c['config'], $c['config']['template']['back_end']['themes']['template']))->get();
};

// languages
$container['languages'] = function ($c) {
    return new Language($c['config'], $c['session']);
};

// Register \Pllano\Adapters\TemplateEngine
$container['view'] = function ($c) {
    $view = null;
    if ($c['config']['settings']['install']['status'] != null) {
        // Получаем название шаблона из конфигурации
        $template = $c['config']['template']['front_end']['themes']['template']; // По умолчанию mini-mo-twig
        $view = new $c['config']['vendor']['templates']['template_engine']($c['config'], $c['package']['require'], $template);
    } else {
        $loader = new \Twig_Loader_Filesystem($c['config']['template']['front_end']['themes']['dir']."/".$c['config']['template']['front_end']['themes']['templates']."/install");
        $view = new \Twig_Environment($loader, ['cache' => false, 'strict_variables' => false]);
    }
    return $view;
};

// Register Original Twig View Admin Panel
$container['admin'] = function ($c) {
    // Получаем название шаблона
    $template = $c['config']['template']['back_end']['themes']['template'];
    $loader = new \Twig_Loader_Filesystem($c['config']['template']['back_end']['themes']['dir']."/".$c['config']['template']['back_end']['themes']['templates']."/".$template."/layouts");
    $twig_config = [];
    $twig_config['cache'] = false;
    $twig_config['strict_variables'] = false;
    if($c['config']['template']['back_end']['cache'] == 1){
        $twig_config['cache'] = $c['config']['template']['back_end']['cache'];
    }
    if($c['package']['require']['twig.twig']['settings']['strict_variables'] == 1) {
        $twig_config['strict_variables'] = true;
    }
    return new \Twig_Environment($loader, $twig_config);
};

// Containers Psr11
$core = new PsrContainer($container);
// Register containers
$routing->setContainer($core);
 