<section class="control-background-3 bg-red" $itemscope="" $itemtype="https://schema.org/Organization">
<meta $itemprop="name" content="">
<meta $itemprop="address" content="">
<meta $itemprop="telephone" content="">
<meta $itemprop="logo" content="">
<meta $itemprop="url" content="">
<div class="sps sps--abv">
<div class="navbar-box">
<div id="navbar-example" class="menu">
<div class="navbar-inverse">
<div class="container">
<nav class="navbar navbar-toggleable-lg padding_0">
<div class="navbar-header text-left-xl-lg-md animated bounceInLeft">
<a class="navbar-brand logo" $itemprop="url" href="/" title="{$config.title} - {$config.description}">
<img $itemprop="logo" class="logo-api" src="/themes/templates/{$template.name}/img/logo.svg" alt="{$config.title} - {$config.description}" title="{$config.title} - {$config.description}">
</a>
</div>
<div class="nav-nav a-white a-hover-light-grey-md">
<ul class="nav justify-content-end animated bounceInRight" role="tablist">
{if $template.layouts.helper.menu}{include ($template.layouts.helper.menu)}{/if}
<li class="nav-item"> 
<div class="btn-group btn-red">
<button type="button" class="btn btn-md btn-hover-effects text-white dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
{if $session.language == "ua"}
<img src="/themes/lib/phone/flags/ua.png" class="position-left" alt=""> Українська
{elseif $session.language == "en"}
<img src="/themes/lib/phone/flags/gb.png" class="position-left" alt=""> English
{elseif $session.language == "ru"}
<img src="/themes/lib/phone/flags/ru.png" class="position-left" alt=""> Русский
{elseif $session.language == "de"}
<img src="/themes/lib/phone/flags/de.png" class="position-left" alt=""> Deutsch
{else}
<img src="/themes/lib/phone/flags/ru.png" class="position-left" alt=""> Русский
{/if}
</button>
<div class="dropdown-menu a-grey-xxxl a-hover-red-lg">
<a class="dropdown-item" href="#" onClick="setLanguage(2);"><img src="/themes/lib/phone/flags/ua.png" alt=""> Українська</a>
<a class="dropdown-item" href="#" onClick="setLanguage(1);"><img src="/themes/lib/phone/flags/ru.png" alt=""> Русский</a>
<a class="dropdown-item" href="#" onClick="setLanguage(3);"><img src="/themes/lib/phone/flags/gb.png" alt=""> English</a>
<a class="dropdown-item" href="#" onClick="setLanguage(4);"><img src="/themes/lib/phone/flags/de.png" alt=""> Deutsch</a>
</div>
</div> 
</li>
<li class="nav-item">&nbsp;&nbsp;</li>
<li class="nav-item">
{if $session.authorize == 1}
<div class="btn-group btn-red">
<a href="#" class="btn btn-md btn-hover-effects text-white dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
{$session.iname} {$session.fname}
</a>
<div class="dropdown-menu a-grey-xxxl a-hover-red-lg">
{if $session.role_id == 100}
<a class="dropdown-item" href="{$admin_uri}{$routers.admin.index.route}">{$language.785}</a>
<a class="dropdown-item" href="{$admin_uri}{$routers.admin.all.route}template">{$language.815}</a>
<a class="dropdown-item" href="{$admin_uri}{$routers.admin.all.route}config">{$language.663}</a>
{/if} 
<a class="dropdown-item" href="#" onClick="logout();">{$language.318}</a>
</div>
</div>
{else}
<a class="nav-link" href="/sign-in"><span class="text-white">{$language.203}</span></a>
{/if} 
</li>

</ul>
</div>
</nav>
</div>
</div>
</div>
</div>
</div>
</section>