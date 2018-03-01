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
 
namespace Pllano\ApiShop\Models;
 
use Pllano\ApiShop\Utilities\Utility;
 
class SessionUser
{
    private $config;
 
    function __construct($config)
    {
        $this->config = $config;
    }
 
    // Получаем данные из session
    public function get()
    {
        // Подключаем сессию
        $session = new $this->config['vendor']['session']['session']($this->config['settings']['session']['name']);
        // Определяем язык интерфейса пользователя
 
        $utility = new Utility();
 
        $langs = new $this->config['vendor']['detector']['language']();
        // Получаем массив данных из таблицы language на языке из $session->language
        $lang = $this->config["settings"]["language"];
        if (isset($session->language)) {
            $lang = $session->language;
        } elseif ($langs->getLanguage()) {
            $session->language = $langs->getLanguage();
            $lang = $session->language;
        } else {
            $session->language = $lang;
        }
        if(!isset($session->post_id)) {
            $session->post_id = $utility->random_alias_id();
        }
        $response = [];
 
        if (isset($session->authorize)) {
            if ($session->authorize == 1) {
                // Читаем ключи
                $session_key = $this->config['key']['session'];
                // Формируем массив данных сессии который отдаем шаблонизатору
 
                $response['language'] = $lang;
                $response["authorize"] = $session->authorize;
 
                try {
 
                    if (isset($session->role_id)) {$response["role_id"] = $session->role_id;}
                    if (isset($session->user_id)) {$response["user_id"] = $session->user_id;}
                    if (isset($session->iname)) {$response["iname"] = $this->config['vendor']['crypto']['crypt']::decrypt($session->iname, $session_key);}
                    if (isset($session->fname)) {$response["fname"] = $this->config['vendor']['crypto']['crypt']::decrypt($session->fname, $session_key);}
                    if (isset($session->phone)) {$response['phone'] = $this->config['vendor']['crypto']['crypt']::decrypt($session->phone, $session_key);}
                    if (isset($session->email)) {$response['email'] = $this->config['vendor']['crypto']['crypt']::decrypt($session->email, $session_key);}
					
                } catch (\Exception $ex) {
 
                    // Если не можем расшифровать, чистим сессию
                    $session->clear();
 
                }
 
                // Возвращаем массив с данными сессии пользователя
                return $response;
 
            } else {
                $response['language'] = $lang;
                $response['authorize'] = $session->authorize;
                return $response;
            }
        } else {
                $response['language'] = $lang;
                $response['authorize'] = null;
                return $response;
        }
    }
 
}
 