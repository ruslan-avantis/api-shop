<footer id="footer" class="control-background-2 bg-red">
<div class="footer-top wow fadeInUp" data-wow-duration="1000ms" data-wow-delay="300ms">
<div class="container text-center">
<div class="footer-logo">
<a href="/"><img class="img-responsive" src="/themes/templates/{$template.name}/img/logo.svg" alt=""></a>
</div>
<div class="social-icons">
<ul>
<li><a class="facebook" href="#"><i class="fa fa-facebook"></i></a></li>
<li><a class="linkedin" href="#"><i class="fa fa-linkedin"></i></a></li>
<li><a class="twitter" href="#"><i class="fa fa-twitter"></i></a></li>
<li><a class="dribbble" href="#"><i class="fa fa-dribbble"></i></a></li>
<li><a class="tumblr" href="#"><i class="fa fa-tumblr-square"></i></a></li>
<li><a class="envelope" href="#"><i class="fa fa-envelope"></i></a></li>
</ul>
</div>
<div class="payment-icons-list">
<ul>
<li><img src="/themes/templates/{$template.name}/img/payment/visa-straight-32px.png" alt="" title="Pay with Visa"></li>
<li><img src="/themes/templates/{$template.name}/img/payment/mastercard-straight-32px.png" alt="" title="Pay with Mastercard"></li>
<li><img src="/themes/templates/{$template.name}/img/payment/paypal-straight-32px.png" alt="" title="Pay with Paypal"></li>
<li><img src="/themes/templates/{$template.name}/img/payment/visa-electron-straight-32px.png" alt="" title="Pay with Visa-electron"></li>
<li><img src="/themes/templates/{$template.name}/img/payment/maestro-straight-32px.png" alt="" title="Pay with Maestro"></li>
<li><img src="/themes/templates/{$template.name}/img/payment/discover-straight-32px.png" alt="" title="Pay with Discover"></li>
</ul>
</div>
</div>
</div>
<div class="footer">
<div class="control-background-3 bg-black">
<div class="control-text text-white">
<div class="container">
<div class="row">
<div class="col-12 col-lg-12 col-md-12 col-sm-12 col-xs-12 text-center">
<div class="copyright-text padding_10 font_12">
Copyright © {$config.copyright.date} <a class="control-link link-white" href="//{$head.host}">{$config.title}</a>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</footer>
</div>
<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Open+Sans" />
<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css" rel="stylesheet">
<link href="https://cdn.joomimart.com/prism/css/prism.min.css" rel="stylesheet">
<link href="https://cdn.pllano.com/joomi.css/1.0.2/css/joomi-css.min.css" rel="stylesheet">
<link href="https://cdn.joomimart.com/joomi-colors/1.0.2/css/joomi-colors.css" rel="stylesheet">
<link href="/themes/lib/pnotify/3.2.0/css/pnotify.min.css" rel="stylesheet">
<link href="/themes/templates/{$template.name}/css/joomi-mini-mo.css" rel="stylesheet">
<link href="/themes/templates/{$template.name}/css/marketing.css" rel="stylesheet">
<link href="/themes/templates/{$template.name}/css/section_1.css" rel="stylesheet">
<link href="/themes/templates/{$template.name}/css/product-list.css" rel="stylesheet">
{if $template.layouts.helper.carousel}<link href="/themes/templates/{$template.name}/css/carousel-100.css" rel="stylesheet">{/if}
{if $template.layouts.helper.title}<link href="/themes/templates/{$template.name}/css/h1-description.css" rel="stylesheet">{/if}
{if $template.layouts.helper.newsletter}<link href="/themes/templates/{$template.name}/css/newsletter.css" rel="stylesheet">{/if}
<script>
{if $admin_uri}
var admin_uri = '{$admin_uri}'
{/if}
var post_id = '{$post_id}'
var router_check_in = '{$routers.site.check_in.route}'
var router_login = '{$routers.site.login.route}'
var router_logout = '{$routers.site.logout.route}'
var router_cart = '{$routers.site.cart.route}'
var router_language = '{$routers.site.language.route}'
</script>
<script src="/themes/app/js/app.min.js"></script>
<link href="/themes/templates/{$template.name}/css/contact.css" rel="stylesheet">
<link href="/themes/templates/{$template.name}/css/footer.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js"></script>
<script src="https://cdn.joomimart.com/holder/2.9.4/js/holder.min.js"></script>
<script src="/themes/lib/countdown/jquery.plugin.min.js"></script>
<script src="/themes/lib/countdown/jquery.countdown.min.js"></script>
<script src="https://cdn.joomimart.com/prism/js/prism.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.min.css">
<link rel="stylesheet" href="/themes/lib/fancybox/source/helpers/jquery.fancybox-buttons.min.css">
<link rel="stylesheet" href="/themes/lib/fancybox/source/helpers/jquery.fancybox-thumbs.min.css">
<script src="/themes/lib/pnotify/3.2.0/js/pnotify.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.min.js"></script>
<script type="text/javascript" src="/themes/lib/fancybox/fancybox_product.min.js"></script>
<script type="text/javascript" src="/themes/lib/fancybox/source/helpers/jquery.fancybox-buttons.min.js"></script>
<script type="text/javascript" src="/themes/lib/fancybox/source/helpers/jquery.fancybox-media.min.js"></script>
<script type="text/javascript" src="/themes/lib/fancybox/source/helpers/jquery.fancybox-thumbs.min.js"></script>
<script>
$(function () {
Holder.addTheme("thumb", { background: "#55595c", foreground: "#eceeef", text: "Thumbnail" });
});
</script>
<link href="/themes/lib/fancybox/3.0.47/css/jquery.fancybox.min.css" rel="stylesheet">
<script src="/themes/lib/fancybox/3.0.47/js/jquery.fancybox.min.js"></script>
<script src="/themes/lib/scrollpos-styler/scrollPosStyler.js"></script>
{if $session.authorize == 1 and $session.role_id == 100}
{if $config.demo_panel == '1' and $template.layouts.panel.demo}
{include ($template.layouts.panel.demo)}
{/if}
{/if}
</body>
</html>