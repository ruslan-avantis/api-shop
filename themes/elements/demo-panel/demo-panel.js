/*
* joomi-Demo-Panel v1.0.1
* Demo version (https://templates.joomimart.com/demo/free/joomi-mini-mo/demo.html)
* Copyright 2017 The Author: joomiMart
* Copyright 2014-2017 joomiMart.com
* Licensed under MIT (https://github.com/joomimart-com/joomi-demo-panel/blob/master/LICENSE)
*/

/* Demo Panel Left Toggle off/on 
<div class="demo_panel_left_section control_section_on_off">
<div class="demo-icon-div">
<div class="demo-icon"
data-icon-default="fa fa-sliders"
data-icon-alternate="fa fa-times"
data-icon-class=".bottom_panel_left_1_fa"
data-icon-class-2="bottom_panel_left_1_fa"
data-icon-class-active=".demo_panel_left_section"
>
<i class="bottom_panel_left_1_fa fa fa-sliders"></i> <span>Global</span>
</div>
</div>
<div class="demo_panel_left_content">
Here we place the content itself
</div>
</div>
*/
var $DemoPanelToggleSection = $('.demo_panel_left_section .demo-icon'); $DemoPanelToggleSection.on('click', function () { $('.demo_panel_left_section').toggleClass('active'); } );
var $DemoPanelToggleColors = $('.demo_panel_left_colors .demo-icon'); $DemoPanelToggleColors.on('click', function () { $('.demo_panel_left_colors').toggleClass('active'); } );
var $DemoPanelLinkColors = $('.link-colors-panel'); $DemoPanelLinkColors.on('click', function () { $('.demo_panel_left_colors').toggleClass('active'); } );
var $DemoPanelToggleBackground = $('.demo_panel_left_background .demo-icon'); $DemoPanelToggleBackground.on('click', function () { $('.demo_panel_left_background').toggleClass('active'); } );
var $DemoPanelToggleFonts = $('.demo_panel_left_fonts .demo-icon'); $DemoPanelToggleFonts.on('click', function () { $('.demo_panel_left_fonts').toggleClass('active'); } );
var $DemoPanelToggleUser = $('.demo_panel_left_user .demo-icon'); $DemoPanelToggleUser.on('click', function () { $('.demo_panel_left_user').toggleClass('active'); } );
var $DemoPanelTogglePlus = $('.demo_panel_left_plus .demo-icon'); $DemoPanelTogglePlus.on('click', function () { $('.demo_panel_left_plus').toggleClass('active'); } );

/* Icon Replacement Script - Universal 
<div class="demo-icon"
data-icon-default="fa fa-sliders"
data-icon-alternate="fa fa-times"
data-icon-class=".bottom_panel_left_1_fa"
data-icon-class-2="bottom_panel_left_1_fa"
data-icon-class-active=".demo_panel_left_section"
>	
<i class="bottom_panel_left_1_fa fa fa-sliders"></i> <span>Global</span>
</div>
*/
jQuery('.demo-icon').click(function() {
if (jQuery(this.getAttribute("data-icon-class-active")).hasClass("active")) {
var RemoveDemoIconClick = $(this.getAttribute("data-icon-class"));
RemoveDemoIconClick.removeClass();
RemoveDemoIconClick.addClass(this.getAttribute("data-icon-alternate")).addClass(this.getAttribute("data-icon-class-2"));
} else {
var RemoveDemoIconClick = $(this.getAttribute("data-icon-class"));
RemoveDemoIconClick.removeClass();
RemoveDemoIconClick.addClass(this.getAttribute("data-icon-default")).addClass(this.getAttribute("data-icon-class-2"));
}
});
jQuery('.link-colors-panel').click(function() {
if (jQuery(this.getAttribute("data-icon-class-active")).hasClass("active")) {
var RemoveDemoIconClick = $(this.getAttribute("data-icon-class"));
RemoveDemoIconClick.removeClass();
RemoveDemoIconClick.addClass(this.getAttribute("data-icon-alternate")).addClass(this.getAttribute("data-icon-class-2"));
} else {
var RemoveDemoIconClick = $(this.getAttribute("data-icon-class"));
RemoveDemoIconClick.removeClass();
RemoveDemoIconClick.addClass(this.getAttribute("data-icon-default")).addClass(this.getAttribute("data-icon-class-2"));
}
});

/* Section off/on
<div class="control_section_on_off">
<a class="control_section_link" href="#" id="section-1-none" data-name-section-on="#section-1-block" data-name-section-off="#section-1-none" data-class-section-on="section-1-block" data-class-section-off="section-1-none">Hide</a>
<a class="control_section_link" href="#" id="section-1-block" data-name-section-on="#section-1-block" data-name-section-off="#section-1-none" data-class-section-on="section-1-block" data-class-section-off="section-1-none">Show</a>
&nbsp;&nbsp;<span class="text-primary-xxl text_bold">Section-1</span>
</div>
*/
$('.control_section_on_off .control_section_link').click(function() {
var NameBlockOn = $(this.getAttribute("data-name-section-on"));
var NameBlockOff = $(this.getAttribute("data-name-section-off"));
if ($('body').hasClass(this.getAttribute("data-class-section-on"))) {
$('body').removeClass(this.getAttribute("data-class-section-on"));
$('body').addClass(this.getAttribute("data-class-section-off"));
} else {
$('body').removeClass(this.getAttribute("data-class-section-off"));
$('body').addClass(this.getAttribute("data-class-section-on"));
}
if ($('body').hasClass(this.getAttribute("data-class-section-on"))) {
NameBlockOff.removeClass('bg-yellow-md text-primary-md');
NameBlockOn.addClass('bg-yellow-md text-primary-md');
} else {
NameBlockOn.removeClass('bg-yellow-md text-primary-md');
NameBlockOff.addClass('bg-yellow-md text-primary-md');
}
});

/* Classic Section control off/on 
<body class="navbar-block">
<body class="navbar-none">
*/
['navbar', 'navbar-menu', 'menu', 'carousel', 'slider', 'section', 'about', 'services', 'features', 'portfolio', 'team', 'pricing', 'testimonial', 'blog', 'marketing', 'product-list', 'product', 'about-us', 'contact', 'reviews', 'newsletter', 'footer-header', 'footer'].forEach(function(i){
if ($( 'body' ).hasClass(i + '-block')) {
$('#' + i + '-none').removeClass( 'bg-yellow-md text-primary-md' );
$('#' + i + '-block').addClass( 'bg-yellow-md text-primary-md' );
} else {
$('#' + i + '-block').removeClass( 'bg-yellow-md text-primary-md' );
$('#' + i + '-none').addClass( 'bg-yellow-md text-primary-md' );
}
});

/* Section control off/on (section-1, section-2, section-*, section-1000) 
<body class="section-1-block">
<body class="section-1-none">
*/
var section = [];
for (var i = 0; i < 50; i++) {
var sectionid = 'section-' + i;	
var sectionnone = '#section-' + i + '-none';	
var sectionblock = '#section-' + i + '-block';	
if ($( 'body' ).hasClass(sectionid + '-block')) {
$(sectionnone).removeClass( 'bg-yellow-md text-primary-md' );	
$(sectionblock).addClass( 'bg-yellow-md text-primary-md' );	
} else {
$(sectionblock).removeClass( 'bg-yellow-md text-primary-md' );	
$(sectionnone).addClass( 'bg-yellow-md text-primary-md' );	
}
section[i] = true;
}

/* Slider control off/on (slider-1, slider-2, slider-*, slider-100)
<body class="slider-1-block">
<body class="slider-1-none">
*/
var slider = [];
for (var i = 0; i < 20; i++) {
var sectionid = 'slider-' + i;	
var sectionnone = '#slider-' + i + '-none';	
var sectionblock = '#slider-' + i + '-block';	
if ($( 'body' ).hasClass(sectionid + '-block')) {
$(sectionnone).removeClass( 'bg-yellow-md text-primary-md' );	
$(sectionblock).addClass( 'bg-yellow-md text-primary-md' );	
} else {
$(sectionblock).removeClass( 'bg-yellow-md text-primary-md' );	
$(sectionnone).addClass( 'bg-yellow-md text-primary-md' );	
}
slider[i] = true;
}

/* Color Control - Universal 
<div class="control-text text-red-md">Color Text</div>	
<div class="control-text-2 text-red-xxl">Color Text 2</div>
<div class="control-background bg-white">Color Background</div>	
*/
$('.color-control .link-color-control').click(function(event) {
event.preventDefault();
var RemoveColorSearch = $('.control-search'); RemoveColorSearch.removeClass(); RemoveColorSearch.addClass(this.getAttribute("data-search")).addClass('control-search');
var RemoveColorSearch2 = $('.control-search-2'); RemoveColorSearch2.removeClass(); RemoveColorSearch2.addClass(this.getAttribute("data-search-2")).addClass('control-search-2');
var RemoveColorText = $('.control-text'); RemoveColorText.removeClass(); RemoveColorText.addClass(this.getAttribute("data-text")).addClass('control-text');
var RemoveColorText2 = $('.control-text-2'); RemoveColorText2.removeClass(); RemoveColorText2.addClass(this.getAttribute("data-text-2")).addClass('control-text-2');
var RemoveColorText3 = $('.control-text-3'); RemoveColorText3.removeClass(); RemoveColorText3.addClass(this.getAttribute("data-text-3")).addClass('control-text-3');
var RemoveColorText4 = $('.control-text-4'); RemoveColorText4.removeClass(); RemoveColorText4.addClass(this.getAttribute("data-text-4")).addClass('control-text-4');
var RemoveColorText5 = $('.control-text-5'); RemoveColorText5.removeClass(); RemoveColorText5.addClass(this.getAttribute("data-text-5")).addClass('control-text-5');
var RemoveColorLink = $('.control-link'); RemoveColorLink.removeClass(); RemoveColorLink.addClass(this.getAttribute("data-link")).addClass('control-link');
var RemoveColorLink2 = $('.control-link-2'); RemoveColorLink2.removeClass(); RemoveColorLink2.addClass(this.getAttribute("data-link-2")).addClass('control-link-2');
var RemoveColorLink3 = $('.control-link-3'); RemoveColorLink3.removeClass(); RemoveColorLink3.addClass(this.getAttribute("data-link-3")).addClass('control-link-3');
var RemoveColorLink4 = $('.control-link-4'); RemoveColorLink4.removeClass(); RemoveColorLink4.addClass(this.getAttribute("data-link-4")).addClass('control-link-4');
var RemoveColorLink5 = $('.control-link-5'); RemoveColorLink5.removeClass(); RemoveColorLink5.addClass(this.getAttribute("data-link-5")).addClass('control-link-5');
var RemoveColorLinkA = $('.control-link-a'); RemoveColorLinkA.removeClass(); RemoveColorLinkA.addClass(this.getAttribute("data-link-a")).addClass('control-link-a');
var RemoveColorLinkA2 = $('.control-link-a-2'); RemoveColorLinkA2.removeClass(); RemoveColorLinkA2.addClass(this.getAttribute("data-link-a-2")).addClass('control-link-a-2');
var RemoveColorLinkA3 = $('.control-link-a-3'); RemoveColorLinkA3.removeClass(); RemoveColorLinkA3.addClass(this.getAttribute("data-link-a-3")).addClass('control-link-a-3');
var RemoveColorLinkA4 = $('.control-link-a-4'); RemoveColorLinkA4.removeClass(); RemoveColorLinkA4.addClass(this.getAttribute("data-link-a-4")).addClass('control-link-a-4');
var RemoveColorLinkA5 = $('.control-link-a-5'); RemoveColorLinkA5.removeClass(); RemoveColorLinkA5.addClass(this.getAttribute("data-link-a-5")).addClass('control-link-a-5');
var RemoveColorButton = $('.control-button'); RemoveColorButton.removeClass(); RemoveColorButton.addClass(this.getAttribute("data-button")).addClass('control-button');
var RemoveColorButton2 = $('.control-button-2'); RemoveColorButton2.removeClass(); RemoveColorButton2.addClass(this.getAttribute("data-button-2")).addClass('control-button-2');
var RemoveColorButton3 = $('.control-button-3'); RemoveColorButton3.removeClass(); RemoveColorButton3.addClass(this.getAttribute("data-button-3")).addClass('control-button-3');
var RemoveColorButton4 = $('.control-button-4'); RemoveColorButton4.removeClass(); RemoveColorButton4.addClass(this.getAttribute("data-button-4")).addClass('control-button-4');
var RemoveColorButton5 = $('.control-button-5'); RemoveColorButton5.removeClass(); RemoveColorButton5.addClass(this.getAttribute("data-button-5")).addClass('control-button-5');
var RemoveColorBackground = $('.control-background'); RemoveColorBackground.removeClass(); RemoveColorBackground.addClass(this.getAttribute("data-background")).addClass('control-background');
var RemoveColorBackground2 = $('.control-background-2'); RemoveColorBackground2.removeClass(); RemoveColorBackground2.addClass(this.getAttribute("data-background-2")).addClass('control-background-2');
var RemoveColorBackground3 = $('.control-background-3'); RemoveColorBackground3.removeClass(); RemoveColorBackground3.addClass(this.getAttribute("data-background-3")).addClass('control-background-3');
var RemoveColorBackground4 = $('.control-background-4'); RemoveColorBackground4.removeClass(); RemoveColorBackground4.addClass(this.getAttribute("data-background-4")).addClass('control-background-4');
var RemoveColorBackground5 = $('.control-background-5'); RemoveColorBackground5.removeClass(); RemoveColorBackground5.addClass(this.getAttribute("data-background-5")).addClass('control-background-5');
var RemoveColorInput = $('.control-input'); RemoveColorInput.removeClass(); RemoveColorInput.addClass(this.getAttribute("data-input")).addClass('control-input');
var RemoveColorInput2 = $('.control-input-2'); RemoveColorInput2.removeClass(); RemoveColorInput2.addClass(this.getAttribute("data-input-2")).addClass('control-input-2');
var RemoveColorInput3 = $('.control-input-3'); RemoveColorInput3.removeClass(); RemoveColorInput3.addClass(this.getAttribute("data-input-3")).addClass('control-input-3');
var RemoveColorInput4 = $('.control-input-4'); RemoveColorInput4.removeClass(); RemoveColorInput4.addClass(this.getAttribute("data-input-4")).addClass('control-input-4');
var RemoveColorInput5 = $('.control-input-5'); RemoveColorInput5.removeClass(); RemoveColorInput5.addClass(this.getAttribute("data-input-5")).addClass('control-input-5');
var RemoveColorCountdownNum = $('.control-countdown'); RemoveColorCountdownNum.removeClass(); RemoveColorCountdownNum.addClass(this.getAttribute("data-countdown-text")).addClass('control-countdown');
});

/* fonts control 
<html style="font-family:'Open Sans',sans-serif;">
<body style="font-family:'Open Sans',sans-serif;">
*/
(function($) {
$(document).ready(function() {
$('#fonts_area .fontsswitch').click(function() {
switchFonts(this.getAttribute("data-style"));
return false;
});
var c = readCookie('fonts');
if (c) switchFonts(c);
});
function switchFonts(fontsName) {
$('#body').css('font-family', fontsName);
$('html').css('font-family', fontsName);
createCookie('fonts', fontsName, 365);
}
})(jQuery);

/* fonts size control
<html style="font-size:14px;">
*/
(function($) {
$(document).ready(function() {
$('#fontssize_area .fontssizeswitch').click(function() {
switchsizeFonts(this.getAttribute("data-size"));
return false;
});
var c = readCookie('fonts-size');
if (c) switchsizeFonts(c);
});
function switchsizeFonts(fontsSize) {
$('html').css('font-size', fontsSize);
createCookie('fonts-size', fontsSize, 365);
}
})(jQuery);

/* Fixed Footer 
before
<html class="fixed-footer-none">
after
<html class="fixed-footer">
*/
var btnFixedFooter = $('.demo_panel_left_section #btn-fixed-footer');
var btnFixedFooterNone = $('.demo_panel_left_section #btn-fixed-footer-none');
if ($('html').hasClass('fixed-footer')) {
btnFixedFooter.addClass('bg-yellow-md text-primary-md');
} else {
btnFixedFooterNone.addClass('bg-yellow-md text-primary-md');
}
btnFixedFooter.click(function(event) {
event.preventDefault();
$('html').addClass('fixed-footer');
$(this).addClass('bg-yellow-md text-primary-md');
btnFixedFooterNone.removeClass('bg-yellow-md text-primary-md');
$('html').removeClass('fixed-footer-none');
});
btnFixedFooterNone.click(function(event) {
event.preventDefault();
$('html').addClass('fixed-footer-none');
$(this).addClass('bg-yellow-md text-primary-md');
btnFixedFooter.removeClass('bg-yellow-md text-primary-md');
$('html').removeClass('fixed-footer');
});

/* Fixed Header 
before
<html class="fixed-header">
after
<html class="fixed-header-none">
*/
var btnFixedHeader = $('.demo_panel_left_section #btn-fixed-header');
var btnFixedHeaderNone = $('.demo_panel_left_section #btn-fixed-header-none');
if ($('html').hasClass('fixed-header')) {
btnFixedHeader.addClass('bg-yellow-md text-primary-md');
} else {
btnFixedHeaderNone.addClass('bg-yellow-md text-primary-md');
}
btnFixedHeader.click(function(event) {
event.preventDefault();
$('html').addClass('fixed-header');
$(this).addClass('bg-yellow-md text-primary-md');
btnFixedHeaderNone.removeClass('bg-yellow-md text-primary-md');
$('html').removeClass('fixed-header-none');
});
btnFixedHeaderNone.click(function(event) {
event.preventDefault();
$('html').addClass('fixed-header-none');
$(this).addClass('bg-yellow-md text-primary-md');
btnFixedHeader.removeClass('bg-yellow-md text-primary-md');
$('html').removeClass('fixed-header');
});

/* boxed 
before
<body class="bg-cover">
after
<body class="boxed-header">
*/
var btnWide = $('.demo_panel_left_section #btn-wide');
var btnBoxed = $('.demo_panel_left_section #btn-boxed');
var btnBoxedplus = $('.demo_panel_left_section #btn-boxed-header');
if ($('body').hasClass('boxed')) {
btnBoxed.addClass('bg-yellow-md text-primary-md');
} else if ($('body').hasClass('bg-cover')) {
btnWide.addClass('bg-yellow-md text-primary-md');
} else {
btnBoxedplus.addClass('bg-yellow-md text-primary-md');
}
btnWide.click(function(event) {
event.preventDefault();
$(this).addClass('bg-yellow-md text-primary-md');
btnBoxed.removeClass('bg-yellow-md text-primary-md');
btnBoxedplus.removeClass('bg-yellow-md text-primary-md');
$('body').removeClass('boxed');
$('body').removeClass('boxed-header');
});
btnBoxed.click(function(event) {
event.preventDefault();
$('body').addClass('boxed');
$(this).addClass('bg-yellow-md text-primary-md');
btnWide.removeClass('bg-yellow-md text-primary-md');
btnBoxedplus.removeClass('bg-yellow-md text-primary-md');
$('body').removeClass('bg-cover');
$('body').removeClass('boxed-header');
});
btnBoxedplus.click(function(event) {
event.preventDefault();
$('body').addClass('boxed-header');
$('body').addClass('boxed');
$(this).addClass('bg-yellow-md text-primary-md');
btnWide.removeClass('bg-yellow-md text-primary-md');
btnBoxed.removeClass('bg-yellow-md text-primary-md');
$('body').removeClass('bg-cover');
});

/* background control
before
<html style="">
<body class="bg-cover">
after
<html style="background-image: url('https://www.toptal.com/designers/subtlepatterns/patterns/topography.png');">
<body class="boxed boxed-header">
Background pattern from Subtle Patterns - toptal.com
*/
$('#background_html .background-html').click(function(event) {
event.preventDefault();
$('body').removeClass('bg-cover');
$('body').addClass('boxed boxed-header');
$('html').css('background-image', 'url("' + $(this).attr('data-src') + '")');
});

/* body background images control 
before
<body class="bg-cover" style="">
<div id="body" style="">
after
<body class="boxed boxed-header" style="">
<div id="body" style="background-image: url('https://www.toptal.com/designers/subtlepatterns/patterns/topography.png');">
Background pattern from Subtle Patterns - toptal.com
*/
$('#background-image-control-id-body .background-image-control').click(function(event) {
event.preventDefault();
btnBoxed.trigger('click');
$('body').removeClass('bg-cover');
$('body').addClass('boxed boxed-header');
$('#body').css('background-image', 'url("' + $(this).attr('data-src') + '")');
});

/* html background images control
before
<html style="">
after
<html style="background-image: url('https://www.toptal.com/designers/subtlepatterns/patterns/topography.png');">
Background pattern from Subtle Patterns - toptal.com
*/
$('#background-image-control-id-html .background-image-control').click(function(event) {
event.preventDefault();
btnBoxed.trigger('click');
$('body').removeClass('bg-cover');
$('body').addClass('boxed boxed-header');
$('html').css('background-image', 'url("' + $(this).attr('data-src') + '")');
});

/* style switch control */
(function($) {
$(document).ready(function() {
$('#styleswitch_area .styleswitch').click(function() {
switchStylestyle(this.getAttribute("data-src"));
// console.log(this.getAttribute("rel"));
return false;
});
var c = readCookie('style');
if (c) switchStylestyle(c);
});
function switchStylestyle(styleName) {
$('link[rel*=style][title]').each(function(i) {
this.disabled = true;
if (this.getAttribute('title') == styleName) this.disabled = false;
});
createCookie('style', styleName, 365);
}
})(jQuery);

/* createCookie */
function createCookie(name, value, days) {
if (days) {
var date = new Date();
date.setTime(date.getTime() + (days * 24 * 60 * 60 * 365));
var expires = "; expires=" + date.toGMTString();
} else var expires = "";
document.cookie = name + "=" + value + expires + "; path=/";
}
function readCookie(name) {
var nameEQ = name + "=";
var ca = document.cookie.split(';');
for (var i = 0; i < ca.length; i++) {
var c = ca[i];
while (c.charAt(0) == ' ') c = c.substring(1, c.length);
if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
}
return null;
}
function eraseCookie(name) {
createCookie(name, "", -1);
}
