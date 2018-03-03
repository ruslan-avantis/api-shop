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


// ----------------------------------------





function check_phone($phone)
{    
    if (strlen(($phone) > 0)) {
        $phone = str_replace(" ", "", $phone);
        $phone = str_replace("(", "", $phone);
        $phone = str_replace(")", "", $phone);
        $phone = str_replace("-", "", $phone);
        $phone = str_replace("_", "", $phone);
        $phone = str_replace("+7", "", $phone);
        if (strlen($phone) == 10) {
            return true;        
        } else {
            return false;
        }
    }    
}

function get_num_ending($number, $endingArray)
{
    $number = $number % 100;
    if ($number >= 11 && $number <= 19) {
        $ending = $endingArray[2];
    } else {
        $i = $number % 10;
        switch ($i) {
            case (1): $ending = $endingArray[0];
			break;
            case (2):
            case (3):
            case (4): $ending = $endingArray[1];
			break;
            default: $ending=$endingArray[2];
        }
    }
    return $ending;
}

function rus_date() 
{
    // Перевод
    $translate = [
    "am" => "дп", "pm" => "пп", "AM" => "ДП", "PM" => "ПП",
    "Monday" => "Понедельник", "Mon" => "Пн", 
	"Tuesday" => "Вторник", "Tue" => "Вт",
    "Wednesday" => "Среда", "Wed" => "Ср",
    "Thursday" => "Четверг", "Thu" => "Чт",
    "Friday" => "Пятница", "Fri" => "Пт",
    "Saturday" => "Суббота", "Sat" => "Сб",
    "Sunday" => "Воскресенье", "Sun" => "Вс",
    "January" => "Января", "Jan" => "Янв",
    "February" => "Февраля", "Feb" => "Фев",
    "March" => "Марта", "Mar" => "Мар",
    "April" => "Апреля", "Apr" => "Апр",
    "May" => "Мая", "May" => "Мая",
    "June" => "Июня", "Jun" => "Июн",
    "July" => "Июля", "Jul" => "Июл",
    "August" => "Августа", "Aug" => "Авг",
    "September" => "Сентября", "Sep" => "Сен",
    "October" => "Октября", "Oct" => "Окт",
    "November" => "Ноября", "Nov" => "Ноя",
    "December" => "Декабря", "Dec" => "Дек",
    "st" => "ое", "nd" => "ое", "rd" => "е", "th" => "ое"
    ];
    // если передали дату, то переводим ее
    if (func_num_args() > 1) {
        $timestamp = func_get_arg(1);
        return strtr(date(func_get_arg(0), $timestamp), $translate);
    } else {
        // иначе текущую дату
        return strtr(date(func_get_arg(0)), $translate);
    }
}






function cleanInput($input)
{ 
    $search = [
        '@<script[^>]*?>.*?</script>@si', // Удаляем javascript
        '@<;[\/\!]*?[^<>]*?>@si', // Удаляем HTML теги
        '@<style[^>]*?>.*?</style>@siU', // Удаляем теги style
        '@@' // Удаляем многострочные комментарии
    ];
    $output = preg_replace($search, '', $input);
    return $output;
}

function sanitize($input)
{
    if (is_array($input)) {
        foreach($input as $var=>$val)
		{
            $output[$var] = sanitize($val);
        }
    } else {
        if (get_magic_quotes_gpc()) {
            $input = stripslashes($input);
        }
        $input  = cleanInput($input);
        $output = mysql_real_escape_string($input);
    }
    return $output;
}


function grd_array($var, $type) {
    foreach ($var as $k => $v) {
        if (is_array($v)) $result[$k] = grd_array($v, $type);
        else if (is_object($v)) $result[$k] = grd_object($v, $type);
        else {
            $result[$k] = strip_tags(trim($v));
            if ($type == 'str') @$result[$k] = addslashes($v);
            elseif ($type == 'int') @$result[$k] = intval($v);
        }
    }
    return $result;
}


function grd_object($var, $type) {
    foreach (get_object_vars($var) as $k => $v) {
        if (is_array($v)) $var->$k = grd_array($v, $type);
        else if (is_object($v)) $var->$k = grd_object($v, $type);
        else {
            $var->$k = strip_tags(trim($v));
            if ($type == 'str') @$var->$k = addslashes($v);
            elseif ($type == 'int') @$var->$k = intval($v);
        }
    }
    return $var;
}


function right_date_format($datestr, $to_mysql = false, $return_delimeter = null) {
    if (!$datestr) return null;
    if (is_int($datestr)) $datestr = date('Y-m-d', $datestr);
    $delimeter = $to_mysql ? '-' : '.';
    $date = explode(($to_mysql ? '.' : '-'), $datestr);
    if ($return_delimeter) $delimeter = $return_delimeter;
    return $date[2].$delimeter.$date[1].$delimeter.$date[0];
}


function right_date($datestr, $time = null, $m_upper = false) {
    if (!$datestr) return null;
    if ($m_upper) $monthes = ['Января', 'Февраля', 'Марта', 'Апреля', 'Мая', 'Июня', 'Июля', 'Августа', 'Сентября', 'Октября', 'Ноября', 'Декабря'];
    else $monthes = ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];
    if (is_string($datestr)) {
        if (mb_strlen($datestr) > 10) {
            if ($time) $time = mb_substr($datestr, 11);
            else $datestr = mb_substr($datestr, 0, 10);
        }
        $date = explode('-', $datestr);
    } else {
        $date_string = date('Y-m-d H:i', $datestr);
        if ($time) $time = mb_substr($date_string, 11);
        else $date_string = mb_substr($date_string, 0, 10);
        $date = explode('-', $date_string);
    }
    return $date[2].' '.$monthes[intval($date[1])-1].' '.$date[0].($time ? ' '.$time : null);
}

// Форматирует дату
function format_date($date, $time = null, $type = null) { 
    $out_date = $out_time = null;
    if (strstr($date,':')) $out_time = ' '.substr($date, 11, 10);
    if ((int)substr($date, 8, 2)) $out_date .= substr($date,8,2).".";
    if ((int)substr($date, 5, 2)) $out_date .= substr($date,5,2).".";
    if ((int)substr($date, 0, 4)) $out_date .= substr($date,0,4);
    if ($type) {
        $out_date .= " ".substr($date, 11, 2).":";
        $out_date .= "".substr($date, 14, 2);
    }
    return $out_date.($time ? $out_time : '');
}

function time_format($time = null) 
{
    $labelTime = date('d.m.Y', $time);
    $arrM = [
        '01'=>'янв', 
        '02'=>'фев', 
        '03'=>'мар', 
        '04'=>'апр',  
        '05'=>'май', 
        '06'=>'июн', 
        '07'=>'июл', 
        '08'=>'авг',  
        '09'=>'сен', 
        '10'=>'окт', 
        '11'=>'ноя', 
        '12'=>'дек'
    ];
    if ($labelTime == date('d.m.Y')) { 
        return 'Сегодня в '.date('H:i', $time); 
        } elseif ($labelTime == (date('d') - 1).'.'.date('m.Y')) { 
        return 'Вчера в '.date('H:i', $time); 
        } else { 
        return date('d', $time).' '.$arrM[date('m', $time)].' '.date('Y', $time).' в '.date('H:i', $time); 
    } 
}

function create_slug($string)
{
    $string = strtolower($string);
    $slug = preg_replace('/[^a-z0-9-]+/', '-', $string);
    return $slug;
}

function generate_password($length = 20)
{
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789``-=~!@#$%^&*()_+,./<>?;:[]{}\|';
    $max = mb_strlen($chars) - 1;
    for ($i = 0; $i < $length; $i++) $str .= $chars[rand(0, $max)];    
    return $str;
}

// Дата по русски
// echo date_rus(time(), '%dayweek%, j %month% Y, G:i');
function date_rus($d, $format = 'j %month% Y', $offset = 0)
{
    $montharray = ['Января', 'Февраля', 'Марта', 'Апреля', 'Мая', 'Июня', 'Июля', 'Августа', 'Сентября', 'Октября', 'Ноября', 'Декабря'];
    $dayarray = ['Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье'];
    $d += 3600 * $offset;
    $sarray = ['/%month%/i', '/%dayweek%/i'];
    $rarray = [$montharray[date("m", $d)-1], $dayarray[date("N", $d)-1]];
    $format = preg_replace($sarray, $rarray, $format);
    return date($format, $d);
}

function strtoSafe(){
    $result = stripslashes($result); // удаляем слэши
    $result = str_replace('#39;', '', $result); // удаляем одинарные кавычки
    $result = str_replace('"', '', $result); // удаляем двойные кавычки
    $result = str_replace('&', '', $result); // удаляем амперсанд
    $result = preg_replace('/([?!:^~|@№$–=+*&%.,;\[\]<>()_—«»#\/]+)/', '', $result); // удаляем недоспустимые символы
    $result = trim($result); // удаляем пробелы по бокам
    $result = preg_replace('/ +/', '-', $result); // пробелы заменяем на минусы
    $result = preg_replace('/-+/', '-', $result); // удаляем лишние минусы
    $result = preg_replace('/([-]*)(.+)([-]*)/', '\\2', $result); // удаляем лишние минусы
}

//Проверка Email на правильность на PHP
function emailValid($string){ 
    if (preg_match ("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+\.[A-Za-z]{2,6}$/", $string)) 
    return true; 
}

//Обрезаем текст правильно
function cropStr($str, $size){ 
    return mb_substr($str,0,mb_strrpos(mb_substr($str,0,$size,'utf-8'),' ',utf-8),'utf-8');
}

function getWord($number, $suffix) {
    $keys = array(2, 0, 1, 1, 1, 2);
    $modern = $number%100;
    $suffix_key = ($modern > 7 && $modern < 20) ?2:
    $keys[min($modern%10, 5)];
    return $suffix[$suffix_key];
}

$arraymin = ["минута", "минуты", "минут"];
//создали массив для минут
$arrayhour = ["час", "часа", "часов"];
//создали массив для часов
$datemin = date('i');
$datehour = date('H');
//создали переменное время: часы и минуты раздельно, для удобства
$hour = getWord($datehour, $arrayhour);
$min = getWord($datemin, $arraymin);
//ну и, собственно, сам вывод
echo "".$datehour." ".$hour." ".$datemin." ".$min."";
//в результате получаем: 14 часов 16 минут

//Нормализуем и делаем текст безопасным для вставки в базу
function ProcessText($text)
{
    $text = trim($text); // удаляем пробелы по бокам
    $text = stripslashes($text); // удаляем слэши
    $text = htmlspecialchars($text); // переводим HTML в текст
    $text = preg_replace("/ +/", " ", $text); // множественные пробелы заменяем на одинарные
    $text = preg_replace("/(\r\n){3,}/", "\r\n\r\n", $text); // убираем лишние переводы строк (больше 1 строки)
    $test = nl2br ($text); // заменяем переводы строк на тег
    $text = preg_replace("/^\"([^\"]+[^=>&lt;])\"/u", "$1«$2»", $text); // ставим людские кавычки
    $text = preg_replace("/(«){2,}/","«",$text); // убираем лишние левые кавычки (больше 1 кавычки)
    $text = preg_replace("/(»){2,}/","»",$text); // убираем лишние правые кавычки (больше 1 кавычки)      
    $text = preg_replace("/(\r\n){2,}/u", "<br><br>", $text); // ставим абзацы
    return $text; //возвращаем переменную
}



//Удаляем лишние из входных данных
function clearspecchars($str)
{
    $str = str_replace(',','',$str);
    $str = str_replace('\'','',$str);
    $str = str_replace('\\','',$str);
    $str = str_replace('/','',$str);
    $str = stripslashes(trim($str));
    $str = htmlspecialchars($str);
    return $str;
}

//Проверка номера телефона на PHP
function check_phone()
{
    preg_match_all("/\(?  (\d{3})?  \)?  (?(1)  [\-\s] ) \d{3}-\d{4}/x",
    "Call 555-1212 or 1-800-555-1212", $phones);
}

//Проверка номера телефона на PHP
function checkPhone($number)
{
    if(preg_match('^[0-9]{3}+-[0-9]{3}+-[0-9]{4}^', $number)) {
        return $number;
    } else {
        $items = ['/\ /', '/\+/', '/\-/', '/\./', '/\,/', '/\(/', '/\)/', '/[a-zA-Z]/'];
        $clean = preg_replace($items, '', $number);
        return substr($clean, 0, 3).'-'.substr($clean, 3, 3).'-'.substr($clean, 6, 4);
    }
}

//Делаем текст безопасным для вставки в базу новости, статьи и т.д.
function filter_secure_text($text)
{
    // Фильтрация опасных слов
    if (!preg_match("/script|http|<|>|SELECT|UNION|UPDATE|exe|exec|INSERT|tmp/i",$text))
    {
        die("ne dopustimie slova");
    }
}


function check_host_ip()
{
    $ip = "yandex.ru";
    if(preg_match('/(\d+).(\d+).(\d+).(\d+)/',$ip))
    $host = gethostbyaddr($ip);
    else
    $host = gethostbyname($ip);
    echo $host;
}


function link_clikabel()
{
    $stringa = " bla bla bla http://www.example.com bla bla http://www.example.net bla bla bla";
    $m = preg_match_all('/http:\/\/[a-z0-9A-Z.]+(?(?=[\/])(.*))/', $stringa, $match);
    if ($m) {
        $links=$match[0];
        for ($j=0;$j<$m;$j++) {
            $stringa=str_replace($links[$j],''.$links[$j].'',$stringa);
        }
    }
}



function check_abcnum($text)
{
    // Проверяем все символы на буквы и цифры
    /// /[^(\w)|(\x7F-\xFF-)|(\s)]/ английские , русские буквы и цифры
    if(!( preg_match("/^([a-z0-9]*)$/i", $text))) {
        die("veli nepravilnie simvloi");
    }
}

function isfile($file)
{
    return preg_match('/^[^.^:^?^\-][^:^?]*\.(?i)' . getexts() . '$/',$file);
    //first character cannot be . : ? - subsequent characters can't be a : ?
    //then a . character and must end with one of your extentions
    //getexts() can be replaced with your extentions pattern
}

function getexts()
{
    //list acceptable file extensions here
    return '(app|avi|doc|docx|exe|ico|mid|midi|mov|mp3|mpg|mpeg|pdf|psd|qt|ra|ram|rm|rtf|txt|wav|word|xls)';
}

//Проверка правильность IP адреса
function valid_ip($ip)
{
    return preg_match("/^([1-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])"."(\.([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3}$/", $ip);
}

function fixtags($text){
    $text = htmlspecialchars($text);
    $text = preg_replace("/=/", "=\"\"", $text);
    $text = preg_replace("/"/", ""\"", $text);
    $tags = "/<(\/|)(\w*)(\ |)(\w*)([\\\=]*)(?|(\")\""\"|)(?|(.*)?"(\")|)([\ ]?)(\/|)>/i";
    $replacement = "<$1$2$3$4$5$6$7$8$9$10>";
    $text = preg_replace($tags, $replacement, $text);
    $text = preg_replace("/=\"\"/", "=", $text);
    return $text;
}

//Очищаем входные данные
function clean_chars($string)
{
    // Remove all remaining other unknown characters
    $string = preg_replace('/[^a-zA-Z0-9\-]/', ' ', $string);
    $string = preg_replace('/^[\-]+/', '', $string);
    $string = preg_replace('/[\-]+$/', '', $string);
    $string = preg_replace('/[\-]{2,}/', ' ', $string);
    return $string;
}

//Удаление файлов на PHP
function delete_dir_files_ex($path)
{
    if(strlen($path) == 0 || $path == '/') {
        return false;
    }
    $full_path = $_SERVER["DOCUMENT_ROOT"]."/".$path;
    $full_path = preg_replace("#[\\\\\\/]+#", "/", $full_path);
    $f = true;
    if(is_file($full_path) || is_link($full_path))
    {
        if(@unlink($full_path))
        return true;
        return false;
    }
    elseif(is_dir($full_path))
    {
        if($handle = opendir($full_path))
        {
            while(($file = readdir($handle)) !== false)
            {
                if($file == "." || $file == "..")
                continue;
                
                if(!DeleteDirFilesEx($path."/".$file))
                $f = false;
            }
            closedir($handle);
        }
        if(!@rmdir($full_path))
        return false;
        return $f;
    }
    return false;
}

//Удаление файлов на PHP
function delete_dir_files($frDir, $toDir, $arExept = [])
{
    if(is_dir($frDir))
    {
        $d = dir($frDir);
        while ($entry = $d->read())
        {
            if ($entry=="." || $entry=="..")
            continue;
            if (in_array($entry, $arExept))
            continue;
            @unlink($toDir."/".$entry);
        }
        $d->close();
    }
}

//Получение тип файла на PHP
function get_file_type($path)
{
    $extension = get_file_extension(strtolower($path));
    switch ($extension)
    {
        case "jpg": case "jpeg": case "gif": case "bmp": case "png":
        $type = "IMAGE";
        break;
        case "swf":
        $type = "FLASH";
        break;
        case "html": case "htm": case "asp": case "aspx":
        case "phtml": case "php": case "php3": case "php4": case "php5": case "php6":
        case "shtml": case "sql": case "txt": case "inc": case "js": case "vbs":
        case "tpl": case "css": case "shtm":
        $type = "SOURCE";
        break;
        default:
        $type = "UNKNOWN";
    }
    return $type;
}

function GetDirectoryIndex($path, $strDirIndex=false)
{
    return GetDirIndex($path, $strDirIndex);
}
 
function GetDirPath($sPath)
{
    if(strlen($sPath))
    {
        $p = strrpos($sPath, "/");
        if($p === false)
        return '/';
        else
        return substr($sPath, 0, $p+1);
    }
    else
    {
        return '/';
    }
}


function NormalizePhone($number, $minLength = 10)
{
    $minLength = intval($minLength);
    if ($minLength <= 0 || strlen($number) < $minLength) {
        return false;
    }
    if (strlen($number) >= 10 && substr($number, 0, 2) == '+8') {
        $number = '00'.substr($number, 1);
    }
    $number = preg_replace("/[^0-9\#\*]/i", "", $number);
    if (strlen($number) >= 10)
    {
        if (substr($number, 0, 2) == '80' || substr($number, 0, 2) == '81' || substr($number, 0, 2) == '82') {
        } else if (substr($number, 0, 2) == '00') {
            $number = substr($number, 2);
        } else if (substr($number, 0, 3) == '011') {
            $number = substr($number, 3);
        } else if (substr($number, 0, 1) == '8') {
            $number = '7'.substr($number, 1);
        } else if (substr($number, 0, 1) == '0') {
            $number = substr($number, 1);
        }
    }
    return $number;
}

function InputType($strType, $strName, $strValue, $strCmp, $strPrintValue=false, $strPrint="", $field1="", $strId="")
{
    $bCheck = false;
    if($strValue <> '') {
        if(is_array($strCmp))
        $bCheck = in_array($strValue, $strCmp);
        elseif($strCmp == '')
        $bCheck = in_array($strValue, explode(",", $strCmp));
    }
    $bLabel = false;
    if ($strType == 'radio')
    $bLabel = true;
    return ($bLabel? '<label>': '').'<input type="'.$strType.'" name="'.$strName.'" id="'.($strId <> ''? $strId : $strName).'" value="'.$strValue.'">'.($strPrintValue? $strValue:$strPrint).($bLabel? '</label>': '');
}


function TruncateText($strText, $intLen)
{
    if(strlen($strText) > $intLen)
    return rtrim(substr($strText, 0, $intLen), ".")."...";
    else
    return $strText;
}


function extract_url($s)
{
    $s2 = '';
    while(strpos(",}])&gt;.", substr($s, -1, 1))!==false)
    {
        $s2 = substr($s, -1, 1);
        $s = substr($s, 0, strlen($s)-1);
    }
    $res = chr(1).$s."/".chr(1).$s2;
    return $res;
}


function DeleteDirFilesEx($path)
{
    if(strlen($path) == 0 || $path == '/')
    return false;
    
    $full_path = $_SERVER["DOCUMENT_ROOT"]."/".$path;
    $full_path = preg_replace("#[\\\\\\/]+#", "/", $full_path);
    
    $f = true;
    if(is_file($full_path) || is_link($full_path))
    {
        if(@unlink($full_path))
        return true;
        return false;
    }
    elseif(is_dir($full_path))
    {
        if($handle = opendir($full_path))
        {
            while(($file = readdir($handle)) !== false)
            {
                if($file == "." || $file == "..")
                continue;
                
                if(!DeleteDirFilesEx($path."/".$file))
                $f = false;
            }
            closedir($handle);
        }
        if(!@rmdir($full_path))
        return false;
        return $f;
    }
    return false;
}

function DeleteDirFiles($frDir, $toDir, $arExept = array())
{
    if(is_dir($frDir))
    {
        $d = dir($frDir);
        while ($entry = $d->read())
        {
            if ($entry=="." || $entry=="..")
            continue;
            if (in_array($entry, $arExept))
            continue;
            @unlink($toDir."/".$entry);
        }
        $d->close();
    }
}


function GetFileName($path)
{
    $path = TrimUnsafe($path);
    $path = str_replace("\\", "/", $path);
    $path = rtrim($path, "/");
    
    $p = bxstrrpos($path, "/");
    if($p !== false)
    return substr($path, $p+1);
    
    return $path;
}

function GetDirPath($sPath)
{
    if(strlen($sPath))
    {
        $p = strrpos($sPath, "/");
        if($p === false)
        return '/';
        else
        return substr($sPath, 0, $p+1);
    }
    else
    {
        return '/';
    }
}


//Парсим URL
function parseURL($url, $arUrlOld = false)
{
    $arUrl = parse_url($url);
    if (is_array($arUrlOld)) {
        if (!array_key_exists('scheme', $arUrl)) {
            $arUrl['scheme'] = $arUrlOld['scheme'];
        }
        if (!array_key_exists('host', $arUrl)) {
            $arUrl['host'] = $arUrlOld['host'];
        }
        
        if (!array_key_exists('port', $arUrl)) {
            $arUrl['port'] = $arUrlOld['port'];
        }
    }
    $arUrl['proto'] = '';
    if (array_key_exists('scheme', $arUrl)) {
        $arUrl['scheme'] = strtolower($arUrl['scheme']);
    } else {
        $arUrl['scheme'] = 'http';
    }
    if (!array_key_exists('port', $arUrl)) {
        if ($arUrl['scheme'] == 'https') {
            $arUrl['port'] = 443;
        } else {
            $arUrl['port'] = 80;
        }
    }
    if ($arUrl['scheme'] == 'https') {
        $arUrl['proto'] = 'ssl://';
    }
    $arUrl['path_query'] = array_key_exists('path', $arUrl) ? $arUrl['path'] : '/';
    if (array_key_exists('query', $arUrl) && strlen($arUrl['query']) > 0) {
        $arUrl['path_query'] .= '?' . $arUrl['query'];
    }
    return $arUrl;
}

 

function InputType($strType, $strName, $strValue, $strCmp, $strPrintValue=false, $strPrint="", $field1="", $strId="")
{
    $bCheck = false;
    if($strValue == '')
    {
        if(is_array($strCmp))
        $bCheck = in_array($strValue, $strCmp);
        elseif($strCmp == '')
        $bCheck = in_array($strValue, explode(",", $strCmp));
    }
    $bLabel = false;
    if ($strType == 'radio')
    $bLabel = true;
    return ($bLabel? '<label>': '').'<input type="'.$strType.'" name="'.$strName.'" id="'.($strId <> ''? $strId : $strName).'" value="'.$strValue.'">'.($strPrintValue? $strValue:$strPrint).($bLabel? '</label>': '');
}

function TruncateText($strText, $intLen)
{
    if(strlen($strText) > $intLen)
    return rtrim(substr($strText, 0, $intLen), ".")."...";
    else
    return $strText;
}


function extract_url($s)
{
    $s2 = '';
    while(strpos(",}])&gt;.", substr($s, -1, 1))!==false)
    {
        $s2 = substr($s, -1, 1);
        $s = substr($s, 0, strlen($s)-1);
    }
    $res = chr(1).$s."/".chr(1).$s2;
    return $res;
}



// Переобразуем HTML в текст, сохраняя переносы и пробелы
function HTMLToTxt($str, $strSiteUrl="", $aDelete=array(), $maxlen=70)
{
    //get rid of whitespace
    $str = preg_replace("/[\\t\\n\\r]/", " ", $str);
    
    //replace tags with placeholders
    static $search = array(
    "'<script[^>]*?>.*?</script>'si",
    "'<style[^>]*?>.*?</style>'si",
    "']*?&gt;.*?'si",
    "'&(quot|#34);'i",
    "'&(iexcl|#161);'i",
    "'&(cent|#162);'i",
    "'&(pound|#163);'i",
    "'&(copy|#169);'i",
    );
    
    static $replace = array(
    "",
    "",
    "",
    "\"",
    "\xa1",
    "\xa2",
    "\xa3",
    "\xa9",
    );
    
    $str = preg_replace($search, $replace, $str);
    
    $str = preg_replace("#&lt;[/]{0,1}(b|i|u|em|small|strong)&gt;#i", "", $str);
    $str = preg_replace("#&lt;[/]{0,1}(font|div|span)[^&gt;]*&gt;#i", "", $str);
    
    //ищем списки
    $str = preg_replace("#]*&gt;#i", "\r\n", $str);
    $str = preg_replace("#]*&gt;#i", "\r\n  - ", $str);
    
    //удалим то что задано
    foreach($aDelete as $del_reg)
    $str = preg_replace($del_reg, "", $str);
    
    //ищем картинки
    $str = preg_replace("/(|\\s*&gt;)/is", "[".chr(1).$strSiteUrl."\\3".chr(1)."] ", $str);
    $str = preg_replace("/(|\\s*&gt;)/is", "[".chr(1)."\\3".chr(1)."] ", $str);
    
    //ищем ссылки
    $str = preg_replace("/()(.*?)&lt;\\/a&gt;/is", "\\6 [".chr(1).$strSiteUrl."\\3".chr(1)."] ", $str);
    $str = preg_replace("/()(.*?)&lt;\\/a&gt;/is", "\\6 [".chr(1)."\\3".chr(1)."] ", $str);
    
    //ищем <br>
    $str = preg_replace("#]*&gt;#i", "\r\n", $str);
    
    //ищем <br>
    $str = preg_replace("#]*&gt;#i", "\r\n\r\n", $str);
    
    //ищем <hr>
    $str = preg_replace("#]*&gt;#i", "\r\n----------------------\r\n", $str);
    
    //ищем таблицы
    $str = preg_replace("#&lt;[/]{0,1}(thead|tbody)[^&gt;]*&gt;#i", "", $str);
    $str = preg_replace("#&lt;([/]{0,1})th[^&gt;]*&gt;#i", "&lt;\\1td&gt;", $str);
    
    $str = preg_replace("##i", "\t", $str);
    $str = preg_replace("##i", "\r\n", $str);
    $str = preg_replace("#]*&gt;#i", "\r\n", $str);
    
    $str = preg_replace("#\r\n[ ]+#", "\r\n", $str);
    
    //мочим вообще все оставшиеся тэги
    $str = preg_replace("#&lt;[/]{0,1}[^&gt;]+&gt;#i", "", $str);
    
    $str = preg_replace("#[ ]+ #", " ", $str);
    $str = str_replace("\t", "    ", $str);
    
    //переносим длинные строки
    if($maxlen > 0)
    $str = preg_replace("#([^\\n\\r]{".intval($maxlen)."}[^ \\r\\n]*[\\] ])([^\\r])#", "\\1\r\n\\2", $str);
    
    $str = str_replace(chr(1), " ",$str);
    return trim($str);
}


/**
    * Функция фильтрует строку и устанавливает формат вывода телефонного номера
    * @param string $phone Строка с телефоном
    * @return string
*/

function setFormatPhone($phone)
{
    $phone = preg_replace("/[^0-9]/", "", $phone);
    
    if(strlen($phone) == 7)
    $phone = preg_replace("/([0-9]{3})([0-9]{2})([0-9]{2})/", "$1-$2-$3", $phone);
    elseif(strlen($phone) == 10)
    $phone = preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "($1)$2-$3", $phone);
    elseif(strlen($phone) == 11)
    {
        $phone = preg_replace("/([0-9])([0-9]{3})([0-9]{3})([0-9]{4})/", "$1($2)$3-$4", $phone);
        $first = substr($phone, 0, 1);
        if(in_array($first, array(7, 8)))
        $phone = '+7'. substr($phone, 1);
    }
    
    return $phone;
}
getNameDay($date = false)

/**
    * Получаем название дня недели
    * @param string|int $date
    * @return string
*/

function getNameDay($date = false)
{
    if(!$date)
    $date = mktime();
    
    $date = is_int($date) ? $date : strtotime($date);
    $names = array(
    'Monday'    => 'Понедельник',
    'Tuesday'   => 'Вторник',
    'Wednesday' => 'Среда',
    'Thursday'  => 'Четверг',
    'Friday'    => 'Пятница',
    'Saturday'  => 'Суббота',
    'Sunday'    => 'Воскресенье'
    );
    
    return $names[ date("l", $date) ];
    
    
    getDaysFromWeek($week = false, $format = 'd.m.Y');
    
    //Функция возвращает интервал дат по номеру недели:
    
    /**
        * По номеру недели функция возвращает интервал дат от понедельника до воскресенья.
        * @param int $week Порядковый номер недели в году
        * @param type $format Формат выводимой даты, по умолчанию 'd.m.Y'
        * @return array
    */
    
    function getDaysFromWeek($week = false, $format = 'd.m.Y')
    {
        if(!$week)
        $week = date("W");
        
        $result['today'] = date($format);
        $result['begin'] = date($format, strtotime(date("W") - date("W") ." week -". (date("w") == 0 ? 6 : date("w") - 1) ." day"));
        $result['end']   = date($format, strtotime($result['begin']) + 60 * 60 * 24 * 6);
        
        $result[] = $result['begin'];
        for($i = 1; $i < 7; $i++)
        $result[] = date($format, strtotime($result['begin']) + 60 * 60 * 24 * $i);
        
        return $result;
    }
    
    
    //Функция для генерации паролей и любых строк заданной длины. Второй параметр может быть массивом классов символов. Если он указан, тогда в результирующую строчку войдет минимум один символ из каждого класса. Функция выдернута из CMS-Bitrix
    randString($pass_len=10, $pass_chars=false)
    
    function randString($pass_len=10, $pass_chars=false)
    {
        static $allchars = "abcdefghijklnmopqrstuvwxyzABCDEFGHIJKLNMOPQRSTUVWXYZ0123456789";
        $string = "";
        if(is_array($pass_chars))
        {
            while(strlen($string) < $pass_len)
            {
                if(function_exists('shuffle'))
                shuffle($pass_chars);
                foreach($pass_chars as $chars)
                {
                    $n = strlen($chars) - 1;
                    $string .= $chars[mt_rand(0, $n)];
                }
            }
            if(strlen($string) > count($pass_chars))
            $string = substr($string, 0, $pass_len);
        }
        else
        {
            if($pass_chars !== false)
            {
                $chars = $pass_chars;
                $n = strlen($pass_chars) - 1;
            }
            else
            {
                $chars = $allchars;
                $n = 61; //strlen($allchars)-1;
            }
            for ($i = 0; $i < $pass_len; $i++)
            $string .= $chars[mt_rand(0, $n)];
        }
        return $string;
    }
    
    echo randString(7, array(
    "abcdefghijklnmopqrstuvwxyz",
    "ABCDEFGHIJKLNMOPQRSTUVWX­YZ",
    "0123456789",
    "!@#\$%^&*()",
    ));
    
    
    
    
    /**
        * Function of processing of variables for a conclusion in a stream
        * Функция обработки переменных для вывода в поток
    */
    htmlChars($data);
    function htmlChars($data)
    {
        if(is_array($data))
        $data = array_map("htmlChars", $data);
        else
        $data = htmlspecialchars($data);
        
        return $data;
    }
    /**
        * Function of processing of variables for a conclusion in a stream
        * Функция обработки переменных для вывода в поток
    */
    function htmlChars_decode($data)
    {
        if(is_array($data))
        $data = array_map("htmlChars_decode", $data);
        else
        $data = htmlspecialchars_decode($data);
        
        return $data;
    }
    
    
    drawTable($data, $tabs = 0, $columns = 10)
    
    /**
        *  Table division
        *  Деление таблицы
    */
    function drawTable($data, $tabs = 0, $columns = 10)
    {
        $tbl = null;
        
        if($tabs === false)
        {
            $tr = $td = null;
        }
        else
        {
            $tr = "\n".str_repeat("\t", $tabs);
            $td = $tr."\t";
        }
        
        for($i = 0, $n = 1, $d = ceil(count($data) / $columns) * $columns; $i < $d; $i++, $n++)
        {
            if($n == 1)
            $tbl .= $tr."\n";
            
            $tbl .= $td."\n".(isset($data[$i]) ? $data[$i] : ' ')."\n";
            
            if($n == $columns)
            {
                $n = 0;
                $tbl .= $tr.'';
            }
        }
        
        if($tabs !== false)
        $tbl .= "\n";
        
        return $tbl;
        /*
            $gallery  = "";
            $gallery .= drawTable($rows, IRB_IMAGES_ROWS, IRB_IMAGES_COLUMNS);
            $gallery .= "
        "; */ }
        
        
        
        
        //Чтобы положить в БД строку, её лучше обработать этой штукой:
        
        /**
            * Function of processing of literal constants for SQL
            * Функция обработки литеральных констант для SQL
        */
        
        function escapeString($data)
        {
            
            if(is_array($data))
            $data = array_map("escapeString", $data);
            else
            $data = mysql_real_escape_string($data);
            
            return $data;
        }
        
        
        function uploadHandle($file_name, $max_file_size = 100, $extensions = array(), $upload_dir = '.', $out_name = false)
        {
            
            $error = null;
            $info  = null;
            $max_file_size *= 1024;
            
            if ($_FILES[$file_name]['error'] === UPLOAD_ERR_OK)
            {
                // проверяем расширение файла
                $file_extension = pathinfo($_FILES[$file_name]['name'], PATHINFO_EXTENSION);
                if (in_array($file_extension, $extensions))
                {
                    // проверяем размер файла
                    if ($_FILES[$file_name]['size'] < $max_file_size)
                    {
                        // новое имя файла
                        if($out_name)
                        $out_name = str_replace('.'.$file_extension, '', $out_name) .'.'. $file_extension;
                        else
                        $out_name = mt_rand(mt_rand(10, 1000), 100000) .'_'. $_FILES[$file_name]['name'];
                        
                        $destination = $upload_dir .'/' . $out_name;
                        
                        if(move_uploaded_file($_FILES[$file_name]['tmp_name'], $destination))
                        $info = LANG_FILE_MESS_OK;
                        else   
                        $error = LANG_FILE_MESS_ERR_LOAD;
                    }
                    else
                    $error = LANG_FILE_MESS_MAX_SIZE;
                }
                else
                $error = LANG_FILE_MESS_ERR_EXT;
            }
            else
            {
                // массив ошибок
                $error_values = array(
                UPLOAD_ERR_INI_SIZE   => LANG_FILE_ERR_INI_SIZE,
                UPLOAD_ERR_FORM_SIZE  => LANG_FILE_ERR_FORM_SIZE,
                UPLOAD_ERR_PARTIAL    => LANG_FILE_ERR_PARTIAL,
                UPLOAD_ERR_NO_FILE    => LANG_FILE_ERR_NO_FILE,
                UPLOAD_ERR_NO_TMP_DIR => LANG_FILE_ERR_NO_TMP_DIR,
                UPLOAD_ERR_CANT_WRITE => LANG_FILE_ERR_CANT_WRITE
                );
                
                $error_code = $_FILES[$file_name]['error'];
                
                if (!empty($error_values[$error_code]))
                $error = $error_values[$error_code];
                else
                $error = LANG_FILE_MESS_BUG;
            }
            
            return array('info' => $info, 'error' => $error, 'name' => $out_name);
        }
        
        


        /*
            * xmlToArray() will convert the given XML text to an array in the XML structure.
            * Link: http://www.bin-co.com/php/scripts/xmlToArray/
            * Arguments : $contents - The XML text
            *     $get_attributes - 1 or 0. If this is 1 the function will get the attributes as well as the tag values - this results in a different array structure in the return value.
            *     $priority - Can be 'tag' or 'attribute'. This will change the way the resulting array sturcture. For 'tag', the tags are given more importance.
            * Return: The parsed XML in an array form. Use print_r() to see the resulting array structure.
            * Examples: $array =  xmlToArray(file_get_contents('feed.xml'));
            *   $array =  xmlToArray(file_get_contents('feed.xml', 1, 'attribute'));
            * xmlToArray($contents, $get_attributes, $priority) 
        */
        
        function xmlToArray($contents, $get_attributes = 1, $priority = 'tag')
        {
            if(!$contents) return array();
            
            if(!function_exists('xml_parser_create')) {
                //print "'xml_parser_create()' function not found!";
                return array();
            }
            
            //Get the XML parser of PHP - PHP must have this module for the parser to work
            $parser = xml_parser_create('');
            xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8"); # http://minutillo.com/steve/weblog/2004/6/17/php-xml-and-character-encodings-a-tale-of-sadness-rage-and-data-loss
            xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
            xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
            xml_parse_into_struct($parser, trim($contents), $xml_values);
            xml_parser_free($parser);
            
            if(!$xml_values) return;//Hmm...
            
            //Initializations
            $xml_array = array();
            $parents = array();
            $opened_tags = array();
            $arr = array();
            
            $current = &$xml_array; //Refference
            
            //Go through the tags.
            $repeated_tag_index = array();//Multiple tags with same name will be turned into an array
            foreach($xml_values as $data) {
                unset($attributes,$value);//Remove existing values, or there will be trouble
                
                //This command will extract these variables into the foreach scope
                // tag(string), type(string), level(int), attributes(array).
                extract($data);//We could use the array by itself, but this cooler.
                
                $result = array();
                $attributes_data = array();
                
                if(isset($value)) {
                    if($priority == 'tag') $result = $value;
                    else $result['value'] = $value; //Put the value in a assoc array if we are in the 'Attribute' mode
                }
                
                //Set the attributes too.
                if(isset($attributes) and $get_attributes) {
                    foreach($attributes as $attr => $val) {
                        if($priority == 'tag') $attributes_data[$attr] = $val;
                        else $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
                    }
                }
                
                //See tag status and do the needed.
                if($type == "open") {//The starting of the tag ''
                    $parent[$level-1] = &$current;
                    if(!is_array($current) or (!in_array($tag, array_keys($current)))) { //Insert New tag
                        $current[$tag] = $result;
                        if($attributes_data) $current[$tag. '_attr'] = $attributes_data;
                        $repeated_tag_index[$tag.'_'.$level] = 1;
                        
                        $current = &$current[$tag];
                        
                        } else { //There was another element with the same tag name
                        
                        if(isset($current[$tag][0])) {//If there is a 0th element it is already an array
                            $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;
                            $repeated_tag_index[$tag.'_'.$level]++;
                            } else {//This section will make the value an array if multiple tags with the same name appear together
                            $current[$tag] = array($current[$tag],$result);//This will combine the existing item and the new item together to make an array
                            $repeated_tag_index[$tag.'_'.$level] = 2;
                            
                            if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well
                                $current[$tag]['0_attr'] = $current[$tag.'_attr'];
                                unset($current[$tag.'_attr']);
                            }
                            
                        }
                        $last_item_index = $repeated_tag_index[$tag.'_'.$level]-1;
                        $current = &$current[$tag][$last_item_index];
                    }
                    
                    } elseif($type == "complete") { //Tags that ends in 1 line ''
                    //See if the key is already taken.
                    if(!isset($current[$tag])) { //New Key
                        $current[$tag] = $result;
                        $repeated_tag_index[$tag.'_'.$level] = 1;
                        if($priority == 'tag' and $attributes_data) $current[$tag. '_attr'] = $attributes_data;
                        
                        } else { //If taken, put all things inside a list(array)
                        if(isset($current[$tag][0]) and is_array($current[$tag])) {//If it is already an array...
                            
                            // ...push the new element into that array.
                            $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;
                            
                            if($priority == 'tag' and $get_attributes and $attributes_data) {
                                $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
                            }
                            $repeated_tag_index[$tag.'_'.$level]++;
                            
                            } else { //If it is not an array...
                            $current[$tag] = array($current[$tag],$result); //...Make it an array using using the existing value and the new value
                            $repeated_tag_index[$tag.'_'.$level] = 1;
                            if($priority == 'tag' and $get_attributes) {
                                if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well
                                    
                                    $current[$tag]['0_attr'] = $current[$tag.'_attr'];
                                    unset($current[$tag.'_attr']);
                                }
                                
                                if($attributes_data) {
                                    $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
                                }
                            }
                            $repeated_tag_index[$tag.'_'.$level]++; //0 and 1 index is already taken
                        }
                    }
                    
                    } elseif($type == 'close') { //End of tag ''
                    $current = &$parent[$level-1];
                }
            }
            
            return($xml_array);
        }
        
        /**
            * Функция обрезает текст по окончании слова.
            * Если задан параметр $link, то в конце текста вставляется ссылка с адресом $link.
            * @param string $string Строка которую нужно обрезать.
            * @param integer $maxlen Количество символов до обрезания.
            * @param string $link URL address.
            * @param string $encoding Кодировка текста, по умолчанию "UTF-8".
            * @return string
        */
        function cropText($string = '', $maxlen = 100, $link = false, $encoding = "UTF-8")
        {
            $len = (mb_strlen($string, $encoding) > $maxlen)
            ? mb_strripos(mb_substr($string, 0, $maxlen, $encoding), ' ', 0, $encoding)
            : $maxlen;
            
            $cutStr = rtrim(mb_substr($string, 0, $len, $encoding), "., |()/");
            
            $result = (mb_strlen($string, $encoding) > $maxlen) ? $cutStr .'...' : $cutStr;
            
            if(!$link)
            return trim($result);
            else
            {
                // Цифра 1130 несёт в себе временный костыль, на её месте должно быть
                // число 30. Проблема связана с кодировкой сервера.
                if(strlen($result) < 20)
                return ''. $result .'';
                
                //$pos = mb_strripos($result, ' ', round(mb_strlen($result, $encoding) / 3), $encoding);
                $pos = mb_strrpos($result, ' ', -round(mb_strlen($result, $encoding) / 3), $encoding);
                $string_bgn = mb_substr($result, 0, $pos, $encoding);
                $string_end = mb_substr($result, $pos, 1000, $encoding);
                
                return trim($string_bgn .' '. $string_end .'');
            }
        }
        clearText($text)
        
        /**
            * Текст на выходе должен содержать HTML-документ.
            * Необходимо удалить все HTML-теги, секции javascript, пробельные символы.
            * Также необходимо заменить некоторые HTML-сущности на их эквивалент.
            * @param string $text Входящий текст
            * @return string
        */
        function clearText($text)
        {
            $search = ["']*?&gt;.*?'si", // Вырезает javaScript
            "'&lt;[\/\!]*?[^<>]*?&gt;'si",// Вырезает HTML-теги
            "'([\r\n])[\s]+'", // Вырезает пробельные символы
            "'&(quot|#34);'i", // Заменяет HTML-сущности
            "'&(amp|#38);'i",
            "'&(lt|#60);'i",
            "'&(gt|#62);'i",
            "'&(nbsp|#160);'i",
            "'&(iexcl|#161);'i",
            "'&(cent|#162);'i",
            "'&(pound|#163);'i",
            "'&(copy|#169);'i",
            "'&#(\d+);'e"]; // интерпретировать как php-код
            $replace = [
			    "",
			    "",
			    "\\1",
			    "\"",
			    "&",
			    "<",
			    ">",
			    " ",
			    chr(161),
			    chr(162),
			    chr(163),
			    chr(169),
			    "chr(\\1)"
			];
            return preg_replace($search, $replace, $text);
        }

        function clean_phone_number($phone) {
            if (!empty($phone)) {
                //var_dump($phone);
                preg_match_all('/[0-9\(\)+.\- ]/s', $phone, $cleaned);
                foreach($cleaned[0] as $k=>$v) {
                    $ready .= $v;
                }
                var_dump($ready);
                die;
                if (mb_strlen($cleaned) > 4 && mb_strlen($cleaned) <=25) {
                    return $cleaned;
                }
                else {
                    return false;
                }
            }
            return false;
        }
        
        //$extension = substr($file_name, strrpos($file_name, "."));
        //$extension = end(explode(".", $file_name));
        
        function formspecialchars($var)
        {
            $pattern = '/&(#)?[a-zA-Z0-9]{0,};/';
            if (is_array($var)) { // If variable is an array
                $out = array(); // Set output as an array
                foreach ($var as $key => $v) {
                    $out[$key] = formspecialchars($v); // Run formspecialchars on every element of the array and return the result. Also maintains the keys.
                }
                } else {
                $out = $var;
                while (preg_match($pattern,$out) > 0) {
                    $out = htmlspecialchars_decode($out,ENT_QUOTES);
                }
                $out = htmlspecialchars(stripslashes(trim($out)), ENT_QUOTES,'UTF-8',true);     // Trim the variable, strip all slashes, and encode it
            }
            return $out;
        }
        
        
        
        
        function paginator($count, $limit, $page, $url = null, $name = 'page', $block = 33) {
            if (!$url) {
                $url = $_SERVER['REQUEST_URI'];
                if (mb_strstr($url, '?'.$name.'=')) $url = preg_replace('/\?'.$name.'=[0-9]*/', '', $url);
            }
            if (!$limit) $limit = 10;
            if ($count <= $limit) return;
            $page_list = null;  
            if (strstr($url, '?')) $qw = '&';
            else $qw = '?';   
            if ($page > $block) {
                $i = floor($page/ $block); 
                $i = $i * $block;
                if ($i == $page) $i--;
                $a_url = $url.$qw.$name.'='.$i;
                $page_list .= '<br>&lt;&lt;<br>';
            } else $i = 0;
            for ($j = 0; $i < ceil($count / $limit), $j < $block; $i++, $j++) {
                if ($i * $limit >= $count) break;
                if ($i == $page-1) {
                    $a_url = $url.$qw.$name.'='.$page;
                    if ($page != $i + 1) $page_list .= '<br>'.($i + 1).'<br>';
                    else $page_list .= '<br>'.($i + 1).'<br>';
                    } else {
                    $a_url = $url.$qw.$name.'='.($i + 1);
                    if ($page != $i + 1) $page_list .= '<br>'.($i + 1).'<br>';
                    else $page_list .= '<br>'.($i + 1).'<br>';
                }  
            }
            if ($i < ceil($count / $limit)) {
                $a_url = $url.$qw.$name.'='.($i + 1);
                $page_list .= '<br>&gt;&gt;<br>';
            }  
            return '<br>'.$page_list.'<br>';
        }
        
        
        function pagination($numRows, $rowsPerPage=1){
            $pageParamNm = "page";
            $pages = ceil( $numRows/$rowsPerPage );
            if ( $pages < 2 ) return "";
            $res = "Страница ";
            if ( array_key_exists("page", $_GET)) {
                $currentPage = is_numeric($_GET[$pageParamNm]) ? $_GET[$pageParamNm] : 1;
                unset( $_GET[$pageParamNm] );
            } else {
                $currentPage = 1;
            }
            $params = '';
            foreach ( $_GET as $k=>$v)
			{
                $params .= $k . '=' . urlencode($v) . '&amp;';
            }
            $path = explode( "?", $_SERVER['REQUEST_URI'] );
            for ( $i = 1; $i <= $pages; $i++ )
			{
                $res .= sprintf("%s", $i == $currentPage ? $i : "{$i}");
            }
            return $res;
        }
        
        
        function paginator_extra($count, $limit, $page, $block = 12) {
            if ($count <= $limit) {
				return null;
			}
            if ($page > $block) {
                $i = floor($page / $block) * $block;
                if ($i == $page) $i -= $block;
                $pages_list .= '<br>&lt;<br>';
            } else {
			    $i = 0;
			}
            for ($j = 0; $i < ceil($count / $limit), $j < $block; $i++, $j++) {
                if ($i * $limit >= $count) break;
                $pages_list .= ''.($i + 1).'';
            }
            if ($i < ceil($count / $limit)) { 
				$pages_list .= '<br>&gt;<br>';
			}
            return $pages_list;
        }
