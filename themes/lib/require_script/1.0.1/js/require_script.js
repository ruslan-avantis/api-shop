// require_script - Подружит PHP программиста с jQuery
// Теперь вы через requireScript или executeScript можете подключать библиотеки https://github.com/kvz/locutus/ они идут в комплекте.

// Например вам нужно подключить библиотеку bootstrap версии 4.0.0-alpha.6
// https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css
// https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js

// require_script предлагает несколько методов

//    function executeScript
//    $.getScript(executeScript(templates_lib, "bootstrap", "4.0.0-alpha.6", false, true, false, "cloudflare"), function(){
        // Получить доступ к подключаемому скрипту вы можете только внутри функции getScript
//    });

//    Подключаем функцию array_change_key_case
//    $.getScript(executeScript(php_array, 'array_change_key_case'), function(){
//         // Получить доступ к функциям подключаемого скрипта вы можете только внутри функции getScript
//        array_change_key_case(array, cs)
//     });

//    function requireScript - Функция принимает масcив json с папаметрами и название функции 
//    которой нужно дать доступ к подключаемым функциям через callback
//    requireScript({
//        "dir": templates_lib, 
//        "name": "bootstrap", 
//        "version": "4.0.0-alpha.6", 
//        "min": false, 
//        "css": true, 
//        "async": false, 
//        "cdn": "cloudflare"
//    }, '')

// Учтите что глобально должна быть прописанна переменная templates_lib путь к папке всех библиотек
// var templates_lib = '/templates/lib'

var templates_dir = templates_lib // Можно изменить значение templates_lib или заменить здесь
// var templates_dir = 'https://cdn.jommimart.com/' // Или подключаем с нашего репозитория тогда в version_require_script черточку - замените на слеш /
var version_require_script = '/1.0.1' // версия require_script
var require_script = templates_dir + '/require_script' + version_require_script // полный путь к папке require_script

function requireScript(arr, callback){

    js_cdn = ''
    js_name = ''
    js_version = ''
    js_min = ''
    css_subdir = ''
    js_subdir = ''
    
    if ( typeof arr.hasOwnProperty('dir') !== "undefined" && arr['dir'] ){
        js_cdn = arr['dir'] + '/'
    }
    
    if ( typeof arr.hasOwnProperty('name') !== "undefined" && arr['name'] ){
        js_name = arr['name']
    }
    
    if ( typeof arr.hasOwnProperty('version') !== "undefined" && arr['version'] ){
        js_version = arr['version'] + '/'
    }
    
    if ( typeof arr.hasOwnProperty('min') !== "undefined" && arr['min']){
        js_min = '.min'
    }
    
    if ( typeof arr.hasOwnProperty('css') !== "undefined" && arr['css']){
        css_subdir = 'css/'
        js_subdir = 'js/'
    }
    
    if ( typeof arr.hasOwnProperty('cdn') !== "undefined" && arr['cdn']){
        if (arr['cdn'] == "cloudflare") {
            js_cdn = 'https://cdnjs.cloudflare.com/ajax/libs/' + js_name + '/' // Подключаем cloudflare
        }
    }

    var head  = document.getElementsByTagName('head')[0]
    var script = document.createElement('script')
    
    script.type = 'text/javascript'
    script.src = js_cdn + js_version + js_subdir + js_name + js_min + '.js'

    if ( typeof arr.hasOwnProperty('async') !== "undefined" && arr['async'] ){
        if (arr['async'] == true) {
            script.async = true
        } else {
            script.async = false
        }
    } else {
            script.async = false
    }
    
    // Then bind the event to the callback function.
    // There are several events for cross browser compatibility.
    script.onreadystatechange = callback
    script.onload = callback

    // Fire the loading
    head.appendChild(script);
    
    if( typeof arr.hasOwnProperty('css') ){
            if (arr['css'] == true) {
            var link  = document.createElement('link')
            link.rel  = 'stylesheet'
            link.type = 'text/css'
            link.href = js_cdn + js_version + css_subdir + js_name + js_min + '.css'
            link.media = 'all'
            head.appendChild(link)
        }
    }

}

function loadScript(url, callback){
    // Adding the script tag to the head as suggested before
    var head = document.getElementsByTagName('head')[0]
    var script = document.createElement('script')
    script.type = 'text/javascript'
    script.src = url

    // Then bind the event to the callback function.
    // There are several events for cross browser compatibility.
    script.onreadystatechange = callback
    script.onload = callback;

    // Fire the loading
    head.appendChild(script)
}

// Пример использования
// Функция которая должна получить callback
// var myPrettyCode = function() {
//     Here, do what ever you want
// };
// Запускаем
// loadScript("my_script.js", myPrettyCode);

// Альтернативная более легкая функция подключения скрипта
// requireLib('../lib/js/', 'script.js'); // загружаться эти скрипты начнут сразу

function requireLib(dir, name){
  var script = document.createElement('script')
  script.src = dir + name + '.js'
  script.async = false // чтобы гарантировать порядок
  document.head.appendChild(script)
}

// executeScript - функция конструктор. Она возвращает только url скрипта
// dir - Папка библиотек по умолчанию берется из /templates/lib или пропишите глобально var lib = '/templates/lib'
// name - название библиотеки
// version - версия библиотеки. Добавляется в путь к файлу
// min - true или false - если true подключит файл с окончанием .min.js
// css - true или false - если true дополнительно подключит файл css с таким же названием
// async - true или false - если true асинхронная загрузка
// cdn - true или false - для подключения с cdn
// Примеры работы есть ниже по каждой функции

function executeScript(dir, name, version = false, min = false, css = false, async = false, cdn = false) {
    
    js_cdn = dir + '/'
    js_name = name + ''
    js_version = ''
    js_min = ''
    css_subdir = ''
    js_subdir = ''
    
    if (css == true) {
    css_subdir = 'css/'
    js_subdir = 'js/'
    }
    
    if (version != false) {
        js_version = version + '/'
    }
    
    if (version != false) {
        js_min = '.min'
    }
    
    if (cdn === "cloudflare") {
        js_cdn = 'https://cdnjs.cloudflare.com/ajax/libs/' + js_name + '/' // Подключаем с cloudflare
    }
    
    url = js_cdn + js_name + js_version + js_subdir + js_min + '.js'
    
    if (css == true) {
        var head  = document.getElementsByTagName('head')[0]
        var link  = document.createElement('link')
        link.rel  = 'stylesheet'
        link.type = 'text/css'
        link.href = js_cdn + js_name + js_version + css_subdir + js_min + '.css'
        link.media = 'all'
        head.appendChild(link)
    }
    
    return url

}

var php_array = require_script + '/php/array'

// $.getScript(executeScript(php_array, 'array_change_key_case'), function(){ array_change_key_case(array, cs) });
// $.getScript(executeScript(php_array, 'array_chunk'), function(){ array_chunk(input, size, preserveKeys) });
// $.getScript(executeScript(php_array, 'array_combine'), function(){ array_combine(keys, values) });
// $.getScript(executeScript(php_array, 'array_count_values'), function(){ array_count_values(array) });
// $.getScript(executeScript(php_array, 'array_diff'), function(){ array_diff(arr1) });
// $.getScript(executeScript(php_array, 'array_diff_assoc'), function(){ array_diff_assoc(arr1) });
// $.getScript(executeScript(php_array, 'array_diff_key'), function(){ array_diff_key(arr1) });
// $.getScript(executeScript(php_array, 'array_diff_uassoc'), function(){ array_diff_uassoc(arr1) });
// $.getScript(executeScript(php_array, 'array_diff_ukey'), function(){ array_diff_ukey(arr1) });
// $.getScript(executeScript(php_array, 'array_fill'), function(){ array_fill(startIndex, num, mixedVal) });
// $.getScript(executeScript(php_array, 'array_fill_keys'), function(){ array_fill_keys(keys, value) });
// $.getScript(executeScript(php_array, 'array_filter'), function(){ array_filter(arr, func) });
// $.getScript(executeScript(php_array, 'array_flip'), function(){ array_flip(trans) });
// $.getScript(executeScript(php_array, 'array_intersect'), function(){ array_intersect(arr1) });
// $.getScript(executeScript(php_array, 'array_intersect_assoc'), function(){ array_intersect_assoc(arr1) });
// $.getScript(executeScript(php_array, 'array_intersect_key'), function(){ array_intersect_key(arr1) });
// $.getScript(executeScript(php_array, 'array_intersect_uassoc'), function(){ array_intersect_uassoc(arr1) });
// $.getScript(executeScript(php_array, 'array_intersect_ukey'), function(){ array_intersect_ukey(arr1) });
// $.getScript(executeScript(php_array, 'array_key_exists'), function(){ array_key_exists(key, search) });
// $.getScript(executeScript(php_array, 'array_keys'), function(){ array_keys(input, searchValue, argStrict) });
// $.getScript(executeScript(php_array, 'array_map'), function(){ array_map(callback) });
// $.getScript(executeScript(php_array, 'array_merge'), function(){ array_merge(arr1, arr2) });
// $.getScript(executeScript(php_array, 'array_merge_recursive'), function(){ array_merge_recursive(arr1, arr2) });
// $.getScript(executeScript(php_array, 'array_multisort'), function(){ array_multisort(arr) });
// $.getScript(executeScript(php_array, 'array_pad'), function(){ array_pad(input, padSize, padValue) });
// $.getScript(executeScript(php_array, 'array_pop'), function(){ array_pop(inputArr) });
// $.getScript(executeScript(php_array, 'array_product'), function(){ array_product(input) });
// $.getScript(executeScript(php_array, 'array_push'), function(){ array_push(inputArr) });
// $.getScript(executeScript(php_array, 'array_rand'), function(){ array_rand(array, num) });
// $.getScript(executeScript(php_array, 'array_reduce'), function(){ array_reduce(aInput, callback) });
// $.getScript(executeScript(php_array, 'array_replace'), function(){ array_replace(arr) });
// $.getScript(executeScript(php_array, 'array_replace_recursive'), function(){ array_replace_recursive(arr) });
// $.getScript(executeScript(php_array, 'array_reverse'), function(){ array_reverse(array, preserveKeys) });
// $.getScript(executeScript(php_array, 'array_search'), function(){ array_search(needle, haystack, argStrict) });
// $.getScript(executeScript(php_array, 'array_shift'), function(){ array_shift(inputArr) });
// $.getScript(executeScript(php_array, 'array_slice'), function(){ array_slice(arr, offst, lgth, preserveKeys) });

// $.getScript(executeScript(php_array, 'array_splice'), function(){ array_splice(param) });
// $.getScript(executeScript(php_array, 'array_sum'), function(){ array_sum(param) });
// $.getScript(executeScript(php_array, 'array_udiff'), function(){ array_udiff(param) });
// $.getScript(executeScript(php_array, 'array_udiff_assoc'), function(){ array_udiff_assoc(param) });
// $.getScript(executeScript(php_array, 'array_udiff_uassoc'), function(){ array_udiff_uassoc(param) });
// $.getScript(executeScript(php_array, 'array_uintersect'), function(){ array_uintersect(param) });
// $.getScript(executeScript(php_array, 'array_uintersect_uassoc'), function(){ array_uintersect_uassoc(param) });
// $.getScript(executeScript(php_array, 'array_unique'), function(){ array_unique(param) });
// $.getScript(executeScript(php_array, 'array_unshift'), function(){ array_unshift(param) });
// $.getScript(executeScript(php_array, 'array_values'), function(){ array_values(param) });
// $.getScript(executeScript(php_array, 'array_walk'), function(){ array_walk(param) });
// $.getScript(executeScript(php_array, 'array_walk_recursive'), function(){ array_walk_recursive(param) });
// $.getScript(executeScript(php_array, 'arsort'), function(){ arsort(param) });
// $.getScript(executeScript(php_array, 'asort'), function(){ asort(param) });
// $.getScript(executeScript(php_array, 'count'), function(){ count(param) });
// $.getScript(executeScript(php_array, 'current'), function(){ current(param) });
// $.getScript(executeScript(php_array, 'each'), function(){ each(param) });
// $.getScript(executeScript(php_array, 'end'), function(){ end(param) });
// $.getScript(executeScript(php_array, 'in_array'), function(){ in_array(param) });
// $.getScript(executeScript(php_array, 'key'), function(){ key(param) });
// $.getScript(executeScript(php_array, 'krsort'), function(){ krsort(param) });
// $.getScript(executeScript(php_array, 'ksort'), function(){ ksort(param) });
// $.getScript(executeScript(php_array, 'natcasesort'), function(){ natcasesort(param) });
// $.getScript(executeScript(php_array, 'natsort'), function(){ natsort(param) });
// $.getScript(executeScript(php_array, 'next'), function(){ next(param) });
// $.getScript(executeScript(php_array, 'pos'), function(){ pos(param) });
// $.getScript(executeScript(php_array, 'prev'), function(){ prev(param) });
// $.getScript(executeScript(php_array, 'range'), function(){ range(param) });
// $.getScript(executeScript(php_array, 'reset'), function(){ reset(param) });
// $.getScript(executeScript(php_array, 'rsort'), function(){ rsort(param) });
// $.getScript(executeScript(php_array, 'shuffle'), function(){ shuffle(param) });
// $.getScript(executeScript(php_array, 'sizeof'), function(){ sizeof(param) });
// $.getScript(executeScript(php_array, 'sort'), function(){ sort(param) });
// $.getScript(executeScript(php_array, 'uasort'), function(){ uasort(param) });
// $.getScript(executeScript(php_array, 'uksort'), function(){ uksort(param) });
// $.getScript(executeScript(php_array, 'usort'), function(){ usort(param) });

var php_bc = require_script + '/php/bc'

// $.getScript(executeScript(php_bc, 'bcadd'), function(){ bcadd(param) });
// $.getScript(executeScript(php_bc, 'bccomp'), function(){ bccomp(param) });
// $.getScript(executeScript(php_bc, 'bcdiv'), function(){ bcdiv(param) });
// $.getScript(executeScript(php_bc, 'bcmul'), function(){ bcmul(param) });
// $.getScript(executeScript(php_bc, 'bcround'), function(){ bcround(param) });
// $.getScript(executeScript(php_bc, 'bcscale'), function(){ bcscale(param) });
// $.getScript(executeScript(php_bc, 'bcsub'), function(){ bcsub(param) });

var php_ctype = require_script + '/php/ctype'

// $.getScript(executeScript(php_ctype, 'ctype_alnum'), function(){ ctype_alnum(param) });
// $.getScript(executeScript(php_ctype, 'ctype_alpha'), function(){ ctype_alpha(param) });
// $.getScript(executeScript(php_ctype, 'ctype_cntrl'), function(){ ctype_cntrl(param) });
// $.getScript(executeScript(php_ctype, 'ctype_digit'), function(){ ctype_digit(param) });
// $.getScript(executeScript(php_ctype, 'ctype_graph'), function(){ ctype_graph(param) });
// $.getScript(executeScript(php_ctype, 'ctype_lower'), function(){ ctype_lower(param) });
// $.getScript(executeScript(php_ctype, 'ctype_print'), function(){ ctype_print(param) });
// $.getScript(executeScript(php_ctype, 'ctype_punct'), function(){ ctype_punct(param) });
// $.getScript(executeScript(php_ctype, 'ctype_space'), function(){ ctype_space(param) });
// $.getScript(executeScript(php_ctype, 'ctype_upper'), function(){ ctype_upper(param) });
// $.getScript(executeScript(php_ctype, 'ctype_xdigit'), function(){ ctype_xdigit(param) });

var php_datetime = require_script + '/php/datetime'

// $.getScript(executeScript(php_datetime, 'checkdate'), function(){ checkdate(param) });
// $.getScript(executeScript(php_datetime, 'date'), function(){ date(param) });
// $.getScript(executeScript(php_datetime, 'date_parse'), function(){ date_parse(param) });
// $.getScript(executeScript(php_datetime, 'getdate'), function(){ getdate(param) });
// $.getScript(executeScript(php_datetime, 'gettimeofday'), function(){ gettimeofday(param) });
// $.getScript(executeScript(php_datetime, 'gmdate'), function(){ gmdate(param) });
// $.getScript(executeScript(php_datetime, 'gmmktime'), function(){ gmmktime(param) });
// $.getScript(executeScript(php_datetime, 'gmstrftime'), function(){ gmstrftime(param) });
// $.getScript(executeScript(php_datetime, 'idate'), function(){ idate(param) });
// $.getScript(executeScript(php_datetime, 'microtime'), function(){ microtime(param) });
// $.getScript(executeScript(php_datetime, 'mktime'), function(){ mktime(param) });
// $.getScript(executeScript(php_datetime, 'strftime'), function(){ strftime(param) });
// $.getScript(executeScript(php_datetime, 'strptime'), function(){ strptime(param) });
// $.getScript(executeScript(php_datetime, 'strtotime'), function(){ strtotime(param) });
// $.getScript(executeScript(php_datetime, 'time'), function(){ time(param) });

var php_exec = require_script + '/php/exec'

// $.getScript(executeScript(php_exec, 'escapeshellarg'), function(){ time(param) });

var php_filesystem = require_script + '/php/filesystem'

// $.getScript(executeScript(php_filesystem, 'basename'), function(){ basename(param) });
// $.getScript(executeScript(php_filesystem, 'dirname'), function(){ dirname(param) });
// $.getScript(executeScript(php_filesystem, 'file_get_contents'), function(){ file_get_contents(param) });
// $.getScript(executeScript(php_filesystem, 'pathinfo'), function(){ pathinfo(param) });
// $.getScript(executeScript(php_filesystem, 'realpath'), function(){ realpath(param) });

var php_funchand = require_script + '/php/funchand'

// $.getScript(executeScript(php_funchand, 'call_user_func'), function(){ call_user_func(param) });
// $.getScript(executeScript(php_funchand, 'call_user_func_array'), function(){ call_user_func_array(param) });
// $.getScript(executeScript(php_funchand, 'create_function'), function(){ create_function(param) });
// $.getScript(executeScript(php_funchand, 'function_exists'), function(){ function_exists(param) });
// $.getScript(executeScript(php_funchand, 'get_defined_functions'), function(){ get_defined_functions(param) });

var php_i18n = require_script + '/php/i18n'

// $.getScript(executeScript(php_i18n, 'i18n_loc_get_default'), function(){ i18n_loc_get_default(param) });
// $.getScript(executeScript(php_i18n, 'i18n_loc_set_default'), function(){ i18n_loc_set_default(param) });

var php_info = require_script + '/php/info'

// $.getScript(executeScript(php_info, 'assert_options'), function(){ assert_options(param) });
// $.getScript(executeScript(php_info, 'getenv'), function(){ getenv(param) });
// $.getScript(executeScript(php_info, 'ini_get'), function(){ ini_get(param) });
// $.getScript(executeScript(php_info, 'ini_set'), function(){ ini_set(param) });
// $.getScript(executeScript(php_info, 'set_time_limit'), function(){ set_time_limit(param) });
// $.getScript(executeScript(php_info, 'version_compare'), function(){ version_compare(param) });

var php_json = require_script + '/php/json'

// $.getScript(executeScript(php_json, 'json_decode'), function(){ json_decode(param) });
// $.getScript(executeScript(php_json, 'json_encode'), function(){ json_encode(param) });
// $.getScript(executeScript(php_json, 'json_last_error'), function(){ json_last_error(param) });

var php_math = require_script + '/php/math'

// $.getScript(executeScript(php_math, 'abs'), function(){ abs(param) });
// $.getScript(executeScript(php_math, 'acos'), function(){ acos(param) });
// $.getScript(executeScript(php_math, 'acosh'), function(){ acosh(param) });
// $.getScript(executeScript(php_math, 'asin'), function(){ asin(param) });
// $.getScript(executeScript(php_math, 'asinh'), function(){ asinh(param) });
// $.getScript(executeScript(php_math, 'atan'), function(){ atan(param) });
// $.getScript(executeScript(php_math, 'atan2'), function(){ atan2(param) });
// $.getScript(executeScript(php_math, 'atanh'), function(){ atanh(param) });
// $.getScript(executeScript(php_math, 'base_convert'), function(){ base_convert(param) });
// $.getScript(executeScript(php_math, 'bindec'), function(){ bindec(param) });
// $.getScript(executeScript(php_math, 'ceil'), function(){ ceil(param) });
// $.getScript(executeScript(php_math, 'cos'), function(){ cos(param) });
// $.getScript(executeScript(php_math, 'cosh'), function(){ cosh(param) });
// $.getScript(executeScript(php_math, 'decbin'), function(){ decbin(param) });
// $.getScript(executeScript(php_math, 'dechex'), function(){ dechex(param) });
// $.getScript(executeScript(php_math, 'decoct'), function(){ decoct(param) });
// $.getScript(executeScript(php_math, 'deg2rad'), function(){ deg2rad(param) });
// $.getScript(executeScript(php_math, 'exp'), function(){ exp(param) });
// $.getScript(executeScript(php_math, 'expm1'), function(){ expm1(param) });
// $.getScript(executeScript(php_math, 'floor'), function(){ floor(param) });
// $.getScript(executeScript(php_math, 'fmod'), function(){ fmod(param) });
// $.getScript(executeScript(php_math, 'getrandmax'), function(){ getrandmax(param) });
// $.getScript(executeScript(php_math, 'hexdec'), function(){ hexdec(param) });
// $.getScript(executeScript(php_math, 'hypot'), function(){ hypot(param) });
// $.getScript(executeScript(php_math, 'is_finite'), function(){ is_finite(param) });
// $.getScript(executeScript(php_math, 'is_infinite'), function(){ is_infinite(param) });
// $.getScript(executeScript(php_math, 'is_nan'), function(){ is_nan(param) });
// $.getScript(executeScript(php_math, 'lcg_value'), function(){ lcg_value(param) });
// $.getScript(executeScript(php_math, 'log'), function(){ log(param) });
// $.getScript(executeScript(php_math, 'log10'), function(){ log10(param) });
// $.getScript(executeScript(php_math, 'log1p'), function(){ log1p(param) });
// $.getScript(executeScript(php_math, 'max'), function(){ max(param) });
// $.getScript(executeScript(php_math, 'min'), function(){ min(param) });
// $.getScript(executeScript(php_math, 'mt_getrandmax'), function(){ mt_getrandmax(param) });
// $.getScript(executeScript(php_math, 'mt_rand'), function(){ mt_rand(param) });
// $.getScript(executeScript(php_math, 'octdec'), function(){ octdec(param) });
// $.getScript(executeScript(php_math, 'pi'), function(){ pi(param) });
// $.getScript(executeScript(php_math, 'pow'), function(){ pow(param) });
// $.getScript(executeScript(php_math, 'rad2deg'), function(){ rad2deg(param) });
// $.getScript(executeScript(php_math, 'rand'), function(){ rand(param) });
// $.getScript(executeScript(php_math, 'round'), function(){ round(param) });
// $.getScript(executeScript(php_math, 'sin'), function(){ sin(param) });
// $.getScript(executeScript(php_math, 'sinh'), function(){ sinh(param) });
// $.getScript(executeScript(php_math, 'sqrt'), function(){ sqrt(param) });
// $.getScript(executeScript(php_math, 'tan'), function(){ tan(param) });
// $.getScript(executeScript(php_math, 'tanh'), function(){ tanh(param) });

var php_misc = require_script + '/php/misc'

// $.getScript(executeScript(php_misc, 'pack'), function(){ pack(param) });
// $.getScript(executeScript(php_misc, 'uniqid'), function(){ uniqid(param) });

var php_net_gopher = require_script + '/php/net-gopher'

// $.getScript(executeScript(php_net_gopher, 'gopher_parsedir'), function(){ gopher_parsedir(param) });

var php_network = require_script + '/php/network'

// $.getScript(executeScript(php_network, 'inet_ntop'), function(){ inet_ntop(param) });
// $.getScript(executeScript(php_network, 'inet_pton'), function(){ inet_pton(param) });
// $.getScript(executeScript(php_network, 'ip2long'), function(){ ip2long(param) });
// $.getScript(executeScript(php_network, 'long2ip'), function(){ long2ip(param) });
// $.getScript(executeScript(php_network, 'setcookie'), function(){ setcookie(param) });
// $.getScript(executeScript(php_network, 'setrawcookie'), function(){ setrawcookie(param) });

var php_pcre = require_script + '/php/pcre'

// $.getScript(executeScript(php_pcre, 'preg_quote'), function(){ preg_quote(param) });
// $.getScript(executeScript(php_pcre, 'sql_regcase'), function(){ sql_regcase(param) });

var php_strings = require_script + '/php/strings'

// $.getScript(executeScript(php_strings, 'addcslashes'), function(){ addcslashes(param) });
// $.getScript(executeScript(php_strings, 'addslashes'), function(){ addslashes(param) });
// $.getScript(executeScript(php_strings, 'bin2hex'), function(){ bin2hex(param) });
// $.getScript(executeScript(php_strings, 'chop'), function(){ chop(param) });
// $.getScript(executeScript(php_strings, 'chr'), function(){ chr(param) });
// $.getScript(executeScript(php_strings, 'chunk_split'), function(){ chunk_split(param) });
// $.getScript(executeScript(php_strings, 'convert_cyr_string'), function(){ convert_cyr_string(param) });
// $.getScript(executeScript(php_strings, 'convert_uuencode'), function(){ convert_uuencode(param) });
// $.getScript(executeScript(php_strings, 'count_chars'), function(){ count_chars(param) });
// $.getScript(executeScript(php_strings, 'crc32'), function(){ crc32(param) });
// $.getScript(executeScript(php_strings, 'echo'), function(){ echo(param) });
// $.getScript(executeScript(php_strings, 'explode'), function(){ explode(param) });
// $.getScript(executeScript(php_strings, 'get_html_translation_table'), function(){ get_html_translation_table(param) });
// $.getScript(executeScript(php_strings, 'hex2bin'), function(){ hex2bin(param) });
// $.getScript(executeScript(php_strings, 'html_entity_decode'), function(){ html_entity_decode(param) });
// $.getScript(executeScript(php_strings, 'htmlentities'), function(){ htmlentities(param) });
// $.getScript(executeScript(php_strings, 'htmlspecialchars'), function(){ htmlspecialchars(param) });
// $.getScript(executeScript(php_strings, 'htmlspecialchars_decode'), function(){ htmlspecialchars_decode(param) });
// $.getScript(executeScript(php_strings, 'implode'), function(){ implode(param) });
// $.getScript(executeScript(php_strings, 'join'), function(){ join(param) });
// $.getScript(executeScript(php_strings, 'lcfirst'), function(){ lcfirst(param) });
// $.getScript(executeScript(php_strings, 'levenshtein'), function(){ levenshtein(param) });
// $.getScript(executeScript(php_strings, 'localeconv'), function(){ localeconv(param) });
// $.getScript(executeScript(php_strings, 'ltrim'), function(){ ltrim(param) });
// $.getScript(executeScript(php_strings, 'md5'), function(){ md5(param) });
// $.getScript(executeScript(php_strings, 'md5_file'), function(){ md5_file(param) });
// $.getScript(executeScript(php_strings, 'metaphone'), function(){ metaphone(param) });
// $.getScript(executeScript(php_strings, 'money_format'), function(){ money_format(param) });
// $.getScript(executeScript(php_strings, 'nl2br'), function(){ nl2br(param) });
// $.getScript(executeScript(php_strings, 'nl_langinfo'), function(){ nl_langinfo(param) });
// $.getScript(executeScript(php_strings, 'number_format'), function(){ number_format(param) });
// $.getScript(executeScript(php_strings, 'ord'), function(){ ord(param) });
// $.getScript(executeScript(php_strings, 'parse_str'), function(){ parse_str(param) });
// $.getScript(executeScript(php_strings, 'printf'), function(){ printf(param) });
// $.getScript(executeScript(php_strings, 'quoted_printable_decode'), function(){ quoted_printable_decode(param) });
// $.getScript(executeScript(php_strings, 'quoted_printable_encode'), function(){ quoted_printable_encode(param) });
// $.getScript(executeScript(php_strings, 'quotemeta'), function(){ quotemeta(param) });
// $.getScript(executeScript(php_strings, 'rtrim'), function(){ rtrim(param) });
// $.getScript(executeScript(php_strings, 'setlocale'), function(){ setlocale(param) });
// $.getScript(executeScript(php_strings, 'sha1'), function(){ sha1(param) });
// $.getScript(executeScript(php_strings, 'sha1_file'), function(){ sha1_file(param) });
// $.getScript(executeScript(php_strings, 'similar_text'), function(){ similar_text(param) });
// $.getScript(executeScript(php_strings, 'soundex'), function(){ soundex(param) });
// $.getScript(executeScript(php_strings, 'split'), function(){ split(param) });
// $.getScript(executeScript(php_strings, 'sprintf'), function(){ sprintf(param) });
// $.getScript(executeScript(php_strings, 'sscanf'), function(){ sscanf(param) });
// $.getScript(executeScript(php_strings, 'str_getcsv'), function(){ str_getcsv(param) });
// $.getScript(executeScript(php_strings, 'str_ireplace'), function(){ str_ireplace(param) });
// $.getScript(executeScript(php_strings, 'str_pad'), function(){ str_pad(param) });
// $.getScript(executeScript(php_strings, 'str_repeat'), function(){ str_repeat(param) });
// $.getScript(executeScript(php_strings, 'str_replace'), function(){ str_replace(param) });
// $.getScript(executeScript(php_strings, 'str_rot13'), function(){ str_rot13(param) });
// $.getScript(executeScript(php_strings, 'str_shuffle'), function(){ str_shuffle(param) });
// $.getScript(executeScript(php_strings, 'str_split'), function(){ str_split(param) });
// $.getScript(executeScript(php_strings, 'str_word_count'), function(){ str_word_count(param) });
// $.getScript(executeScript(php_strings, 'strcasecmp'), function(){ strcasecmp(param) });
// $.getScript(executeScript(php_strings, 'strchr'), function(){ strchr(param) });
// $.getScript(executeScript(php_strings, 'strcmp'), function(){ strcmp(param) });
// $.getScript(executeScript(php_strings, 'strcoll'), function(){ strcoll(param) });
// $.getScript(executeScript(php_strings, 'strcspn'), function(){ strcspn(param) });
// $.getScript(executeScript(php_strings, 'strip_tags'), function(){ strip_tags(param) });
// $.getScript(executeScript(php_strings, 'stripos'), function(){ stripos(param) });
// $.getScript(executeScript(php_strings, 'stripslashes'), function(){ stripslashes(param) });
// $.getScript(executeScript(php_strings, 'stristr'), function(){ stristr(param) });
// $.getScript(executeScript(php_strings, 'strlen'), function(){ strlen(param) });
// $.getScript(executeScript(php_strings, 'strnatcasecmp'), function(){ strnatcasecmp(param) });
// $.getScript(executeScript(php_strings, 'strnatcmp'), function(){ strnatcmp(param) });
// $.getScript(executeScript(php_strings, 'strncasecmp'), function(){ strncasecmp(param) });
// $.getScript(executeScript(php_strings, 'strncmp'), function(){ strncmp(param) });
// $.getScript(executeScript(php_strings, 'strpbrk'), function(){ strpbrk(param) });
// $.getScript(executeScript(php_strings, 'strpos'), function(){ strpos(param) });
// $.getScript(executeScript(php_strings, 'strrchr'), function(){ strrchr(param) });
// $.getScript(executeScript(php_strings, 'strrev'), function(){ strrev(param) });
// $.getScript(executeScript(php_strings, 'strripos'), function(){ strripos(param) });
// $.getScript(executeScript(php_strings, 'strrpos'), function(){ strrpos(param) });
// $.getScript(executeScript(php_strings, 'strspn'), function(){ strspn(param) });
// $.getScript(executeScript(php_strings, 'strstr'), function(){ strstr(param) });
// $.getScript(executeScript(php_strings, 'strtok'), function(){ strtok(param) });
// $.getScript(executeScript(php_strings, 'strtolower'), function(){ strtolower(param) });
// $.getScript(executeScript(php_strings, 'strtoupper'), function(){ strtoupper(param) });
// $.getScript(executeScript(php_strings, 'strtr'), function(){ strtr(param) });
// $.getScript(executeScript(php_strings, 'substr'), function(){ substr(param) });
// $.getScript(executeScript(php_strings, 'substr_compare'), function(){ substr_compare(param) });
// $.getScript(executeScript(php_strings, 'substr_count'), function(){ substr_count(param) });
// $.getScript(executeScript(php_strings, 'substr_replace'), function(){ substr_replace(param) });
// $.getScript(executeScript(php_strings, 'trim'), function(){ trim(param) });
// $.getScript(executeScript(php_strings, 'ucfirst'), function(){ ucfirst(param) });
// $.getScript(executeScript(php_strings, 'ucwords'), function(){ ucwords(param) });
// $.getScript(executeScript(php_strings, 'vprintf'), function(){ vprintf(param) });
// $.getScript(executeScript(php_strings, 'vsprintf'), function(){ vsprintf(param) });
// $.getScript(executeScript(php_strings, 'wordwrap'), function(){ wordwrap(param) });

var php_url = require_script + '/php/url'

// $.getScript(executeScript(php_url, 'base64_decode'), function(){ base64_decode(param) });
// $.getScript(executeScript(php_url, 'base64_encode'), function(){ base64_encode(param) });
// $.getScript(executeScript(php_url, 'http_build_query'), function(){ http_build_query({id: id, id_2: id_2}, '', '&'); });
// $.getScript(executeScript(php_url, 'parse_url'), function(){ parse_url(param) });
// $.getScript(executeScript(php_url, 'rawurldecode'), function(){ rawurldecode(param) });
// $.getScript(executeScript(php_url, 'rawurlencode'), function(){rawurlencode(param) });
// $.getScript(executeScript(php_url, 'urldecode'), function(){ urldecode(param) });
// $.getScript(executeScript(php_url, 'urlencode'), function(){ urlencode(param) });

var php_var = require_script + '/php/var'

// $.getScript(executeScript(php_var, 'boolval'), function(){ boolval(param) });
// $.getScript(executeScript(php_var, 'doubleval'), function(){ doubleval(param) });
// $.getScript(executeScript(php_var, 'empty'), function(){ empty(param) });
// $.getScript(executeScript(php_var, 'floatval'), function(){ floatval(param) });
// $.getScript(executeScript(php_var, 'gettype'), function(){ gettype(param) });
// $.getScript(executeScript(php_var, 'intval'), function(){ intval(param) });
// $.getScript(executeScript(php_var, 'is_array'), function(){ is_array(param) });
// $.getScript(executeScript(php_var, 'is_binary'), function(){ is_binary(param) });
// $.getScript(executeScript(php_var, 'is_bool'), function(){ is_bool(param) });
// $.getScript(executeScript(php_var, 'is_buffer'), function(){ is_buffer(param) });
// $.getScript(executeScript(php_var, 'is_callable'), function(){ is_callable(param) });
// $.getScript(executeScript(php_var, 'is_double'), function(){ is_double(param) });
// $.getScript(executeScript(php_var, 'is_float'), function(){ is_float(param) });
// $.getScript(executeScript(php_var, 'is_int'), function(){ is_int(param) });
// $.getScript(executeScript(php_var, 'is_integer'), function(){ is_integer(param) });
// $.getScript(executeScript(php_var, 'is_long'), function(){ is_long(param) });
// $.getScript(executeScript(php_var, 'is_null'), function(){ is_null(param) });
// $.getScript(executeScript(php_var, 'is_numeric'), function(){ is_numeric(param) });
// $.getScript(executeScript(php_var, 'is_object'), function(){ is_object(param) });
// $.getScript(executeScript(php_var, 'is_real'), function(){ is_real(param) });
// $.getScript(executeScript(php_var, 'is_scalar'), function(){ is_scalar(param) });
// $.getScript(executeScript(php_var, 'is_string'), function(){ is_string(param) });
// $.getScript(executeScript(php_var, 'is_unicode'), function(){ is_unicode(param) });
// $.getScript(executeScript(php_var, 'isset'), function(){ isset(param) });
// $.getScript(executeScript(php_var, 'print_r'), function(){ print_r(param) });
// $.getScript(executeScript(php_var, 'serialize'), function(){ serialize(param) });
// $.getScript(executeScript(php_var, 'strval'), function(){ strval(param) });
// $.getScript(executeScript(php_var, 'unserialize'), function(){ unserialize(param) });
// $.getScript(executeScript(php_var, 'var_dump'), function(){ var_dump(param) });
// $.getScript(executeScript(php_var, 'var_export'), function(){ var_export(param) });

var php_xdiff = require_script + '/php/xdiff'

// $.getScript(executeScript(php_xdiff, 'xdiff_string_diff'), function(){ php_xdiff(param) });
// $.getScript(executeScript(php_xdiff, 'xdiff_string_patch'), function(){ php_xdiff(param) });

var php_xml = require_script + '/php/xml'

// $.getScript(executeScript(php_xml, 'utf8_decode'), function(){ utf8_decode(param) });
// $.getScript(executeScript(php_xml, 'utf8_encode'), function(){ utf8_encode(param) });

var c_math = require_script + '/c/math'

// $.getScript(executeScript(c_math, 'abs'), function(){ abs(param) });
// $.getScript(executeScript(c_math, 'frexp'), function(){ frexp(param) });

var python_string = require_script + '/python/string'

// $.getScript(executeScript(python_string, 'capwords'), function(){ capwords(param) });

var ruby_math = require_script + '/ruby/math'

// $.getScript(executeScript(ruby_math, 'acos'), function(){ acos(param) });
 
