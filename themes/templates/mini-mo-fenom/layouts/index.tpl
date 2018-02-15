{if $template.layouts.helper.header}{include ($template.layouts.helper.header)}{/if}
{if $template.layouts.helper.nav}{include ($template.layouts.helper.nav)}{/if}
{if $template.layouts.helper.carousel}{include ($template.layouts.helper.carousel)}{/if}
{if $template.layouts.helper.title}{include ($template.layouts.helper.title)}{/if}
{if $content.products}
<section id="product-list" class="product-list">
<div class="control-background bg-white">
<div class="control-text-3 text-black">
<br>
<div class="control-background bg-white">
<div class="control-text-3 text-black">
<div class="album">
<div class="container">
<div class="row text-center cards">
{foreach $content.products as $item}
{if $template.layouts.helper.products}{include ($template.layouts.helper.products)}{/if}
{/foreach}
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</section>
{/if}
{if $template.layouts.helper.newsletter}{include ($template.layouts.helper.newsletter)}{/if}
{if $template.layouts.helper.footer}{include ($template.layouts.helper.footer)}{/if}