<section id="h1-description">
<div class="control-background-2 bg-black">
<div class="h1-description text-center">
<div class="container padding_21">
<div class="text-red-md">

{if $head.page == "category"}
<h1 class="h1-description-heading">{if $page.title}{$page.title}{/if}</h1>
{/if}

{if $head.page != "category"}
<div class="font_22 text_bold"><div class="control-text-3 text-white">{$language.82}</div></div>
<h1 class="h1-description-heading">{$config.settings.site.title}</h1>
<div class="font_22 text_bold"><div class="control-text-3 text-white">{$language.88}</div></div>
<div class="font_22 text_bold"><div class="control-text-3 text-white">{$language.89}</div></div>
{/if}

</div>

{if $head.page != "category"}
<div class="padding_10">
<span class="control-link-a a-white a-hover-black">
<span class="control-button-3 btn-red">
<a href="/category" class="btn btn-lg btn-hover-effects"><i class="fa fa-download" aria-hidden="true"></i> {$language.402}</a>
</span>
</span>
<span class="control-link-a-3 a-white a-hover-red">
<span class="control-button btn-black">
<a href="/faq.html" class="btn btn-lg btn-hover-effects"><i class="fa fa-github" aria-hidden="true"></i> {$language.311}</a>
</span>
</span>
</div>
{/if}

</div>
</div>
</div>
</section>