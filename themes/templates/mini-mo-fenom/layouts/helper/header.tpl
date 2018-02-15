<!DOCTYPE html>
<html lang="{$site.language}" class="fixed-header fixed-footer-none" style="font-family:'Open Sans',sans-serif; font-size:14px;">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1">
<link rel="icon" href="/favicon.ico">
{if $head.title or $page == 'category_list'}
{if $head.title}<title>{$head.title} | {$head.host}</title>{/if}
{if $head.keywords}<meta name="keywords" content="{$head.keywords}, {$head.host}">{/if}
{if $head.description}<meta name="description" content="{$head.description} | {$head.host}">{/if}
{if $head.host and $head.path}<link name="canonical" href="//{$head.host}{$head.path}">{/if}
{if $head.robots}<meta name="robots" content="{$head.robots}">{/if}
{if $head.og_url}<meta property="og:url" content="//{$head.host}{$head.path}">{/if}
{if $head.og_locale}<meta property="og:locale" content="{$head.og_locale}">{/if}
{if $head.og_type}<meta property="og:type" content="{$head.og_type}">{/if}
{if $head.og_title}<meta property="og:title" content="{$head.og_title} | {$head.host}">{/if}
{if $head.og_description}<meta property="og:description" content="{$head.og_description} | {$head.host}">{/if}
{if $head.og_image}<meta property="og:image" content="{$head.og_image}">{/if}
{elseif $content.title}
{if $content.title}<title>{$content.title} | {$head.host}</title>{/if} 
{if $content.keywords}<meta name="keywords" content="{$content.keywords}, {$head.host}">{/if} 
{if $content.description}<meta name="description" content="{$content.description} | {$head.host}">{/if} 
{if $content.host and $head.path}<link name="canonical" href="//{$head.host}{$head.path}">{/if} 
{if $content.robots}<meta name="robots" content="{$content.robots}">{/if} 
{if $content.og_url}<meta property="og:url" content="//{$content.host}{$content.path}">{/if} 
{if $content.og_locale}<meta property="og:locale" content="{$content.og_locale}">{/if} 
{if $content.og_type}<meta property="og:type" content="{$content.og_type}">{/if} 
{if $content.og_title}<meta property="og:title" content="{$content.og_title} | {$head.host}">{/if} 
{if $content.og_description}<meta property="og:description" content="{$content.og_description} | {$head.host}">{/if} 
{if $content.og_image}<meta property="og:image" content="{$content.og_image}">{/if} 
{/if}
</head>
<body data-spy="scroll" data-target="#navbar-example" class="bg-cover navbar-block carousel-block-100 product-list-block h1-description-block marketing-block newsletter-block footer-block footer-header-block section_1-block" style="font-family: 'Open Sans',sans-serif;">
<div id="body">
<input type="hidden" name="csrf" id="csrf" value="{$token}">