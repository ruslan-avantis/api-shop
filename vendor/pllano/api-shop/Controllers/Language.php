<?php
/**
 * This file is part of the {API}$hop
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/pllano/api-shop
 * @version 1.1.1
 * @package pllano.api-shop
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
namespace Pllano\ApiShop\Controllers;
 
use Psr\Http\Message\{ServerRequestInterface as Request, ResponseInterface as Response};
use Psr\Container\ContainerInterface as Container;

class Language
{
 
    private $app;
	private $config = [];
	private $time_start;
    private $package = [];
    private $session;
	private $languages;
	private $logger;
    private $template;
	private $view;
    private $route;
    private $query;

    function __construct(Container $app, $route)
    {
		$this->app = $app;
		$this->route = $route;
		$this->config = $app->get('config');
		$this->time_start = $app->get('time_start');
        $this->package = $app->get('package');
		$this->session = $app->get('session');
		$this->languages = $app->get('languages');
        $this->logger = $app->get('logger');
        $this->template = $app->get('template');
		$this->view = $app->get('view');
    }
 
    public function get(Request $request, Response $response, array $args)
    {
        $config = $this->config;
        $session = $this->session;
		$language = $this->languages->get($request);
        $langs = new $config['vendor']['detector']['language']();

        if (isset($session->language)) {
            $lang = $session->language;
        } elseif ($langs->getLanguage()) {
            $lang = $langs->getLanguage();
        } else {
            $lang = $config['settings']['language'];
        }

        foreach($language as $key => $value)
        {
            $arr["id"] = $key;
            $arr["name"] = $value;
            $langArr[] = $arr;
        }
        // callback - Даем ответ в виде json о результате
        $callback = [
            'language' => $lang,
            'languages' => $langArr,
            'status' => 200
        ];
        // Выводим заголовки
        $response->withStatus(200);
        $response->withHeader('Content-type', 'application/json');
 
        // Выводим json
        return $response->write(json_encode($callback));
 
    }
    
    public function post(Request $request, Response $response, array $args)
    {
        $config = $this->config;
        $session = $this->session;
		$language = $this->languages->get($request);
        $langs = new $config['vendor']['detector']['language']();

        if (isset($session->language)) {
            $lang = $session->language;
        } elseif ($langs->getLanguage()) {
            $lang = $langs->getLanguage();
        } else {
            $lang = $config['settings']['language'];
        }
        // Получаем данные отправленные нам через POST
        $post = $request->getParsedBody();
        $lg = filter_var($post['id'], FILTER_SANITIZE_STRING);
        if ($lg) {
            // Записываем в сессию язык выбранный пользователем
            if ($lg == 1) {$session->language = "ru";}
            if ($lg == 2) {$session->language = "ua";}
            if ($lg == 3) {$session->language = "en";}
            if ($lg == 4) {$session->language = "de";}
        }

        foreach($language as $key => $value)
        {
            $arr["id"] = $key;
            $arr["name"] = $value;
            $langArr[] = $arr;
        }
        // callback - Даем ответ в виде json о результате
        $callback = [
            'language' => $session->language,
            'languages' => $langArr,
            'status' => 200
        ];
        // Выводим заголовки
        $response->withStatus(200);
        $response->withHeader('Content-type', 'application/json');
 
        // Выводим json
        return $response->write(json_encode($callback));
 
    }
 
}
 