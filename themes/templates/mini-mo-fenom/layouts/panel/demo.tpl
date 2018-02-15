<script>
$(function(){
var sources = $('[data-source]');
var folders = $('[data-folder]');
var elements = $('[data-element]');
jQuery.each(sources, function(){
var file = '' + $(this).data('source') + '' + $(this).data('folder') + '' + $(this).data('element') + '';
$(this).load(file);
});
});
</script>
<div data-source="" data-folder="/themes/elements/demo-panel/" data-element="demo-panel.html" class="demo-body demo-navbar demo-slider-1 demo-slider-2 demo-slider-3 demo-slider-4 demo-section-1 demo-section-2 demo-marketing demo-product-list demo-contact demo-newsletter demo-footer-header demo-footer"></div>