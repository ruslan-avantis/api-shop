<?php /**
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

use Psr\Http\Message\{ServerRequestInterface as Request, ResponseInterface as Response};
use Pllano\RouterDb\Router as RouterDb;
use ApiShop\Model\Language;
use ApiShop\Utilities\Utility;
use ApiShop\Controller\Error;

class Cart
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
	
	public function post_add_to_cart(Request $request, Response $response)
	{
		$language = $this->languages->get($request);
		// Подключаем утилиты
		$utility = new Utility();
		// Читаем ключи
		$session_key = $this->config['key']['session'];
		$cookie_key = $this->config['key']['cookie'];
		// Разбираем post
		$post = $request->getParsedBody();
		$id = filter_var($post['id'], FILTER_SANITIZE_STRING);
		$product_id = filter_var($post['product_id'], FILTER_SANITIZE_STRING);
		$price = filter_var($post['price'], FILTER_SANITIZE_STRING);
		$num = filter_var($post['num'], FILTER_SANITIZE_STRING);
		$cookie = $this->config['vendor']['crypto']['crypt']::decrypt($_COOKIE[$this->config['settings']['session']['name']], $cookie_key);
		
		$callbackStatus = 400;
		$callbackTitle = 'Соообщение системы';
		$callbackText = '';
		
		if ($this->session->authorize == 1) {
			$user_id = $this->session->user_id;
		} else {
			$user_id = 0;
		}

		// Ресурс (таблица) к которому обращаемся
		$resource = "cart";
		// Отдаем роутеру RouterDb конфигурацию
		$routerDb = new RouterDb($this->config, 'APIS');
		// Пингуем для ресурса указанную и доступную базу данных
		// Подключаемся к БД через выбранный Adapter: Sql, Pdo или Apis (По умолчанию Pdo)
		$db = $routerDb->run($routerDb->ping($resource));
		// Массив для запроса
		$query = [
		    'user_id' => $user_id,
		    'cookie' => $cookie,
		    'product_id' => $product_id,
		    'num' => $num,
		    'price' => $price,
		    'currency_id' => $this->config['seller']['currency_id'],
		    'order_id' => null,
		    'status_id' => 1,
		    'state' => 1
		];
		// Отправляем запрос к БД в формате адаптера. В этом случае Apis
		$responseArr = $db->post($resource, $query);

		if ($responseArr >= 1) {
			$callbackStatus = 200;
			$callbackTitle = $language["23"];
			$callbackText = $language["126"]." ".$language["124"]."<br>".$language["194"]." ".$price;
		} else {
			$callbackText = 'Действие заблокировано';
		}
		
		$callback = ['status' => $callbackStatus, 'title' => $callbackTitle, 'text' => $callbackText];
		// Выводим заголовки
		$response->withStatus(200);
		$response->withHeader('Content-type', 'application/json');
		// Выводим json
		echo json_encode($callback);

	}
	
	public function post_new_order(Request $request, Response $response)
	{
		$language = $this->languages->get($request);
		// Читаем ключи
		$session_key = $this->config['key']['session'];
		$cookie_key = $this->config['key']['cookie'];
		// Разбираем post
		$post = $request->getParsedBody();
		// Подключаем систему безопасности
		$security = new Security($this->config);
		
		try {
			// Получаем токен из сессии
			$token = $this->config['vendor']['crypto']['crypt']::decrypt($this->session->token, $token_key);
		} catch (\Exception $ex) {
			$token = 0;
			// Сообщение об Атаке или подборе токена
			$security->token($request, $response);
		}
		try {
			// Получаем токен из POST
			$post_csrf = $this->config['vendor']['crypto']['crypt']::decrypt(filter_var($post['csrf'], FILTER_SANITIZE_STRING), $token_key);
			// Чистим данные на всякий случай пришедшие через POST
			$csrf = $utility->clean($post_csrf);
		} catch (\Exception $ex) {
			$csrf = 1;
			// Сообщение об Атаке или подборе csrf
			$security->csrf($request, $response);
		}
		
		$id = filter_var($post['id'], FILTER_SANITIZE_STRING);
		$iname = filter_var($post['iname'], FILTER_SANITIZE_STRING);
		$fname = filter_var($post['fname'], FILTER_SANITIZE_STRING);
		$phone = filter_var($post['phone'], FILTER_SANITIZE_STRING);
		$email = filter_var($post['email'], FILTER_SANITIZE_STRING);
		$city_name = filter_var($post['city_name'], FILTER_SANITIZE_STRING);
		$street = filter_var($post['street'], FILTER_SANITIZE_STRING);
		$build = filter_var($post['build'], FILTER_SANITIZE_STRING);
		$apart = filter_var($post['apart'], FILTER_SANITIZE_STRING);
		$product_id = filter_var($post['product_id'], FILTER_SANITIZE_STRING);
		$price = filter_var($post['price'], FILTER_SANITIZE_STRING);
		$num = filter_var($post['num'], FILTER_SANITIZE_STRING);
		$description = filter_var($post['description'], FILTER_SANITIZE_STRING);
		$cookie = $this->config['vendor']['crypto']['crypt']::decrypt($_COOKIE[$this->config['settings']['session']['name']], $cookie_key);
		
		if ($this->session->authorize == 1) {
			$user_id = $this->config['vendor']['crypto']['crypt']::decrypt($this->session->user_id, $session_key);
		} else {
            // Ресурс (таблица) к которому обращаемся
            $resource = "user";
            // Отдаем роутеру RouterDb конфигурацию
            $routerDb = new RouterDb($this->config, 'APIS');
            // Пингуем для ресурса указанную и доступную базу данных
            // Подключаемся к БД через выбранный Adapter: Sql, Pdo или Apis (По умолчанию Pdo)
            $db = $routerDb->run($routerDb->ping($resource));
            // Массив для запроса
            $query = [
				"site_id" => 1,
				"cookie" => $cookie,
				"iname" => $iname,
				"fname" => $fname,
				"phone" => $phone,
				"email" => $email,
				"password" => ""
            ];
            // Отправляем запрос к БД в формате адаптера. В этом случае Apis
            $user = $db->post($resource, $query);

			if (isset($user['response']['id'])) {
				$this->session->user_id = $this->config['vendor']['crypto']['crypt']::encrypt($user['response']['id'], $session_key);
				$user_id = $user['response']['id'];
			}
		}
		
		$callbackStatus = 400;
		$callbackTitle = 'Соообщение системы';
		$callbackText = '';
		
		if ($user_id >= 1) {
			
			$addressArr = [
				"table_name" => "user",
				"user_id" => $user_id,
				"city_id" => 1,
				"street_id" => 2,
				"number" => $build,
				"apartment" => $apart
			];
			
			// Ресурс (таблица) к которому обращаемся
			$resource = "address";
			// Отдаем роутеру RouterDb конфигурацию.
			$router = new Router($this->config);
			// Получаем название базы для указанного ресурса
			$name_db = $router->ping($resource);
			// Подключаемся к базе
			$db = new Db($name_db, $this->config);
			// Отправляем запрос и получаем данные
			$address = $db->post($resource, $addressArr);
			
			if ($address >= 1) {
				
				$orderArr = [
					"site_id" => 1,
					"order_type" => 1,
					"user_id" => $user_id,
					"status_id" => 1,
					"delivery_id" => 1,
					"address_id" => $address,
					"note" => $description
				];
				
				// Ресурс (таблица) к которому обращаемся
				$resource = "order";
				// Отдаем роутеру RouterDb конфигурацию.
				$router = new Router($this->config);
				// Получаем название базы для указанного ресурса
				$name_db = $router->ping($resource);
				// Подключаемся к базе
				$db = new Db($name_db, $this->config);
				// Отправляем запрос и получаем данные
				$order = $db->post($resource, $orderArr);
				
				if ($order >= 1) {
					
					$cartArr = [
					    'user_id' => $user_id,
					    'cookie' => $cookie,
					    'product_id' => $product_id,
					    'order_id' => $order,
					    'num' => $num,
					    'price' => $price,
					    'currency_id' => $this->config['settings']['site']['currency_id'],
					    'status_id' => 1,
					    'state' => 1
					];
					
					// Ресурс (таблица) к которому обращаемся
					$resource = "cart";
					// Отдаем роутеру RouterDb конфигурацию.
					$router = new Router($this->config);
					// Получаем название базы для указанного ресурса
					$name_db = $router->ping($resource);
					// Подключаемся к базе
					$db = new Db($name_db, $this->config);
					// Отправляем запрос и получаем данные
					$cart = $db->post($resource, $cartArr);
					
					if ($cart >= 1) {
						$callbackStatus = 200;
						$callbackTitle = 'Спасибо за заказ';
						$callbackText = '<div class="text-center">Копию заказа мы отправили вам на почту.</div>';
					}
				} else {
					$callbackText = 'Ошибка !';
				}
			} else {
				$callbackText = 'Ошибка !';
			}
		} else {
			$callbackText = 'Ошибка !';
		}

		$callback = ['status' => $callbackStatus, 'title' => $callbackTitle, 'text' => $callbackText];
		// Выводим заголовки
		$response->withStatus(200);
		$response->withHeader('Content-type', 'application/json');
		// Выводим json
		echo json_encode($callback);

	}

}
 