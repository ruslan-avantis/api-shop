function resourcePut(resource, id) {
    var fields = $( ":input" || ":textarea" || ":checkbox" || ":radio" || "select").serializeArray();
	//console.log( fields )
    $.post('/admin/resource-put/' + resource + '/' + id, fields, function (response) {
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
    $.post("/admin/resource-post", {resource: resource, csrf: csrf}, function (response) {
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
    $.post("/admin/resource-delete", {id: id, resource: resource, csrf: csrf}, function (response) {
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
    $.post("/admin/template-install", {alias: alias, csrf: csrf}, function (response) {
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
    $.post("/admin/template-buy", {alias: alias, csrf: csrf}, function (response) {
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
    $.post("/admin/template-activate", {alias: alias, name: name, csrf: csrf}, function (response) {
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
    $.post("/admin/template-delete", {alias: alias, name: name, csrf: csrf}, function (response) {
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
    $.post("/admin/order-activate", {alias: alias, csrf: csrf}, function (response) {
        var data = JSON && JSON.parse(response) || $.parseJSON(response)
        if(data.status == 200)
        {
            window.location.reload()
            } else if(data.status == 400) {
            OneNotify(data.title, data.text, data.color)
        }
    }),"json"
}
