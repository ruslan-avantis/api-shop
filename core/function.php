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
// Допускаются следующие приведения типов:
// (int), (integer) - приведение к integer
// (bool), (boolean) - приведение к boolean
// (float), (double), (real) - приведение к float
// (string) - приведение к string
// (array) - приведение к array
// (object) - приведение к object
// (unset) - приведение к NULL

declare(strict_types = 1);

/* ----------- CORE ---------- */

// Функция клинер. Усиленная замена filter_var
function sanitize($value = null)
{
    if (isset($value)) {
        $value = filter_var(clean($value), FILTER_SANITIZE_STRING);
    }
    return $value;
}

// Функция клинер. Усиленная замена htmlspecialchars
function clean($value = null)
{
    if (isset($value)) {
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
    }
    return $value;
}

/* ----------- $_COOKIE ---------- */

// set cookie
function set_cookie($session_name, $identificator, $period = 60*60*24*365)
{
    if (https() === true) {
        setcookie($session_name, $identificator, time() + $period, '/', domain(), true, true);
    } else {
        setcookie($session_name, $identificator, time() + $period, '/', domain());
    }
}
// clean cookie
function clean_cookie($session_name, $period = 60*60*24*365)
{
    if (https() === true) {
        setcookie($session_name, null, time() - $period, '/', domain(), true, true);
    } else {
        setcookie($session_name, null, time() - $period, '/', domain());
    }
}

// get cookie
function get_cookie($session_name)
{
    return $_COOKIE[$session_name] ?? null;
}

// Данные из COOKIE
function data_cookie_0($name, $type = 'str', $json_decode = false)
{
    if (isset($_COOKIE['cookie'])) {
        foreach ($_COOKIE['cookie'] as $name => $value) {
            $name = htmlspecialchars($name);
            $value = htmlspecialchars($value);
            //echo "$name : $value <br />\n";
        }
    }
}

// Данные из COOKIE
function data_cookie($name, $type = 'str', $json_decode = false)
{
    global $_COOKIE;
    $data = $json_decode ? json_decode($_COOKIE[$name]) : $_COOKIE[$name];
    if (!isset($data)) {
        return null;
    }
    if (is_array($data)) {
        $result = grd_array($data, $type);
        } elseif (is_object($data)) {
        $result = grd_object($data, $type);
        } else {
        $result = strip_tags(trim($data));
        if ($type == 'str') {
            $result = addslashes($result);
            } elseif ($type == 'int') {
            $result = intval($result);
        }
    }
    return $result;
}

/* ----------- htaccess ---------- */

function ban_htaccess($path, $ip, $mask = null)
{
    $ip_mask = $ip;
    if (isset($mask)) {
        $ip_mask = $ip.'/'.$mask;
    }
    file_put_contents($path.'/.htaccess', PHP_EOL . 'Deny from '.$ip_mask, FILE_APPEND | LOCK_EX);
}

/* ----------- config ---------- */

function routing_config($routing_settings_arr = []): array
{
    $routingConfig = [];
    if (isset($routing_settings_arr)) {
        foreach($routing_settings_arr as $key => $val)
        {
            if((int)$val == 1){
                $routingConfig[$key] = true;
                } elseif((int)$val == 0) {
                $routingConfig[$key] = false;
                } else {
                $routingConfig[$key] = $val;
            }
        }
    }
    return $routingConfig;
}

/* ----------- DATE ---------- */

function today() {
    return date("d-m-Y H:i:s");
}

function today_date() {
    return date("Y-m-d H:i:s");
}

function microtime_float()
{
    list($usec, $sec)=explode(" ", microtime());
    return ((float)$usec+(float)$sec);
}

function date_arr($date)
{
    $date = strtotime($date);
    $arr = [];
    $arr['y'] = date("Y", $date);
    $arr['m'] = date("m", $date);
    $arr['d'] = date("d", $date);
    $arr['h'] = date("H", $date);
    $arr['i'] = date("i", $date);
    $arr['s'] = date("s", $date);
    return $arr;
}

// date_rand_min(1000, 5000);
function date_rand_min($from = null, $up_to = null)
{
    if (isset($from) && isset($up_to)){
        $rand = rand($from, $up_to);
    } else {
        $rand = rand(1000, 5000);
    }
    $date = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s")." +".$rand." minutes"));
    return $date;
}

// вывод только числа и месяца
function rich_date($date, $full_month=false)
{
    if(strlen($date) < 12) $date .= " 00:00:00";
    $month_ru_full = ["", "января", "февраля", "марта", "апреля", "мая", "июня", "июля", "августа", "сентября", "октября", "ноября", "декабря"];
    $month_ru_short = ["", "янв.", "февр.", "мар.", "апр.", "мая", "июн.", "июл.", "авг.", "сент.", "окт.", "нояб.", "дек."];
    $month_ru = ($full_month) ? $month_ru_full : $month_ru_short;
    $month = (int) substr($date, 5, -12);
    $day = (int) substr($date, 8, -9);
    return ($day . "&nbsp;" . $month_ru[(int)$month]);
}


/* ----------- $_SERVER ---------- */

function domain()
{
    return ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false;
}

function https()
{
    if ($_SERVER['HTTPS'] != "on") {
        return false;
        } else {
        return true;
    }
}

function http_host()
{
    if (https() === true) {
        return 'https://' . $_SERVER['HTTP_HOST'];
        } else {
        return 'http://' . $_SERVER['HTTP_HOST'];
    }
}

//Получаем реальный IP
function get_ip()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
        $ip = $_SERVER['HTTP_X_REAL_IP'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

function escaped_url()
{
    $uri = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    return htmlspecialchars($uri, ENT_QUOTES, 'UTF-8');
}

function GetBasePath()
{
    return substr($_SERVER['SCRIPT_FILENAME'], 0, strlen($_SERVER['SCRIPT_FILENAME']) - strlen(strrchr($_SERVER['SCRIPT_FILENAME'], "\\")));
}

function GetURI(){
    $this_page = basename($_SERVER['REQUEST_URI']);
    if (strpos($this_page, "?") !== false) {
        $this_page = reset(explode("?", $this_page));
    }
    return $this_page;
}

//Текушая реальная директория
function get_real_dir()
{
    return substr($_SERVER["REAL_FILE_PATH"], 0, strrpos($_SERVER["REAL_FILE_PATH"], "/")+1);
}

function isAjax()
{
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function utf8_urldecode($str)
{
    $str = preg_replace("/%u([0-9a-f]{3,4})/i","&#x\\1;",urldecode($str));
    return html_entity_decode($str,null,'UTF-8');
}

/* ----------- $_REQUEST ---------- */

// Данные из REQUEST
function grd($name, $type = 'str') 
{
    global $_REQUEST;
    if (!isset($_REQUEST[$name])) return null;
    $result = null;
    if (is_array($_REQUEST[$name])) {
        $result = grd_array($_REQUEST[$name], $type);
    } else {
        $result = strip_tags(trim($_REQUEST[$name]));
        if ($type == 'str') {
            $result = addslashes($result);
        } elseif ($type == 'int') {
            $result = intval($result);
        }
    }
    return $result;
}

/* ----------- RANDOM ---------- */
// Функция генерации токена длиной 64 символа
function random_token($length = 32)
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
function random_alias_id($length = 6)
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

/* ----------- FILE ---------- */

// Загрузить архив по ссылке
// Распаковать в указанную директорию
function archive_load($link, $dir)
{
    $link = filter_var($link, FILTER_VALIDATE_URL);
    $pathinfo = pathinfo($link);
    if (isset($pathinfo["extension"]) && isset($pathinfo["basename"])) {
        $file = $dir.'/'.$pathinfo["basename"].'.'.$pathinfo["extension"];
    file_put_contents($file, file_get_contents($link));
    // Подключаем архиватор
    $zip = new \ZipArchive;
    $res = $zip->open($file);
        if ($res === TRUE) {
            $zip->extractTo($dir);
            $zip->close();
            unlink($file);
        }
    }
}

function archive_create($tmpdir, $uploaddir, $arFiles, $outfilename)
{
    if(extension_loaded('zip')) {
        $zip = new ZipArchive();
        $zip_name = $uploaddir.$outfilename.".zip";
        $zip->open($zip_name, ZIPARCHIVE::CREATE);
        if($zip->open($zip_name, ZIPARCHIVE::CREATE)!== true){
            $result['errors'] = "Error, ZIP creation failed at this time\n";
        }
        foreach($arFiles as $file)
        {
            $zip->addFile($tmpdir.$file, $file);
        }
        $zip->close();
        if(file_exists($zip_name)){                    
            return $zip_name;
        }                    
        } else {
        echo "You dont have ZIP extension";
    }
}

function dir_delete($dir)
{
    $files = array_diff(scandir($dir), ['.','..']);
    foreach ($files as $file) {
        (is_dir("$dir/$file")) ? $this->delete("$dir/$file") : unlink("$dir/$file");
    }
    return rmdir($dir);
}

// Загрузка и преобразование в массив файла
function get_json_decode($json)
{
    if (file_exists($json)) {
        return json_decode(file_get_contents($json), true);
    } else {
        return null;
    }
}

// Получаем файл
function get_file($file)
{
    return file_get_contents($file);
}

// Сохранение контента в файл
function save_file($file, $content)
{
    return (file_put_contents($file, stripslashes($content)));
}

// Загружает файлы, вернет массив, имена и реальный путь файлов
function upload_files($uploaddir, $filename)
{
    foreach($_FILES[$filename]['error'] as $k=>$v)
    {
        $uploadfile = $uploaddir. basename($_FILES['FILES']['name'][$k]);                
        if(move_uploaded_file($_FILES[$filename]['tmp_name'][$k], $uploadfile)) {
            $arFiles[]= $_FILES[$filename]['name'][$k];
        }
    }
    return $arFiles;
}

/* Protection against SQL injections */
// Very simple function
function search_injections(string $value = null, array $add_keywords = [], array $new_keywords = []): int
{
    $list_keywords = [];
    if (isset($value)) {
        if (isset($new_keywords)) {
            $list_keywords = $new_keywords;
            } else {
            $plus_keywords = [];
            if (isset($add_keywords)) {
                $plus_keywords = $add_keywords;
            }
            $list_keywords = [
            '*', 
            'SELECT', 
            'UPDATE', 
            'DELETE', 
            'INSERT', 
            'INTO', 
            'VALUES', 
            'FROM', 
            'LEFT', 
            'JOIN', 
            'WHERE', 
            'LIMIT', 
            'ORDER BY', 
            'AND', 
            'OR ',
            'DESC', 
            'ASC', 
            'ON',
            'LOAD_FILE', 
            'GROUP',
            'BY',
            'foreach',
            'echo',
            'script',
            'javascript',
            'public',
            'function',
            'admin',
            'root',
            'push',
            '"false"',
            '"true"',
            'return',
            'onclick'
            ];
            $keywords = array_replace_recursive($list_keywords, $plus_keywords);
        }
        $value = str_ireplace($keywords, "👌", $value, $i);
        return $i;
        } else {
        return 0;
    }
}

/* ----------- CLEANER ---------- */

function clean_json($json = null)
{
    for ($i = 0; $i <= 31; ++$i) {
        $json = str_replace(chr($i), "", $json);
    }
    $json = str_replace(chr(127), "", $json);
    if (0 === strpos(bin2hex($json), "efbbbf")) {
        $json = substr($json, 3);
    }
    return $json;
}

// Функция клинер. Усиленная замена htmlspecialchars
function cleanText($value = "")
{
    $value = trim($value);
    $value = htmlspecialchars($value, ENT_QUOTES);
    return $value; 
}

function clean_number($value = "")
{
    $value = preg_replace("/[^0-9]/", "", $value);
    return $value;
}

function clean_percent($value = "")
{
    $value = preg_replace("/[^0-9.]/", "", $value);
    return $value;
}

function clean_phone($value = "")
{
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
function clean_xml($value = "")
{
    $value = str_replace("&", "&amp;", $value);
    $value = str_replace("<", "&lt;", $value);
    $value = str_replace(">", "&gt;", $value);
    $value = str_replace("{", "&#123;", $value);
    $value = str_replace("}", "&#125;", $value);
    $value = str_replace('"', '&quot;', $value);
    $value = str_replace("'", "&apos;", $value);
    $value = clean($value);
    return $value;
    
}

/* ----------- VALIDATION ---------- */

function check_phone($phone)
{
    if(check_length(sanitize($phone), 8, 25) === true) {
        $pattern = "/^[\+0-9\-\(\)\s]*$/";
        $phone = preg_match($pattern, $phone);
        return $phone;
    }
}

// Функция для проверки длинны строки
function check_length($value = "", $min, $max)
{
    $result = (mb_strlen($value) < $min || mb_strlen($value) > $max);
    return !$result;
}

//Проверка электронного адреса на PHP
function valid_email($email)
{
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
        } else {
        return true;
    }
}

function validate_email($email = null)
{
    if (isset($email)) {
        $email = filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    return $email;
}

//Проверка электронного адреса на PHP
function check_mail($email)
{
    //$email = "phil.taylor@a_domain.tv";
    if (preg_match("/^[^@]*@[^@]*\.[^@]*$/", $email)) {
        return true;
    }
}

function is_url($url)
{
    return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
}

function is_valid_phone($phone)
{
    return preg_match("/^(?:\+?[7,8][-. ]?)?\(?([0-9]{3})\)?[-. ]?([0-9]{3})[-. ]?([0-9]{2})[-. ]?([0-9]{2})$/", $phone);
}

/* ----------- PARSERS ---------- */

function parse_url_if_valid($url)
{
    // Массив с компонентами URL, сгенерированный функцией parse_url()
    $arUrl = parse_url($url);
    // Возвращаемое значение. По умолчанию будет считать наш URL некорректным.
    $ret = null;
    // Если не был указан протокол, или
    // указанный протокол некорректен для url
    if (!array_key_exists("scheme", $arUrl)
    || !in_array($arUrl["scheme"], ["http", "https"]))
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

/* ----------- TRANSLIT ALIAS ---------- */

// Функция генерации алиаса
function get_new_alias($str, $charset = 'UTF-8')
{
    $str = mb_strtolower($str, $charset);
    $glyph_array = [
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
    ];
    
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
function get_alias($str, $charset = 'UTF-8')
{
    $str = mb_strtolower($str, $charset);
    $glyph_array = [
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
    ];
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

/**
    * Transliteration function
    * Функция транслитерации текста
    * @param string $text
    * @param string $direct
    * @return string
*/
// Use 
// translateIt($text, $direct = 'ru_en');
function translateIt($text, $direct = 'ru_en')
{
    $arr['ru'] = [
    'Ё', 'Ж', 'Ц', 'Ч', 'Щ', 'Ш', 'Ы', 'Э', 'Ю', 'Я', 'ё', 'ж', 'ц', 'ч',
    'ш', 'щ', 'ы', 'э', 'ю', 'я', 'А', 'Б', 'В', 'Г', 'Д', 'Е', 'З', 'И',
    'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ъ',
    'Ь', 'а', 'б', 'в', 'г', 'д', 'е', 'з', 'и', 'й', 'к', 'л', 'м', 'н',
    'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ъ', 'ь'
    ];
    $arr['en'] = [
    "YO", "ZH",  "CZ", "CH", "SHH","SH", "Y'", "E'", "YU",  "YA", "yo", "zh", "cz", "ch",
    "sh", "shh", "y'", "e'", "yu", "ya", "A", "B" , "V" ,  "G",  "D",  "E",  "Z",  "I",
    "J",  "K",   "L",  "M",  "N",  "O",  "P", "R",  "S",   "T",  "U",  "F",  "X",  "''",
    "'",  "a",   "b",  "v",  "g",  "d",  "e", "z",  "i",   "j",  "k",  "l",  "m",  "n",
    "o",  "p",   "r",  "s",  "t",  "u",  "f", "x",  "''",  "'"
    ];
    
    // Конвертируем
    if($direct == 'en_ru') {
        $translated = str_replace($arr['en'], $arr['ru'], $text);
        // Теперь осталось проверить регистр мягкого и твердого знаков.
        $translated = preg_replace('/(?<=[а-яё])Ь/u', 'ь', $translated);
        $translated = preg_replace('/(?<=[а-яё])Ъ/u', 'ъ', $translated);
        } else {
        // И наоборот
        $translated = str_replace($arr['ru'], $arr['en'], $text);
        // Заменяем пробел на нижнее подчеркивание
        $translated = str_replace(' ', '_', $translated);
    }
    // Возвращаем
    return $translated;
}

//Транслитерация с Латинского на Русский
function translit_rus($string)
{
    $converter = [
    'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 
    'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 
    'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 
    'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 
    'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch', 'ь' => "'", 'ы' => 'y', 
    'ъ' => "'", 'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
    'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 
    'Ё' => 'E', 'Ж' => 'Zh', 'З' => 'Z', 'И' => 'I', 'Й' => 'Y', 'К' => 'K', 
    'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R', 
    'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C', 
    'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sch', 'Ь' => "'", 'Ы' => 'Y', 
    'Ъ' => "'", 'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya'
    ];
    return strtr($string, $converter);
}

//Транслитерация с Латинского на Русский
function translit_to_rus($string)
{
    $table = [
    'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'YO', 
    'Ж' => 'ZH', 'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 
    'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 
    'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C', 'Ч' => 'CH', 'Ш' => 'SH', 'Щ' => 'CSH', 'Ь' => '', 
    'Ы' => 'Y', 'Ъ' => '', 'Э' => 'E', 'Ю' => 'YU', 'Я' => 'YA', 'а' => 'a', 'б' => 'b', 
    'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh', 'з' => 'z', 
    'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 
    'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h',
    'ц' => 'c', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'csh', 'ь' => '', 'ы' => 'y', 'ъ' => '', 
    'э' => 'e', 'ю' => 'yu', 'я' => 'ya'
    ];
    $output = str_replace(array_keys($table), array_values($table), $string);
    return $output;
}

/* ----------- FORMAT ---------- */

function format_size($size, $type = 'KB', $text = null)
{
    if ($type == 'bytes') {$metrics = ['bytes', 'KB', 'MB', 'GB', 'TB'];}
    if ($type == 'KB') {$metrics = ['KB', 'MB', 'GB', 'TB'];}
    if ($type == 'MB') {$metrics = ['MB', 'GB', 'TB'];}
    $metric = 0;
    while(floor($size/1024) > 0){
        ++$metric;
        $size /= 1024;
    }
    if ($text == null) {
        $ret = round($size,2);
        } else {
        $ret = round($size,2)." ".(isset($metrics[$metric])?$metrics[$metric]:'??');
    }
    return $ret;
}

/* ----------- EXEC ---------- */

function meminfo()
{
    @exec('cat /proc/meminfo', $meminfo);
    if (isset($meminfo['0'])) {
        //print_r($meminfo);
        $arr['MemTotal'] = format_size(str_replace(['MemTotal:', 'kB', ' '], '', $meminfo['0']));
        $arr['MemFree'] = format_size(str_replace(['MemFree:', 'kB', ' '], '', $meminfo['1']));
        $arr['MemAvailable'] = format_size(str_replace(['MemAvailable:', 'kB', ' '], '', $meminfo['2']));
        $arr['Buffers'] = format_size(str_replace(['Buffers:', 'kB', ' '], '', $meminfo['3']));
        $arr['Cached'] = format_size(str_replace(['Cached:', 'kB', ' '], '', $meminfo['4']));
        $arr['SwapTotal'] = format_size(str_replace(['SwapTotal:', 'kB', ' '], '', $meminfo['14']));
        $arr['SwapFree'] = format_size(str_replace(['SwapFree:', 'kB', ' '], '', $meminfo['15']));
        $arr['MemUsed'] = $arr['MemTotal'] - $arr['MemFree'];
        //print_r($arr);
        return $arr;
    } else {
        return null;
    }
}

function memory_free()
{
    $meminfo = meminfo();
    return round($meminfo['MemFree'] / ($meminfo['MemTotal'] / 100), 2);
}

function memory_used()
{
    $meminfo = meminfo();
    return round($meminfo['MemUsed'] / ($meminfo['MemTotal'] / 100), 2);
}

function cpuinfo()
{
    @exec('cat /proc/cpuinfo', $cpuinfo);
    if (isset($cpuinfo['0'])) {
        return $cpuinfo;
        } else {
        return null;
    }
}

function nproc()
{
    @exec('nproc', $nproc);
    @exec('cat /proc/cpuinfo | grep ^processor |wc -l', $cpuinfo);
    if (isset($nproc['0']) && isset($cpuinfo['0'])) {
        if ($nproc['0'] <= $cpuinfo['0']) {
            return $cpuinfo['0'];
            } else {
            return $nproc['0'];
        }
        } else {
        return null;
    }
}

/* ----------- WHOIS ---------- */

// Whois
function whois_query($domain)
{
    // fix the domain name:
    $domain = strtolower(trim($domain));
    $domain = preg_replace('/^http:\/\//i', '', $domain);
    $domain = preg_replace('/^www\./i', '', $domain);
    $domain = explode('/', $domain);
    $domain = trim($domain[0]);
    // split the TLD from domain name
    $_domain = explode('.', $domain);
    $lst = count($_domain)-1;
    $ext = $_domain[$lst];
    // You find resources and lists
    // like these on wikipedia:
    //
    // http://de.wikipedia.org/wiki/Whois
    //
    $servers = [
    "biz" => "whois.neulevel.biz",
    "com" => "whois.internic.net",
    "us" => "whois.nic.us",
    "coop" => "whois.nic.coop",
    "info" => "whois.nic.info",
    "name" => "whois.nic.name",
    "net" => "whois.internic.net",
    "gov" => "whois.nic.gov",
    "edu" => "whois.internic.net",
    "mil" => "rs.internic.net",
    "int" => "whois.iana.org",
    "ac" => "whois.nic.ac",
    "ae" => "whois.uaenic.ae",
    "at" => "whois.ripe.net",
    "au" => "whois.aunic.net",
    "be" => "whois.dns.be",
    "bg" => "whois.ripe.net",
    "br" => "whois.registro.br",
    "bz" => "whois.belizenic.bz",
    "ca" => "whois.cira.ca",
    "cc" => "whois.nic.cc",
    "ch" => "whois.nic.ch",
    "cl" => "whois.nic.cl",
    "cn" => "whois.cnnic.net.cn",
    "cz" => "whois.nic.cz",
    "de" => "whois.nic.de",
    "fr" => "whois.nic.fr",
    "hu" => "whois.nic.hu",
    "ie" => "whois.domainregistry.ie",
    "il" => "whois.isoc.org.il",
    "in" => "whois.ncst.ernet.in",
    "ir" => "whois.nic.ir",
    "mc" => "whois.ripe.net",
    "to" => "whois.tonic.to",
    "tv" => "whois.tv",
    "ru" => "whois.ripn.net",
    "org" => "whois.pir.org",
    "aero" => "whois.information.aero",
    "nl" => "whois.domain-registry.nl"
    ];
    if (!isset($servers[$ext])){
        die('Error: No matching nic server found!');
    }
    $nic_server = $servers[$ext];
    $output = '';
    // connect to whois server:
    if ($conn = fsockopen ($nic_server, 43)) {
        fputs($conn, $domain."\r\n");
        while(!feof($conn)) {
            $output .= fgets($conn,128);
        }
        fclose($conn);
    }
    else {die('Error: Could not connect to ' . $nic_server . '!');}
    return $output;
}

// Определения города
function detect_city($ip)
{
    $default = 'UNKNOWN';
    if (!is_string($ip) || strlen($ip) < 1 || $ip == '127.0.0.1' || $ip == 'localhost') {
        $ip = '8.8.8.8';
    }
    $curlopt_useragent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.2) Gecko/20100115 Firefox/3.6 (.NET CLR 3.5.30729)';
    $url = 'https://ipinfodb.com/ip_locator.php?ip='.urlencode($ip);
    $ch = curl_init();
    $curl_opt = [
    CURLOPT_FOLLOWLOCATION => 1,
    CURLOPT_HEADER => 0,
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_USERAGENT => $curlopt_useragent,
    CURLOPT_URL => $url,
    CURLOPT_TIMEOUT => 1,
    CURLOPT_REFERER => http_host(),
    ];
    curl_setopt_array($ch, $curl_opt);
    $content = curl_exec($ch);
    if (!is_null($curl_info)) {
        $curl_info = curl_getinfo($ch);
    }
    curl_close($ch);
    if ( preg_match('{<li>City : ([^<]*)</li>}i', $content, $regs) )  {
        $city = $regs[1];
    }
    if ( preg_match('{<li>State/Province : ([^<]*)</li>}i', $content, $regs) )  {
        $state = $regs[1];
    }
    if( $city!='' && $state!='' ) {
        $location = $city.', '.$state;
        return $location;
        } else {
        return $default;
    }
}

/* ----------- Other ---------- */

/**
    * Функция склонения слов
    *
    * @param mixed $digit
    * @param mixed $expr
    * @param bool $onlyword
    * @return
*/
function declension($digit,$expr,$onlyword=false)
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

/**
    * Счетчик обратного отсчета
    *
    * @param mixed $date
    * @return
*/
function downcounter($date)
{
    $check_time = time() - strtotime($date);
    if($check_time <= 0) {
        return false;
    }
}
 