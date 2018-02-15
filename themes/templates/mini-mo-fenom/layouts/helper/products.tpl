<div class="product-blocks" $itemscope="" $itemtype="https://schema.org/Product">
<div class="bg-white">
<div class="control-text-3 text-black">
<div class="product-block">
<div class="product-img">
<a $itemprop="url" href="{if $item.url}{$item.url}{/if}" class="link-product-img">
{if $item.image.1}
<img $itemprop="image" class="product-img-primary" src="/{$item.image.1}" alt="{$item.name}">
<img $itemprop="image" content="/{$item.image.1}">
{else}
<img $itemprop="image" class="product-img-primary" src="/{$item.no_image}" alt="{$item.name}">
{/if}
{if $item.image.2}
<img $itemprop="image" class="product-img-alt" src="/{$item.image.2}" alt="{$item.name}">
{else}
<img $itemprop="image" class="product-img-alt" src="/{$item.no_image}" alt="{$item.name}">
{/if}
</a>
</div>
<div class="product-name padding_top_28" style="z-index: 10; position: relative; display: block;">
{if $item.name}
<a $itemprop="url" href="{$item.url}" titel="{$item.name}" class="link-product-name"><span class="control-text-3 text-black font_18" $itemprop="name">{$item.name}</span></a>
<meta $itemprop="name" content="{$item.name}" />
{/if}
<span class="control-countdown countdown-red-md">
<div class="count-down">
<span class="countdown-lastest" data-y="{$item.y}" data-m="{$item.m}" data-d="{$item.d}" data-h="{$item.h}" data-i="{$item.i}" data-s="{$item.s}">
</span>
</div>
</span>
<div class="product-price" $itemprop="offers" $itemscope="" $itemtype="https://schema.org/Offer">
{if $item.oldprice}
<span class="control-text text-grey-md"><span class="product-price-old font_18 text_normal">{$item.oldprice} {$item.shortname}</span></span>
{/if}
{if $item.price}
<input type="hidden" value="{$item.product_id}" name="product_id" id="product_id-{$item.product_id}">
<input type="hidden" value="{$item.price}" name="price" id="price-{$item.product_id}">
<input type="hidden" value="1" name="num" id="num-{$item.product_id}">
<span class="control-text-5 text-black"><span class="product-price-new font_20 text_bold" $itemprop="price">{$item.price} <span $itemprop="priceCurrency" content="{$item.currency}" class="">{$item.shortname}</span></span></span>
<meta $itemprop="price" content="{$item.price}">
{/if}
</div>
<div class="product-link">
<span class="control-button-3 btn-red">
<span class="control-link-a a-white a-hover-light-grey-md">
<a class="fancybox btn btn-md btn-hover-effects btn-red" href="#block_order_{$item.product_id}"><i class="fa fa-cart-plus" aria-hidden="true"></i> {$language.1}</a>
{include ('helper/order.tpl')}
</span>
</span>
<span class="control-button-2 btn-black">
<span class="control-link-a a-white a-hover-light-grey-md">
<a href="#" onClick="addToCart({$item.product_id});" class="btn btn-md btn-hover-effects"><i class="fa fa-balance-scale" aria-hidden="true"></i> {$language.3}</a>
</span>
</span>
</div>
</div>
</div>
</div>
</div>
</div>