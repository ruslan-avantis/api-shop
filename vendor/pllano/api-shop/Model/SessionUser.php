<?php
 
namespace ApiShop\Model;
 
use Defuse\Crypto\Crypto;
use Sinergi\BrowserDetector\Language as Langs;
use ApiShop\Config\Settings;
use Adbar\Session;
 
class SessionUser {
 
    // Получаем данные из session
    public function get() {
        $config = (new Settings())->get();
        // Подключаем сессию
        $session = new Session($config['settings']['session']['name']);
        // Определяем язык интерфейса пользователя
 
        $langs = new Langs();
        // Получаем массив данных из таблицы language на языке из $session->language
        $lang = $config["settings"]["language"];
        if (isset($session->language)) {
            $lang = $session->language;
        } elseif ($langs->getLanguage()) {
            $session->language = $langs->getLanguage();
            $lang = $session->language;
        } else {
            $session->language = $lang;
        }
 
        $response = array();
 
        if (isset($session->authorize)) {
            if ($session->authorize == 1) {
                // Читаем ключи
                $session_key = $config['key']['session'];
                // Формируем массив данных сессии который отдаем шаблонизатору
 
                $response['language'] = $lang;
                $response["authorize"] = $session->authorize;
 
                try {
 
                    if (isset($session->role_id)) {$response["role_id"] = $session->role_id;}
                    if (isset($session->user_id)) {$response["user_id"] = $session->user_id;}
                    if (isset($session->iname)) {$response["iname"] = Crypto::decrypt($session->iname, $session_key);}
                    if (isset($session->fname)) {$response["fname"] = Crypto::decrypt($session->fname, $session_key);}
                    if (isset($session->phone)) {$response['phone'] = Crypto::decrypt($session->phone, $session_key);}
                    if (isset($session->email)) {$response['email'] = Crypto::decrypt($session->email, $session_key);}
 
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
 