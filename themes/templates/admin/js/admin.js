function resourcePut(resource, id) {
    var fields = $( ":input" || ":textarea" || ":checkbox" || ":radio" || "select").serializeArray();
    //console.log( fields )
    $.post(admin_uri + admin_dir + 'resource-put/' + resource + '/' + id, fields, function (response) {
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
    $.post(admin_uri + admin_dir + "resource-post", {resource: resource, csrf: csrf}, function (response) {
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
    $.post(admin_uri + admin_dir + "resource-delete", {id: id, resource: resource, csrf: csrf}, function (response) {
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
    $.post(admin_uri + admin_dir + "template-install", {alias: alias, csrf: csrf}, function (response) {
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
    $.post(admin_uri + admin_dir + "template-buy", {alias: alias, csrf: csrf}, function (response) {
        var data = JSON && JSON.parse(response) || $.parseJSON(response)
        if(data.status == 200)
        {
            window.location.reload()
            } else if(data.status == 400) {
            OneNotify(data.title, data.text, data.color)
        }
    }),"json"
}

function templateActivate(alias, dir) {
    var csrf = $("#csrf").val()
    $.post(admin_uri + admin_dir + "template-activate", {alias: alias, dir: dir, csrf: csrf}, function (response) {
        var data = JSON && JSON.parse(response) || $.parseJSON(response)
        if(data.status == 200)
        {
            window.location.reload()
            } else if(data.status == 400) {
            OneNotify(data.title, data.text, data.color)
        }
    }),"json"
}

function templateDelete(alias, dir) {
    var csrf = $("#csrf").val()
    $.post(admin_uri + admin_dir + "template-delete", {alias: alias, dir: dir, csrf: csrf}, function (response) {
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
    $.post(admin_uri + admin_dir + "order-activate", {alias: alias, csrf: csrf}, function (response) {
        var data = JSON && JSON.parse(response) || $.parseJSON(response)
        if(data.status == 200)
        {
            window.location.reload()
            } else if(data.status == 400) {
            OneNotify(data.title, data.text, data.color)
        }
    }),"json"
}


function packageBuy(vendor_package) {
    var csrf = $("#csrf").val()
    $.post(admin_uri + admin_dir + "package-buy/" + vendor_package, {csrf: csrf}, function (response) {
        var data = JSON && JSON.parse(response) || $.parseJSON(response)
        if(data.status == 200)
        {
            window.location.reload()
            } else if(data.status == 400) {
            OneNotify(data.title, data.text, data.color)
        }
    }),"json"
}

function packageActivate(vendor_package) {
    var csrf = $("#csrf").val()
    $.post(admin_uri + admin_dir + "package-activate/" + vendor_package, {csrf: csrf}, function (response) {
        var data = JSON && JSON.parse(response) || $.parseJSON(response)
        if(data.status == 200)
        {
            window.location.reload()
            } else if(data.status == 400) {
            OneNotify(data.title, data.text, data.color)
        }
    }),"json"
}

function packageDeactivate(vendor_package) {
    var csrf = $("#csrf").val()
    $.post(admin_uri + admin_dir + "package-deactivate/" + vendor_package, {csrf: csrf}, function (response) {
        var data = JSON && JSON.parse(response) || $.parseJSON(response)
        if(data.status == 200)
        {
            window.location.reload()
            } else if(data.status == 400) {
            OneNotify(data.title, data.text, data.color)
        }
    }),"json"
}

function packageDelete(vendor_package) {
    var csrf = $("#csrf").val()
    $.post(admin_uri + admin_dir + "package-delete/" + vendor_package, {csrf: csrf}, function (response) {
        var data = JSON && JSON.parse(response) || $.parseJSON(response)
        if(data.status == 200)
        {
            window.location.reload()
            } else if(data.status == 400) {
            OneNotify(data.title, data.text, data.color)
        }
    }),"json"
}
 