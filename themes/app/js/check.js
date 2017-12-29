var word_expression = new RegExp(/^[а-яА-Яa-zA-ZÀ-ÿ0-9а-щА-ЩЬьЮюЯяЇїІіЄєҐґ\/\n/:;.!'"?%&*-+=_,№#$€@\ \-]{1,500}$/);
var check_expression_new = new RegExp(/^[а-яА-Яa-zA-ZÀ-ÿ0-9а-щА-ЩЬьЮюЯяЇїІіЄєҐґ\/\n/:;.!'"?%&*-+=_,№#$€@\ \-]{1,500}$/);
var check_expression = new RegExp(/^[а-яА-Яa-zA-ZÀ-ÿ0-9а-щА-ЩЬьЮюЯяЇїІіЄєҐґ\/\n/:;.!'"?%&*-+=_,№#$€@\ \-]{1,500}$/);
var email_expression = new RegExp(/^[-0-9a-z_\.]+@[-0-9a-z_\.]+\.[a-z]{2,6}$/i);
var phone_expression = new RegExp(/^[\(\)\[\]\s\\\/\-0-9\+]{5,50}/i);
var phone_pattern = new RegExp(/^((8|0|\+\d{1,2})[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$/i);
var password_expression = new RegExp(/^[^\s]{8,25}$/);
var code_expression = new RegExp(/[0-9]{2}-[0-9]{2}-[0-9]{2}$/);
var url_expression = new RegExp(/[-a-zA-Z0-9@:%_\+.~#?&//=]{2,256}\.[a-z]{2,4}\b(\/[-a-zA-Z0-9@:%_\+.~#?&//=]*)?/gi);
 
function checkData(fieldname, message_item, check_expression)
{    
    var data_string = $("#" + fieldname).val();
    if(data_string.length == 0)
    {
        $("#" + fieldname + " ").after(
            "<div class=error_message id=" + fieldname + "_di ><i class='fa fa-info-circle text-red-md' aria-hidden='true'></i> " 
            + message_item 
            + " " 
            + lang(43) 
            + "</div>"
        );
        errorStatus = true;
        $('#' + fieldname).css('background', '#FFF9C5');
    }
    else
    if(check_expression != false)
    {
        var result = check_expression.test(data_string);
        if(result == false)
        {
            $("#" + fieldname).after(
                "<div class=error_message id=" + fieldname + "_di ><i class='fa fa-info-circle text-red-md' aria-hidden='true'></i> " 
                + lang(41) 
                + " " 
                + message_item 
                + "</div>"
            );
            errorStatus = true;
            $('#' + fieldname).css('background', '#FFF9C5');
        }
        else
        {
            $('#' + fieldname).css('background', '#fff');
        }
    }
    else
    {
        $('#' + fieldname).css('background', '#fff');
    }
}
 
function checkPassword(fieldname, message_item, password_expression)
{
    var data_string = $('#' + fieldname).val();
    if(data_string.length == 0)
    {
        $('#' + fieldname + "-status").html(
            '<div class=error_message id=' + fieldname + '_div><i class="fa fa-info-circle text-red-md" aria-hidden="true"></i> ' 
            + lang(42) 
            + ' '
            + message_item 
            + ' ' 
            + lang(43) 
            + '</div>'
        );
        errorStatus = true;
        $('#' + fieldname).css('background', '#FFF9C5');
    }
    else
    if(password_expression != false)
    {
        var result = password_expression.test(data_string);
        if(result == false)
        {
            $('#' + fieldname + "-status").html(
                '<div class=error_message id=' + fieldname + '_divx><i class="fa fa-info-circle text-red-md" aria-hidden="true"></i> '
                + message_item
                + ': '
                + data_string+' ' 
                + lang(44) 
                + ' </div>'
            );
            errorStatus = true;
            $('#' + fieldname).css('background', '#FFF9C5');
        }
    }
    else
    {
        $('#' + fieldname).css('background', '#fff');
    }
}
 
function checkPhone(fieldname, message_item, phone_expression)
{
    var data_string = $('#' + fieldname).val();
    if(data_string.length == 0)
    {
        $('#' + fieldname + "-status").html(
            '<div class=error_message id=' + fieldname + '_di ><i class="fa fa-info-circle text-red-md" aria-hidden="true"></i> ' 
            + lang(42) 
            + ' '
            + message_item
            + ' ' 
            + lang(43) 
            + '</div>'
        );
        errorStatus = true;
        $('#' + fieldname).css('background', '#FFF9C5');
    }
    else
    if(phone_expression != false)
    {
        var result = phone_expression.test(data_string);
        if(result == false)
        {
            $('#' + fieldname + "-status").html(
                '<div class=error_message id=' + fieldname + '_di ><i class="fa fa-info-circle text-red-md" aria-hidden="true"></i> ' 
                + lang(41) 
                + ' '
                + message_item
                + '</div>'
            );
            errorStatus = true;
            $('#' + fieldname).css('background', '#FFF9C5');
        }
    }
    else
    {
        $('#' + fieldname).css('background', '#fff');
    }
}
 
function checkEmail(fieldname, message_item, email_expression)
{
    var data_string = $('#' + fieldname).val();
    if(data_string.length == 0)
    {
        $('#' + fieldname + "-status").html(
            '<div class=error_message id=' + fieldname + '_di ><i class="fa fa-info-circle text-red-md" aria-hidden="true"></i> ' 
            + lang(42) 
            + ' '
            + message_item
            + ' ' 
            + lang(43) 
            + '</div>'
        );
        errorStatus = true;
        $('#' + fieldname).css('background', '#FFF9C5');
    }
    else
    if(email_expression != false)
    {
        var result = email_expression.test(data_string);
        if(result == false)
        {
            $('#' + fieldname + "-status").html(
                '<div class=error_message id=' + fieldname + '_di ><i class="fa fa-info-circle text-red-md" aria-hidden="true"></i> ' 
                + lang(41) 
                + ' '
                + message_item
                + '</div>'
            );
            errorStatus = true;
            $('#' + fieldname).css('background', '#FFF9C5');
        }
    }
    else
    {
        $('#' + fieldname).css('background', '#fff');
    }
}
 