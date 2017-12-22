<?php
/**
 * This file is part of the API SHOP
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/pllano/api-shop
 * @version 1.0.1
 * @package pllano.api-shop
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
namespace ApiShop\Utilities;

use Imagine\Image\Box;
use Imagine\Image\Point;

// Здесь собраны основные полезные функции
class Utility {

    // Функция для проверки длинны строки
    public function check_length($value = "", $min, $max) {
        $result = (mb_strlen($value) < $min || mb_strlen($value) > $max);
        return !$result;
    }

    public function utf8_urldecode($str) {
        $str = preg_replace("/%u([0-9a-f]{3,4})/i","&#x\\1;",urldecode($str));
        return html_entity_decode($str,null,'UTF-8');
    }

    // Функция клинер. Усиленная замена htmlspecialchars
    public function clean($value = "") {
        // Убираем пробелы вначале и в конце
        $value = trim($value);
        // Убираем слеши, если надо
        // Удаляет экранирование символов
        $value = stripslashes($value);
        // Удаляет HTML и PHP-теги из строки
        $value = strip_tags($value);
        // Заменяем служебные символы HTML на эквиваленты
        // Преобразует специальные символы в HTML-сущности
        $value = htmlspecialchars($value, ENT_QUOTES);

        return $value;

    }

    public function phone_clean($value = "") {

        // Убираем пробелы вначале и в конце
        $value = trim($value);
        // чистим всякие украшательства в номере телефона
        // в результате должны получить просто числовое значение номера
        $value = str_replace("+", "", $value);
        $value = str_replace("(", "", $value);
        $value = str_replace(")", "", $value);
        $value = str_replace("-", "", $value);
        $value = str_replace(" ", "", $value);
        // Убираем слеши, если надо
        // Удаляет экранирование символов
        $value = stripslashes($value);
        // Удаляет HTML и PHP-теги из строки
        $value = strip_tags($value);
 
        return $value;
    }

    // Функция очистки для xml
    public function clean_xml($value = "") {

        $value = str_replace("&", "&amp;", $value);
        $value = str_replace("<", "&lt;", $value);
        $value = str_replace(">", "&gt;", $value);
        $value = str_replace("{", "&#123;", $value);
        $value = str_replace("}", "&#125;", $value);
        $value = str_replace('"', '&quot;', $value);
        $value = str_replace("'", "&apos;", $value);
        // Убираем пробелы вначале и в конце
        $value = trim($value);
        // Убираем слеши, если надо
        // Удаляет экранирование символов
        $value = stripslashes($value);
        // Удаляет HTML и PHP-теги из строки
        $value = strip_tags($value);
        // Заменяем служебные символы HTML на эквиваленты
        // Преобразует специальные символы в HTML-сущности
        $value = htmlspecialchars($value, ENT_QUOTES);

        return $value;
 
    }

    // Функция контроля изображений если есть отдает локальный url, если нет загружает с платформы
    public function get_image($product_id = null, $image_url, $width, $height)
    {

        $dir_size = $width.'x'.$height;
        $subdir = 'default';
        if ($product_id >= 1) {$subdir = ($product_id - ($product_id % 1000)) / 1000;}
        if (file_exists('images/')) {} else {mkdir('images/');}
        if (file_exists('images/'.$dir_size.'/')) {} else {mkdir('images/'.$dir_size.'/');}
        if (file_exists('images/'.$dir_size.'/'.$subdir.'/')) {} else {mkdir('images/'.$dir_size.'/'.$subdir.'/');}
        if (file_exists('images/temp/')) {} else {mkdir('images/temp/');}

        $path_image = pathinfo($image_url);

        if (isset($path_image["extension"])) {
            // echo $path_image["extension"].'<br>';
                if ($path_image["extension"] == 'jpg' || 
                    $path_image["extension"] == 'png' || 
                    $path_image["extension"] == 'jpeg') {
                        $extension = $path_image["extension"];

                    if (isset($path_image["basename"])) {
                        $basename = $path_image["basename"];
                        if (!file_exists('images/'.$dir_size.'/'.$subdir.'/'.$basename)) {
                            $images_url = filter_var($image_url, FILTER_VALIDATE_URL);
                            if ($images_url == true) {

                                if (!file_exists('images/temp/'.$basename)) {
                                    file_put_contents('images/temp/'.$basename, file_get_contents($image_url));
                                }

                                if (file_exists('images/temp/'.$basename)) {

                                    $optimizerChain = Spatie\ImageOptimizer\OptimizerChainFactory::create();
                                    $optimizerChain->optimize('images/temp/'.$basename, 'images/temp/'.$basename);

                                    $imagine = new Imagine\Gd\Imagine();
                                    $size = new Imagine\Image\Box($width, $height);
                                    $mode = Imagine\Image\ImageInterface::THUMBNAIL_INSET;

                                    $imagine->open('images/temp/'.$basename)
                                        ->thumbnail($size, $mode)
                                        ->save('images/'.$dir_size.'/'.$subdir.'/'.$basename);

                                    unlink('images/temp/'.$basename);

                                    if (file_exists('images/'.$dir_size.'/'.$subdir.'/'.$basename)) {
                                        return 'images/'.$dir_size.'/'.$subdir.'/'.$basename;
                                    } else {
                                        return null;
                                    }
                                } else {
                                    return null;
                                }
 
                            }
                        } else {
                            return 'images/'.$dir_size.'/'.$subdir.'/'.$basename;
                        }

                    } else {
                        return false;
                    }

                } else {
                    return false;
                }
            }
 
    }

    // Функция генерации токена длиной 64 символа
    public function random_token($length = 32)
    {
        if(!isset($length) || intval($length) <= 8 ){
            $length = 32;
        }
        if (function_exists('random_bytes')) {
            return bin2hex(random_bytes($length));
        }
        if (function_exists('mcrypt_create_iv')) {
            return bin2hex(mcrypt_create_iv($length, MCRYPT_DEV_URANDOM));
        }
        if (function_exists('openssl_random_pseudo_bytes')) {
            return bin2hex(openssl_random_pseudo_bytes($length));
        }
    }

    // Функция генерации короткого токена длиной 12 символов
    public function random_alias_id($length = 6)
    {
        if(!isset($length) || intval($length) <= 5 ){
            $length = 6;
        }
        if (function_exists('random_bytes')) {
            return bin2hex(random_bytes($length));
        }
        if (function_exists('mcrypt_create_iv')) {
            return bin2hex(mcrypt_create_iv($length, MCRYPT_DEV_URANDOM));
        }
        if (function_exists('openssl_random_pseudo_bytes')) {
            return bin2hex(openssl_random_pseudo_bytes($length));
        }
    }

    // Функция генерации алиаса
    public function get_new_alias($str, $charset = 'UTF-8')
    {
        $str = mb_strtolower($str, $charset);
        $glyph_array = array(
            'a' => 'а',
            'b' => 'б',
            'v' => 'в',
            'g' => 'г,ґ',
            'd' => 'д',
            'e' => 'е,є,э',
            'jo' => 'ё',
            'zh' => 'ж',
            'z' => 'з',
            'i' => 'и,і',
            'ji' => 'ї',
            'j' => 'й',
            'k' => 'к',
            'l' => 'л',
            'm' => 'м',
            'n' => 'н',
            'o' => 'о',
            'p' => 'п',
            'r' => 'р',
            's' => 'с',
            't' => 'т',
            'u' => 'у',
            'f' => 'ф',
            'kh' => 'х',
            'ts' => 'ц',
            'ch' => 'ч',
            'sh' => 'ш',
            'shh' => 'щ',
            '' => 'ъ',
            'y' => 'ы',
            '' => 'ь',
            'ju' => 'ю',
            'ja' => 'я',
            '-' => ' ,_',
            'x' => '*'
        );

        foreach ($glyph_array as $letter => $glyphs)
        {
            $glyphs = explode(',', $glyphs);
            $str = str_replace($glyphs, $letter, $str);
        }

        $str = preg_replace('/[^A-Za-z0-9-]+/', '', $str);
        $str = preg_replace('/\s[\s]+/', '-', $str);
        $str = preg_replace('/_[_]+/', '-', $str);
        $str = preg_replace('/-[-]+/', '-', $str);
        $str = preg_replace('/[\s\W]+/', '-', $str);
        $str = preg_replace('/^[\-]+/', '', $str);
        $str = preg_replace('/[\-]+$/', '', $str);
        // Если нужно что бы url и алиасе вместо черточек были нижние подчеркивания
        //$str = preg_replace('/[^A-Za-z0-9-]+/', '', $str);
        //$str = preg_replace('/\s[\s]+/', '_', $str);
        //$str = preg_replace('/_[_]+/', '_', $str);
        //$str = preg_replace('/-[-]+/', '_', $str);
        //$str = preg_replace('/[\s\W]+/', '_', $str);
        //$str = preg_replace('/^[\-]+/', '', $str);
        //$str = preg_replace('/[\-]+$/', '', $str);
        return $str;
    }

    // Функция генерации алиаса
    public function get_alias($str, $charset = 'UTF-8')
    {
        $str = mb_strtolower($str, $charset);
        $glyph_array = array(
            'a' => 'а',
            'b' => 'б',
            'v' => 'в',
            'g' => 'г,ґ',
            'd' => 'д',
            'e' => 'е,є,э',
            'jo' => 'ё',
            'zh' => 'ж',
            'z' => 'з',
            'i' => 'и,і',
            'ji' => 'ї',
            'j' => 'й',
            'k' => 'к',
            'l' => 'л',
            'm' => 'м',
            'n' => 'н',
            'o' => 'о',
            'p' => 'п',
            'r' => 'р',
            's' => 'с',
            't' => 'т',
            'u' => 'у',
            'f' => 'ф',
            'kh' => 'х',
            'ts' => 'ц',
            'ch' => 'ч',
            'sh' => 'ш',
            'shh' => 'щ',
            '' => 'ъ',
            'y' => 'ы',
            '' => 'ь',
            'ju' => 'ю',
            'ja' => 'я',
            '-' => ' ,_',
            'x' => '*'
        );

        foreach ($glyph_array as $letter => $glyphs)
        {
            $glyphs = explode(',', $glyphs);
            $str = str_replace($glyphs, $letter, $str);
        }

        $str = preg_replace('/[^A-Za-z0-9-]+/', '', $str);
        $str = preg_replace('/\s[\s]+/', '-', $str);
        $str = preg_replace('/_[_]+/', '-', $str);
        $str = preg_replace('/-[-]+/', '-', $str);
        $str = preg_replace('/[\s\W]+/', '-', $str);
        $str = preg_replace('/^[\-]+/', '', $str);
        $str = preg_replace('/[\-]+$/', '', $str);

        return $str;
    }

    public function is_url($url) {
        return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
    }

    public function parse_url_if_valid($url)
    {
        // Массив с компонентами URL, сгенерированный функцией parse_url()
        $arUrl = parse_url($url);

        // Возвращаемое значение. По умолчанию будет считать наш URL некорректным.
        $ret = null;

        // Если не был указан протокол, или
        // указанный протокол некорректен для url
        if (!array_key_exists("scheme", $arUrl)
            || !in_array($arUrl["scheme"], array("http", "https")))

        // Задаем протокол по умолчанию - http
        $arUrl["scheme"] = "http";

        // Если функция parse_url смогла определить host
        if (array_key_exists("host", $arUrl) &&
            !empty($arUrl["host"]))

        // Собираем конечное значение url
        $ret = sprintf("%s://%s%s", $arUrl["scheme"],
        $arUrl["host"], $arUrl["path"]);

        // Если значение хоста не определено
        // (обычно так бывает, если не указан протокол),
        // Проверяем $arUrl["path"] на соответствие шаблона URL.
        else if (preg_match("/^\w+\.[\w\.]+(\/.*)?$/", $arUrl["path"]))

        // Собираем URL
        $ret = sprintf("%s://%s", $arUrl["scheme"], $arUrl["path"]);

        return $ret;
    }

    public function clean_number($value = "")
    {
        $value = preg_replace("/[^0-9]/", "", $value);
        return $value;
    }

    public function clean_percent($value = "")
    {
        $value = preg_replace("/[^0-9.]/", "", $value);
        return $value;
    }

    /**
    * Функция склонения слов
    *
    * @param mixed $digit
    * @param mixed $expr
    * @param bool $onlyword
    * @return
    */
    public function declension($digit,$expr,$onlyword=false)
    {
        if(!is_array($expr)) $expr = array_filter(explode(' ', $expr));
        if(empty($expr[2])) $expr[2]=$expr[1];
        $i=preg_replace('/[^0-9]+/s','',$digit)%100;
        if($onlyword) $digit='';
        if($i>=5 && $i<=20) $res=$digit.' '.$expr[2];
        else {
            $i%=10;
            if($i==1) $res=$digit.' '.$expr[0];
            elseif($i>=2 && $i<=4) $res=$digit.' '.$expr[1];
            else $res=$digit.' '.$expr[2];
        }
 
        return trim($res);
    }

    // вывод только числа и месяца
    public function rich_date($date, $full_month=false)
    {
        if(strlen($date) < 12) $date .= " 00:00:00";

        $month_ru_full = array("", "января", "февраля", "марта", "апреля", "мая", "июня", "июля", "августа", "сентября", "октября", "ноября", "декабря");
        $month_ru_short = array("", "янв.", "февр.", "мар.", "апр.", "мая", "июн.", "июл.", "авг.", "сент.", "окт.", "нояб.", "дек.");

        $month_ru = ($full_month) ? $month_ru_full : $month_ru_short;

        $month = (int) substr($date, 5, -12);
        $day = (int) substr($date, 8, -9);

        return ($day . "&nbsp;" . $month_ru[(int)$month]);
    }

    /**
    * Счетчик обратного отсчета
    *
    * @param mixed $date
    * @return
    */
    public function downcounter($date)
    {

        $check_time = time() - strtotime($date);

        if($check_time <= 0){

            return false;

        }

    }

}
 