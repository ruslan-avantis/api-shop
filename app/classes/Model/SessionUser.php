<?php

namespace ApiShop\Model;

use Adbar\Session;
use Defuse\Crypto\Crypto;
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
        // Читаем ключи
        $session_key = $config['key']['session'];
        // Получаем данные пользователя по cookie
        $data = (new User())->getUserCode();
        if ($data != null) {
            $session->user_id = Crypto::encrypt($data["items"]["item"]["0"]["user_id"], $session_key);
            $session->iname = Crypto::encrypt($data["items"]["item"]["0"]["iname"], $session_key);
            $session->fname = Crypto::encrypt($data["items"]["item"]["0"]["fname"], $session_key);
            $session->phone = Crypto::encrypt($data["items"]["item"]["0"]["phone"], $session_key);
            $session->email = Crypto::encrypt($data["items"]["item"]["0"]["email"], $session_key);
        }
    }

    // Получаем данные из session
    public function get() {
        $config = (new Settings())->get();
        // Подключаем сессию
        $session = new Session($config['settings']['session']['name']);
        // Читаем ключи
        $session_key = $config['key']['session'];
        if ($session->language) {
            // Формируем массив данных сессии который отдаем шаблонизатору
            $response = array();
            if ($session->language) {$response['language'] = Crypto::decrypt($session->language, $session_key);}
            if ($session->iname) {$response["iname"] = Crypto::decrypt($session->iname, $session_key);}
            if ($session->fname) {$response["fname"] = Crypto::decrypt($session->fname, $session_key);}
            if ($session->phone) {$response['phone'] = Crypto::decrypt($session->phone, $session_key);}
            if ($session->email) {$response['email'] = Crypto::decrypt($session->email, $session_key);}
            // Возвращаем массив с данными сессии пользователя
            return $response;
        }
    }

}
 