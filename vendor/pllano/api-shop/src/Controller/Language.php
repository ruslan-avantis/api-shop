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
 
namespace ApiShop\Controller;
 
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
 
use ApiShop\Model\Language as Languages;
 
class Language
{
 
	private $config = [];
	protected $logger;
	protected $view;
 
    function __construct($config, $view, $logger)
    {
		$this->config = $config;
		$this->logger = $logger;
		$this->view = $view;
    }
 
    public function get(Request $request, Response $response, array $args)
    {
        $config = $this->config;
 
        // Подключаем сессию, берет название класса из конфигурации
        // $session = new Session();
        $session = new $config['vendor']['session']['session']($config['settings']['session']['name']);
        $langs = new $config['vendor']['detector']['language']();
        // Получаем массив данных из таблицы language на языке из $session->language
        if (isset($session->language)) {
            $lang = $session->language;
        } elseif ($langs->getLanguage()) {
            $lang = $langs->getLanguage();
        } else {
            $lang = $config['settings']['language'];
        }
        $languages = new Languages($request, $config);
        $language = $languages->get();
        foreach($language as $key => $value)
        {
            $arr["id"] = $key;
            $arr["name"] = $value;
            $langArr[] = $arr;
        }
        // callback - Даем ответ в виде json о результате
        $callback = array(
            'language' => $lang,
            'languages' => $langArr,
            'status' => 200
        );
        // Выводим заголовки
        $response->withStatus(200);
        $response->withHeader('Content-type', 'application/json');
 
        // Выводим json
        echo json_encode($callback);
 
	}
	
    public function post(Request $request, Response $response, array $args)
    {
        $config = $this->config;
 
        // Подключаем сессию, берет название класса из конфигурации
        // $session = new Session();
        $session = new $config['vendor']['session']['session']($config['settings']['session']['name']);
        $langs = new $config['vendor']['detector']['language']();
        // Получаем массив данных из таблицы language на языке из $session->language
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
        $languages = new Languages($request, $config);
        $language = $languages->get();
        foreach($language as $key => $value)
        {
            $arr["id"] = $key;
            $arr["name"] = $value;
            $langArr[] = $arr;
        }
        // callback - Даем ответ в виде json о результате
        $callback = array(
            'language' => $session->language,
            'languages' => $langArr,
            'status' => 200
        );
        // Выводим заголовки
        $response->withStatus(200);
        $response->withHeader('Content-type', 'application/json');
 
        // Выводим json
        echo json_encode($callback);
 
    }
 
}
 