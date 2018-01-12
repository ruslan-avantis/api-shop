function Load() {
    $("#loader-div").html("<div class=\'loader-fon\'><div class=\'loader-wrapper\'><div class=\'loader\'></div></div></div>");
    $("#loader-link").html("<div class=\'load\'>Загружаю файлы ...</div>");
}
function checkStore(id) {
    var csrf = $("#csrf").val()
    $.post("/install-store", {id: id, csrf: csrf}, function (response) {
        var data = JSON && JSON.parse(response) || $.parseJSON(response)
        if(data.status == 200)
        {
            window.location.reload()
        } else if(data.status == 400) {
            OneNotify(data.title, data.text, data.color)
        }
    }),"json"
}

function checkTemplate(id) {
    var csrf = $("#csrf").val()
    var host = $("#host").val()
    var uri = $("#uri-" + id).val()
    var dir = $("#dir-" + id).val()
    $.post("/install-template", {id: id, csrf: csrf, host: host, uri: uri, dir: dir}, function (response) {
        var data = JSON && JSON.parse(response) || $.parseJSON(response)
        if(data.status == 200)
        {
            // Сохраняем массив с локализацией у юзера
            try {
                window.location.reload()
            } catch(e){
                window.location.reload()
            }
        } else if(data.status == 400) {
            OneNotify(data.title, data.text, data.color)
        }
    }),"json"
}

function checkInSeller() {
    $('.error_message').remove()
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
    setDb('phone', phone)
    setDb('email', email)
    setDb('iname', iname)
    setDb('fname', fname)
    $.post("/install-in-seller", {email: email, phone: phone, password: password, iname: iname, fname: fname, csrf: csrf}, function (response) {
        var data = $.parseJSON(response)
        if(data.status == 200) {
            window.location.reload()
        }
        else if(data.status == 400) {
            OneNotify(data.title, data.text, data.color)
        }
    }
    ),"json"
}
 
function checkApiKey() {
    $('.error_message').remove()
    checkData('public_key', 'public_key', false)
    var public_key = $("#public_key").val()
    var csrf = $("#csrf").val()
    $.post("/install-api-key", {public_key: public_key, csrf: csrf}, function (response) {
        var data = $.parseJSON(response)
        if(data.status == 200) {
            window.location.reload()
        }
        else if(data.status == 400) {
            OneNotify(data.title, data.text, data.color)
        }
    }
    ),"json"
}

function checkKey() {
    var csrf = $("#csrf").val()
    $.post("/install-key", {csrf: csrf}, function (response) {
        var data = $.parseJSON(response)
        if(data.status == 200) {
            window.location.reload()
        }
        else if(data.status == 400) {
            OneNotify(data.title, data.text, data.color)
        }
    }
    ),"json"
}

function checkNoKey() {
    var csrf = $("#csrf").val()
    $.post("/install-no-key", {csrf: csrf}, function (response) {
        var data = $.parseJSON(response)
        if(data.status == 200) {
            window.location.reload()
        }
        else if(data.status == 400) {
            OneNotify(data.title, data.text, data.color)
        }
    }
    ),"json"
}