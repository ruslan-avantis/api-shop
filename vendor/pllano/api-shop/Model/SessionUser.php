<?php

namespace ApiShop\Model;

use Adbar\Session;
use Defuse\Crypto\Crypto;
use Sinergi\BrowserDetector\Language as Langs;
use ApiShop\Config\Settings;
use ApiShop\Resources\User;

class SessionUser {

    // Контролируем наличие всех необходимых данных
    // Если пользователь авторизовался или регестрировался на другом ресурсе
    public function checking()
    {
        $config = (new Settings())->get();
        // Подключаем сессию
        $session = new Session($config['settings']['session']['name']);
        if (!$session->user_id && !$session->iname && !$session->fname && !$session->phone && !$session->email) {
            // Читаем ключи
            $session_key = $config['key']['session'];
            // Получаем данные пользователя по cookie
			$data = (new User())->getUserCode();
            if ($data != null) {
			    $session->user_id = Crypto::encrypt($data["items"]["0"]["item"]["user_id"], $session_key);
                $session->iname = Crypto::encrypt($data["items"]["0"]["item"]["iname"], $session_key);
                $session->fname = Crypto::encrypt($data["items"]["0"]["item"]["fname"], $session_key);
                $session->phone = Crypto::encrypt($data["items"]["0"]["item"]["phone"], $session_key);
                $session->email = Crypto::encrypt($data["items"]["0"]["item"]["email"], $session_key);
            }
		}
    }
 
    // Получаем данные из session
    public function get() {
        $config = (new Settings())->get();
        // Подключаем сессию
        $session = new Session($config['settings']['session']['name']);
        $langs = new Langs();
        // Получаем массив данных из таблицы language на языке из $session->language
        if (isset($session->language)) {
            $lang = $session->language;
        } elseif ($langs->getLanguage()) {
            $lang = $langs->getLanguage();
        } else {
            $lang = "en";
        }
		if (isset($session->authorize)) {
        	if ($session->authorize == 1) {
				// Читаем ключи
        		$session_key = $config['key']['session'];
            	// Формируем массив данных сессии который отдаем шаблонизатору
            	$response = array();
				if ($lang) {$response['language'] = $lang;}
            	if (isset($session->iname)) {$response["iname"] = Crypto::decrypt($session->iname, $session_key);}
            	if (isset($session->fname)) {$response["fname"] = Crypto::decrypt($session->fname, $session_key);}
            	if (isset($session->phone)) {$response['phone'] = Crypto::decrypt($session->phone, $session_key);}
            	if (isset($session->email)) {$response['email'] = Crypto::decrypt($session->email, $session_key);}
            	// Возвращаем массив с данными сессии пользователя
            	return $response;
        	} else {
		    	$response = array();
				if (isset($session->language)) {$response['language'] = $session->language;}
				return $response;
			}
		} else {
		    	$response = array();
				if (isset($session->language)) {$response['language'] = $session->language;}
				return $response;
		}
    }

}
 