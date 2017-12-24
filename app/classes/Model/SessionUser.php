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
        // Получаем конфигурацию \ApiShop\Config\Settings
        $config = (new Settings())->get();
        // Подключаем сессию
        $session = new Session($config['settings']['session']['name']);
        // Читаем ключи
        $session_key = $config['key']['session'];

        $user = new User();
        // Получаем данные пользователя по печеньке
        $data = $user->getUserCode();

        if ($data != 0) {

            $session->site_id = Crypto::encrypt($config['settings']['config']['site_id'], $session_key);
            $session->user_id = Crypto::encrypt($data["0"]["user_id"], $session_key);
            $session->userdata_id = Crypto::encrypt($data["0"]["id"], $session_key);
            $session->iname = Crypto::encrypt($data["0"]["iname"], $session_key);
            $session->fname = Crypto::encrypt($data["0"]["fname"], $session_key);
            $session->phone = Crypto::encrypt($data["0"]["phone"], $session_key);
            $session->email = Crypto::encrypt($data["0"]["email"], $session_key);
            $session->credit_limit = Crypto::encrypt($data["0"]["credit_limit"], $session_key);
            $session->balans = Crypto::encrypt($data["0"]["balans"], $session_key);
            $session->bonus_code = Crypto::encrypt($data["0"]["bonus_code"], $session_key);
            $session->referral_code = Crypto::encrypt($data["0"]["referral_code"], $session_key);
            $session->country_id = Crypto::encrypt($data["0"]["country_id"], $session_key);
            $session->country_name = Crypto::encrypt($data["0"]["country_name"], $session_key);
            $session->city_id = Crypto::encrypt($data["0"]["city_id"], $session_key);
            $session->city_name = Crypto::encrypt($data["0"]["city_name"], $session_key);
            $session->region_id = Crypto::encrypt($data["0"]["region_id"], $session_key);
            $session->district = Crypto::encrypt($data["0"]["district"], $session_key);
            $session->district_id = Crypto::encrypt($data["0"]["district_id"], $session_key);
            $session->street = Crypto::encrypt($data["0"]["street"], $session_key);
            $session->street_id = Crypto::encrypt($data["0"]["street_id"], $session_key);
            $session->build = Crypto::encrypt($data["0"]["build"], $session_key);
            $session->parade = Crypto::encrypt($data["0"]["parade"], $session_key);
            $session->apart = Crypto::encrypt($data["0"]["apart"], $session_key);
            $session->payment_mode = Crypto::encrypt($data["0"]["payment_mode"], $session_key);
            $session->payment_method = Crypto::encrypt($data["0"]["payment_method"], $session_key);
        }
    }

    // Получаем данные поставщика или склада по id
    public function get() {
        // Получаем конфигурацию \ApiShop\Config\Settings
        $config = (new Settings())->get();
        // Подключаем сессию
        $session = new Session($config['settings']['session']['name']);
        // Читаем ключи
        $session_key = $config['key']['session'];

        if ($session->language) {
            // Формируем массив данных сессии который отдаем шаблонизатору
            // Нужно убрать все что не будет использоватся в шаблоне
            $response = array();
            if ($session->language) {$response['language'] = Crypto::decrypt($session->language, $session_key);}
            if ($session->site_id) {$response['site_id'] = Crypto::decrypt($session->site_id, $session_key);}
            if ($session->user_id) {$response["user_id"] = Crypto::decrypt($session->user_id, $session_key);}
            if ($session->userdata_id) {$response["userdata_id"] = Crypto::decrypt($session->userdata_id, $session_key);}
            if ($session->iname) {$response["iname"] = Crypto::decrypt($session->iname, $session_key);}
            if ($session->fname) {$response["fname"] = Crypto::decrypt($session->fname, $session_key);}
            if ($session->phone) {$response['phone'] = Crypto::decrypt($session->phone, $session_key);}
            if ($session->email) {$response['email'] = Crypto::decrypt($session->email, $session_key);}
            if ($session->credit_limit) {$response["credit_limit"] = Crypto::decrypt($session->credit_limit, $session_key);}
            if ($session->balans) {$response["balans"] = Crypto::decrypt($session->balans, $session_key);}
            if ($session->bonus_code) {$response["bonus_code"] = Crypto::decrypt($session->bonus_code, $session_key);}
            if ($session->referral_code) {$response["referral_code"] = Crypto::decrypt($session->referral_code, $session_key);}
            if ($session->country_id) {$response["country_id"] = Crypto::decrypt($session->country_id, $session_key);}
            if ($session->country_name) {$response["country_name"] = Crypto::decrypt($session->country_name, $session_key);}
            if ($session->city_id) {$response["city_id"] = Crypto::decrypt($session->city_id, $session_key);}
            if ($session->city_name) {$response["city_name"] = Crypto::decrypt($session->city_name, $session_key);}
            if ($session->region_id) {$response["region_id"] = Crypto::decrypt($session->region_id, $session_key);}
            if ($session->district) {$response["district"] = Crypto::decrypt($session->district, $session_key);}
            if ($session->district_id) {$response["district_id"] = Crypto::decrypt($session->district_id, $session_key);}
            if ($session->street) {$response["street"] = Crypto::decrypt($session->street, $session_key);}
            if ($session->street_id) {$response["street_id"] = Crypto::decrypt($session->street_id, $session_key);}
            if ($session->build) {$response["build"] = Crypto::decrypt($session->build, $session_key);}
            if ($session->parade) {$response["parade"] = Crypto::decrypt($session->parade, $session_key);}
            if ($session->apart) {$response["apart"] = Crypto::decrypt($session->apart, $session_key);}
            if ($session->payment_mode) {$response["payment_mode"] = Crypto::decrypt($session->payment_mode, $session_key);}
            if ($session->payment_method) {$response["payment_method"] = Crypto::decrypt($session->payment_method, $session_key);}
            // Возвращаем массив с данными сессии пользователя
            return $response;
        }
    }

}
 