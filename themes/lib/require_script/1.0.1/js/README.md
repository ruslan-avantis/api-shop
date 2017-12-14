# require_script.js
```html
<script src="https://cdn.jommimart.com/require_script/require_script.js"></script>
```

## Подключение .js скриптов после загрузки страницы
#### Теперь вы можете:

  Подключать скрипты и функции только когда нужно !
  
  Подключать библиотеку [locutus](https://github.com/kvz/locutus/)
  
  Подключать скрипты с `cloudflare` или других `cdn`

## require_script.js - Подружит PHP программиста с javaScript

Например вам нужно подключить библиотеку bootstrap версии 4.0.0-alpha.6

require_script предлагает несколько методов

### function executeScript
```js
$.getScript(executeScript(lib, "bootstrap", "4.0.0-alpha.6", false, true, false, "cloudflare"), 
	function(){
		// Получить доступ к подключаемому скрипту вы можете только внутри функции getScript
	});
```
Подключаем функцию array_change_key_case
```js
$.getScript(executeScript(php_array, 'array_change_key_case'), function(){
	// Получить доступ к функциям подключаемого скрипта вы можете только внутри функции getScript
	array_change_key_case(array, cs)
});
```
Поключаем несколько функций
```js
$.when(
    $.getScript(executeScript(php_url, 'http_build_query'),
    $.getScript(executeScript(php_url, 'urldecode'),
    $.getScript(executeScript(php_url, 'urlencode'),
    $.Deferred(function( deferred ){
        $( deferred.resolve )
    })
).done(function(){

    //place your code here, the scripts are all loaded
    
var request = {
    id: 1, 
    product_id: 10001, 
    amount: 5, 
    price: "100.01", 
    iname: "Orlando", 
    fname: "Mithci", 
    phone: "1234567890", 
    email: "info@example.com", 
    city: "New York", 
    street: "42nd Street", 
    build: "123", 
    apart: "12", 
    description: "Hello world"
}

    var response = http_build_query(request, '', '&')
 
    alert(response)

})
```

### function requireScript `Мы еще работаем над этой функцией !`

Пишем какую то функцию и в ней нам нужно вызвать функцию http_build_query

После функции functionCallback дважды выпоним requireScript и подключим необходимые функции
```js
function functionCallback() {
	var request = {
	    id: 1, 
	    product_id: 10001, 
	    amount: 5, 
	    price: "100.01", 
	    iname: "Orlando", 
	    fname: "Mithci", 
	    phone: "1234567890", 
	    email: "info@example.com", 
	    city: "New York", 
	    street: "42nd Street", 
	    build: "123", 
	    apart: "12", 
	    description: "Hello world"
	}
	
	var response = http_build_query(request, '', '&')
 
	alert(response)
}
```
Дальше подключаем функции http_build_query и urlencode. Функция requireScript принимает масcив json с папаметрами и название функции которой нужно вернуть callback. В нашем случае оставляем пустым что бы подключить функции глобально.
```js
requireScript({"dir": php_url, "name": "http_build_query"}, '')
requireScript({"dir": php_url, "name": "urlencode"}, '')
```
 
