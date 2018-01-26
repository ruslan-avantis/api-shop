function resourcePut(resource, id) {
    var fields = $( ":input" || ":textarea" || ":checkbox" || ":radio" || "select").serializeArray();
	//console.log( fields )
    $.post(admin_dir + 'resource-put/' + resource + '/' + id, fields, function (response) {
        var data = JSON && JSON.parse(response) || $.parseJSON(response)
        if(data.status == 200)
        {
            window.location.reload()
        } else if(data.status == 400) {
            OneNotify(data.title, data.text, data.color)
        }
    }),"json"
}
 
function resourcePost(resource) {
    var csrf = $("#csrf").val()
    $.post(admin_dir + "resource-post", {resource: resource, csrf: csrf}, function (response) {
        var data = JSON && JSON.parse(response) || $.parseJSON(response)
        if(data.status == 200)
        {
            window.location.reload()
            } else if(data.status == 400) {
            OneNotify(data.title, data.text, data.color)
        }
    }),"json"
}
 
function resourceDelete(resource, id) {
    var csrf = $("#csrf").val()
    $.post(admin_dir + "resource-delete", {id: id, resource: resource, csrf: csrf}, function (response) {
        var data = JSON && JSON.parse(response) || $.parseJSON(response)
        if(data.status == 200)
        {
            window.location.reload()
            } else if(data.status == 400) {
            OneNotify(data.title, data.text, data.color)
        }
    }),"json"
}
 
function templateInstall(alias) {
    var csrf = $("#csrf").val()
    $.post(admin_dir + "template-install", {alias: alias, csrf: csrf}, function (response) {
        var data = JSON && JSON.parse(response) || $.parseJSON(response)
        if(data.status == 200)
        {
            window.location.reload()
            } else if(data.status == 400) {
            OneNotify(data.title, data.text, data.color)
        }
    }),"json"
}
 
function templateBuy(alias) {
    var csrf = $("#csrf").val()
    $.post(admin_dir + "template-buy", {alias: alias, csrf: csrf}, function (response) {
        var data = JSON && JSON.parse(response) || $.parseJSON(response)
        if(data.status == 200)
        {
            window.location.reload()
            } else if(data.status == 400) {
            OneNotify(data.title, data.text, data.color)
        }
    }),"json"
}

function templateActivate(alias, name) {
    var csrf = $("#csrf").val()
    $.post(admin_dir + "template-activate", {alias: alias, name: name, csrf: csrf}, function (response) {
        var data = JSON && JSON.parse(response) || $.parseJSON(response)
        if(data.status == 200)
        {
            window.location.reload()
            } else if(data.status == 400) {
            OneNotify(data.title, data.text, data.color)
        }
    }),"json"
}

function templateDelete(alias, name) {
    var csrf = $("#csrf").val()
    $.post(admin_dir + "template-delete", {alias: alias, name: name, csrf: csrf}, function (response) {
        var data = JSON && JSON.parse(response) || $.parseJSON(response)
        if(data.status == 200)
        {
            window.location.reload()
            } else if(data.status == 400) {
            OneNotify(data.title, data.text, data.color)
        }
    }),"json"
}

function orderActivate(alias) {
    var csrf = $("#csrf").val()
    $.post(admin_dir + "order-activate", {alias: alias, csrf: csrf}, function (response) {
        var data = JSON && JSON.parse(response) || $.parseJSON(response)
        if(data.status == 200)
        {
            window.location.reload()
            } else if(data.status == 400) {
            OneNotify(data.title, data.text, data.color)
        }
    }),"json"
}
