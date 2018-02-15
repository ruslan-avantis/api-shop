<style>
.dropdown-submenu {
position: relative;
}
.dropdown-submenu>.dropdown-menu {
top: 0;
left: 100%;
margin-top: -6px;
margin-left: -1px;
-webkit-border-radius: 0 6px 6px 6px;
-moz-border-radius: 0 6px 6px;
border-radius: 0 6px 6px 6px;
}
.dropdown-submenu:hover>.dropdown-menu {
display: block;
}
.dropdown-submenu>a:after {
display: block;
content: " ";
float: right;
width: 0;
height: 0;
border-color: transparent;
border-style: solid;
border-width: 5px 0 5px 5px;
border-left-color: #ccc;
margin-top: 5px;
margin-right: -10px;
}
.dropdown-submenu:hover>a:after {
border-left-color: #fff;
}
.dropdown-submenu.pull-left {
float: none;
}
.dropdown-submenu.pull-left>.dropdown-menu {
left: -100%;
margin-left: 10px;
-webkit-border-radius: 6px 0 6px 6px;
-moz-border-radius: 6px 0 6px 6px;
border-radius: 6px 0 6px 6px;
}
</style>
{foreach $menu as $menu}
<div class="dropdown">
{if $menu.submenu != null}
<button class="btn btn-md bg-red btn-hover-effects text-white dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
{if $session.language == "ua" and $menu.title_ua != ''}
{$menu.title_ua}
{elseif $session.language == "en" and $menu.title_en != ''}
{$menu.title_en}
{elseif $session.language == "ru" and $menu.title_ru != ''}
{$menu.title_ru}
{elseif $session.language == "de" and $menu.title_de != ''}
{$menu.title_de}
{else}
{$menu.title}
{/if}
</button>
<ul class="dropdown-menu a-grey-xxxl a-hover-red-lg multi-level" role="menu" aria-labelledby="dropdownMenu">
{foreach $menu.submenu as $submenu}
{if $submenu.subsubmenu != null}
<li class="dropdown-submenu">
<a  class="dropdown-item" tabindex="-1" href="{$submenu.url}">
{if $session.language == "ua" and $submenu.title_ua != ''}
{$submenu.title_ua}
{elseif $session.language == "en" and $submenu.title_en != ''}
{$submenu.title_en}
{elseif $session.language == "ru" and $submenu.title_ru != ''}
{$submenu.title_ru}
{elseif $session.language == "de" and $submenu.title_de != ''}
{$submenu.title_de}
{else}
{$submenu.title}
{/if}
</a>
<ul class="dropdown-menu a-grey-xxxl a-hover-red-lg">
{foreach $submenu.subsubmenu as $subsubmenu}
<li class="dropdown-item"><a href="{$subsubmenu.url}">
{if $session.language == "ua" and $subsubmenu.title_ua != ''}
{$subsubmenu.title_ua}
{elseif $session.language == "en" and $subsubmenu.title_en != ''}
{$subsubmenu.title_en}
{elseif $session.language == "ru" and $subsubmenu.title_ru != ''}
{$subsubmenu.title_ru}
{elseif $session.language == "de" and $subsubmenu.title_de != ''}
{$subsubmenu.title_de}
{else}
{$subsubmenu.title}
{/if}
</a>
</li>
{/foreach}
</ul>
</li>
{else}
<li class="dropdown-item"><a href="{$submenu.url}">
{if $session.language == "ua" and $submenu.title_ua != ''}
{$submenu.title_ua}
{elseif $session.language == "en" and $submenu.title_en != ''}
{$submenu.title_en}
{elseif $session.language == "ru" and $submenu.title_ru != ''}
{$submenu.title_ru}
{elseif $session.language == "de" and $submenu.title_de != ''}
{$submenu.title_de}
{else}
{$submenu.title}
{/if}
</a></li>
{/if}
{/foreach}
</ul>
{else}
<a class="btn btn-md text-white" href="{$menu.url}">
{if $session.language == "ua" and $menu.title_ua != ''}
{$menu.title_ua}
{elseif $session.language == "en" and $menu.title_en != ''}
{$menu.title_en}
{elseif $session.language == "ru" and $menu.title_ru != ''}
{$menu.title_ru}
{elseif $session.language == "de" and $menu.title_de != ''}
{$menu.title_de}
{else}
{$menu.title}
{/if}
</a>
{/if}
</div>
{/foreach}