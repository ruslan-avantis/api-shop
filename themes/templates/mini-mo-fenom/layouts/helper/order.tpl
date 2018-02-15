<div id="block_order_{$item.product_id}" class="text-center" style="width: 450px; display: none;">

<div id="ok-{$item.product_id}">
<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 text-center padding_10">
{if $item.price}
<span class="control-text text-red"><span class="product-price-new font_16 text_bold">
Цена товара: {$item.price} <span class="">{$config.shortname}</span>
</span></span>
{/if}
        
<div class="font_16 text_bold padding_7">Заказ оформить очень просто</div>

<div class="form-group row">
<div class="col-6 col-xl-6 col-lg-6 col-md-6 col-sm-6 col-xs-6 text-center">
<input class="form-control form-control-sm br-green-sm text-center padding_7" type="text" placeholder="Имя" value="{$session.iname}" name="iname" id="iname-{$item.product_id}">
</div>
<div class="col-6 col-xl-6 col-lg-6 col-md-6 col-sm-6 col-xs-6 text-center">
<input class="form-control form-control-sm br-green-sm text-center padding_7" type="text" placeholder="Фамилия" value="{$session.fname}" name="fname" id="fname-{$item.product_id}">
</div>
</div>

<div class="form-group row">
<div class="col-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12 text-center">
<input class="form-control form-control-sm br-green-sm text-center padding_7" type="text" placeholder="Телефон" value="{$session.phone}" name="phone" id="phone-{$item.product_id}">
</div>
</div>

<div class="form-group row">
<div class="col-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12 text-center">
<input class="form-control form-control-sm br-green-sm text-center padding_7" type="text" placeholder="e-mail" value="{$session.email}" name="email" id="email-{$item.product_id}">
</div>
</div>

<div class="form-group row">
<div class="col-6 col-xl-6 col-lg-6 col-md-6 col-sm-6 col-xs-6 text-center">
<input class="form-control form-control-sm br-green-sm text-center padding_7" type="text" placeholder="Город" value="" name="city_name" id="city_name-{$item.product_id}">
</div>
<div class="col-6 col-xl-6 col-lg-6 col-md-6 col-sm-6 col-xs-6 text-center">
<input class="form-control form-control-sm br-green-sm text-center padding_7" type="text" placeholder="Улица" value="" name="street" id="street-{$item.product_id}">
</div>
</div>

<div class="form-group row">
<div class="col-6 col-xl-6 col-lg-6 col-md-6 col-sm-6 col-xs-6 text-center">
<input class="form-control form-control-sm br-green-sm text-center padding_7" type="text" placeholder="Дом" value="" name="build" id="build-{$item.product_id}">
</div>
<div class="col-6 col-xl-6 col-lg-6 col-md-6 col-sm-6 col-xs-6 text-center">
<input class="form-control form-control-sm br-green-sm text-center padding_7" type="text" placeholder="Квартира" value="" name="apart" id="apart-{$item.product_id}">
</div>
</div>

<div class="form-group row">
<div class="col-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12 text-center">
<textarea class="form-control form-control-sm br-green-sm" rows="3" name="messages" id="description-{$item.product_id}" placeholder="" maxlength="150"></textarea>
</div>
</div>

<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 text-red-md"><div id="div-{$item.product_id}"><br></div></div>

<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
<a href="#" class="btn btn-lg bg-red text-white btn-hover-effects" onClick="newOrder({$item.product_id});">Оформить заказ <i class="fa fa-sign-in" aria-hidden="true"></i></a>
</div>

</div>

</div>

</div>
