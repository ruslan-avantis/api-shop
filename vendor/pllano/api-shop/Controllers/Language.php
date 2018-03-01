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

class Language
{
 
    private $config = [];
    private $package = [];
    private $session;
	private $languages;
	private $logger;
    private $view;
    private $route;
    private $query;
 
    function __construct($query, $route, $config, $package, $session, $languages, $view, $logger)
    {
        $this->config = $config;
        $this->package = $package;
		$this->session = $session;
		$this->languages = $languages;
        $this->logger = $logger;
        $this->view = $view;
        $this->route = $route;
        $this->query = $query;
    }
 
    public function get(Request $request, Response $response)
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
        echo json_encode($callback);
 
    }
    
    public function post(Request $request, Response $response)
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
        echo json_encode($callback);
 
    }
 
}
 