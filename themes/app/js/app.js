// global lib
var templates_lib = '/themes/lib'
// api-shop lib
var api_shop = '/themes/app'

// Проверка доступности indexedDB
var indexedDB = window.indexedDB || window.mozIndexedDB || window.webkitIndexedDB || window.msIndexedDB
// Также могут отличаться и window.IDB* objects: Transaction, KeyRange и тд
var IDBTransaction = window.IDBTransaction || window.webkitIDBTransaction || window.msIDBTransaction
var IDBKeyRange = window.IDBKeyRange || window.webkitIDBKeyRange || window.msIDBKeyRange
// Открываем базу данных MyTestDatabase
var db
var request = indexedDB.open("apiShop", 1)
request.onupgradeneeded = function(event) {
    db = event.target.result
    db.createObjectStore("languages", {keyPath: "id"})
    db.createObjectStore("user", {keyPath: "id"})
}

// localStorage
var localDb = window.localStorage
// Примеры использования
// localDb.setItem('key', 'value');
// var data = localDb.getItem('key');
// localDb.removeItem('key');
// localDb.clear();

// sessionStorage
var sessionDb = window.sessionStorage
// Примеры использования
// sessionDb.setItem('key', 'value');
// var data = sessionDb.getItem('key');
// sessionDb.removeItem('key');
// sessionDb.clear();

// Функция проверки доступности localStorage и sessionStorage
function storageAvailable(type) {
    try {
        var storage = window[type],
            x = '__storage_test__'
        storage.setItem(x, x)
        storage.removeItem(x)
        return true
    }
    catch(e) {
        return e instanceof DOMException && (
            // everything except Firefox
            e.code === 22 ||
            // Firefox
            e.code === 1014 ||
            // test name field too, because code might not be present
            // everything except Firefox
            e.name === 'QuotaExceededError' ||
            // Firefox
            e.name === 'NS_ERROR_DOM_QUOTA_REACHED') &&
            // acknowledge QuotaExceededError only if there's something already stored
            storage.length !== 0
    }
}

// Пишем данные в localStorage, sessionStorage, indexedDB
function setDb(key, data) {
    if (storageAvailable('localStorage')) {
        localDb.setItem(key, data)
    }
    if (storageAvailable('sessionStorage')) {
        sessionDb.setItem(key, data)
    }
    // Сейчас мы только пишем в indexedDB
    // Разбираюсь как удобно получать данные
    request.onerror = function(event) {
        //alert("Почему Вы не позволяете моему веб-приложению использовать IndexedDB?!")
        return null
    }
    request.onsuccess = function(event) {
        db = event.target.result
        var tx = db.transaction(key, "readwrite")
        var store = tx.objectStore(key)
        for (var i in data) {
            try {
                //console.log(i, cData[i])
                store.put(data[i])
            } catch(e){
                //alert(e.name)
            }
        }
    }
}

function getDb(key) {
    if (storageAvailable('localStorage')) {
        return localDb.getItem(key)
    }
    else if (storageAvailable('sessionStorage')) {
        return sessionDb.getItem(key)
    }
    else {
        return null
    }
}

function removeDb(key) {
    if (storageAvailable('localStorage')) {
        localDb.removeItem(key)
        return true
    }
    else if (storageAvailable('sessionStorage')) {
        sessionDb.removeItem(key)
        return true
    }
    else {
        return null
    }
}

function clearDb() {
    try {
        localDb.clear()
        sessionDb.clear()
        return true
    } catch(e){
        // alert(e.name)
        return null
    }
}

// Получаем локализацию в js
var languages = new Object()
languages = getLanguages()

function createCookie(name, value, days) {
    if (days) {
        var date = new Date()
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 365))
        var expires = "; expires=" + date.toGMTString()
    } else var expires = ""
    document.cookie = name + "=" + value + expires + "; path=/"
}

function readCookie(name) {
    var nameEQ = name + "="
    var ca = document.cookie.split(';')
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i]
        while (c.charAt(0) == ' ') c = c.substring(1, c.length)
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length)
    }
    return null
}

function eraseCookie(name) {
    createCookie(name, "", -1)
}

function isValidJSON(src) {
    var filtered = src
    filtered = filtered.replace(/\\["\\\/bfnrtu]/g, '@')
    filtered = filtered.replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']')
    filtered = filtered.replace(/(?:^|:|,)(?:\s*\[)+/g, '')
    return (/^[\],:{}\s]*$/.test(filtered))
};

function OneNotify(title, text, type, icon, addclass) {
    new PNotify({title: title, text: text, type: type, icon: icon, addclass: addclass})
}

function jsonNotify(json) {
    new PNotify(json)
}
    
function getLanguages() {
    if(getDb('languages') != null) {
        var json = getDb('languages')
        var obj = JSON && JSON.parse(json) || $.parseJSON(json)
        return obj
    } else {
        setLanguage(2)
    }
}

function lang(id) {
    var getLang = $.grep(languages, function(e){ return e.id == id })
    return getLang[0].name
}

function setLanguage(id) {
    var csrf = $("#csrf").val()
    $.post("/language", {csrf: csrf, id: id}, function (response) {
        var data = JSON && JSON.parse(response) || $.parseJSON(response)
        if(data.status == "OK")
        {
            // Сохраняем массив с локализацией у юзера
            try {
                setDb('languages', JSON.stringify(data.languages))
                window.location.reload()
            } catch(e){
                //testDb('languages')
                window.location.reload()
            }
        } else if(data.status == "NO") {
            OneNotify(data.title, data.text, data.color)
        }
    }),"json"
}

function checkIn() {
    $('.error_message').remove();
    checkPhone('phone', lang(200), false)
    checkPassword('password', lang(201), password_expression)
    checkEmail('email', lang(161), email_expression)
    checkData('fname', lang(57), word_expression)
    checkData('iname', lang(58), word_expression)
    var phone = $("#phone").intlTelInput("getNumber")
    var email = $("#email").val()
    var password = $("#password").val()
    var iname = $("#iname").val()
    var fname = $("#fname").val()
    var csrf = $("#csrf").val()
    $.post("/check-in", {email: email, phone: phone, password: password, iname: iname, fname: fname, csrf: csrf}, function (response) {
        var data = $.parseJSON(response)
        if(data.status == "OK") {
            window.location = '/'
        }
        else if(data.status == "NO") {
            OneNotify(data.title, data.text, data.color)
        }
    }
    ),"json"
    }

function login() {
    $('.error_message').remove();
    checkPhone('phone', lang(200), false)
    checkPassword('password', lang(201), password_expression)
    checkEmail('email', lang(161), email_expression)
    var phone = $("#phone").intlTelInput("getNumber")
    var email = $("#email").val()
    var password = $("#password").val()
    var csrf = $("#csrf").val()
    $.post("/login", {email: email, phone: phone, password: password, csrf: csrf}, function (response) {
        var data = $.parseJSON(response)
        if(data.status == "OK") {
            window.location = '/'
        }
        else if(data.status == "NO") {
            OneNotify(data.title, data.text, data.color)
        }
    }
    ),"json"
}

function logout() {
    var csrf = $("#csrf").val()
    $.post("/logout", {csrf: csrf}, function (response) {
        var data = $.parseJSON(response)
        if(data.status == "OK")
        {
            window.location = '/'
        }
        else if(data.status == "NO") {
            OneNotify(data.title, data.text, data.color)
        }
    }
    ),"json"
}
 