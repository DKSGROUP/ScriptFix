<?
function cache_it($title,$data)
{
	//echo 1;
	if($data=='')
		return '';
	$file = file_get_contents('cache.json');
	$file = json_decode($file,true);
	$file[$title] = $data;
	//print_r($file);
	$file = json_encode($file);
	//echo chr(10).chr(10).'###';
	//print_r($file);
	file_put_contents('cache.json', $file);
}

function get_cache($title)
{
	$file = file_get_contents('cache.json');
	$file = json_decode($file,true);
	if(isset($file[$title]))
		return $file[$title];
	else
		return false;
}

$id = $_GET['id'];
$data = file_get_contents('data.json');
$data = json_decode($data,true);
$data = $data[$id];

$link = $data['link2']==''?$data['link']:$data['link2'];
$linkPos = $link;
$linkPos = str_replace('https://yadi.sk/d/', '', $linkPos);
$linkPos = str_replace('/', '', $linkPos);
$positions = file_get_contents('data/'.$linkPos.'.json');
$positions = json_decode($positions,true);

$l1 = 'https://cloud-api.yandex.net/v1/disk/public/resources?limit=10000&preview_size=250x160&preview_crop=true&public_key='.urlencode($link);
$cache = get_cache($l1);
if($cache)
	$photos = $cache;
else
	$photos = file_get_contents($l1);

cache_it($l1, $photos);
$photos = json_decode($photos,true);

$l2 = 'https://cloud-api.yandex.net/v1/disk/public/resources?limit=10000&preview_size=XXXL&public_key='.urlencode($link);

$cache = get_cache($l2);
if($cache)
	$photos_big = $cache;
else
	$photos_big = file_get_contents($l2);

cache_it($l2, $photos_big);
//print_r($photos_big);
$photos_big = json_decode($photos_big,true);
//print_r($photos_big);

$down = file_get_contents('https://cloud-api.yandex.net/v1/disk/public/resources/download?public_key='.urlencode($data['link']));
$down = json_decode($down,true);
$data['href'] = $down['href'];

function getDown($link,$path)
{
	return '/gallery/down.php?link='.$link.'&path='.$path;
	/*$down = file_get_contents('https://cloud-api.yandex.net/v1/disk/public/resources/download?public_key='.urlencode($link).'&path='.urlencode($path));
	//print_r($down);
	$down = json_decode($down,true);
	return $down['href'];*/
}

require 'NCLNameCaseRu.php';
$nc = new NCLNameCaseRu();

$gender = $nc->genderDetect($data['name']);

$form = file_get_contents('http://photty.ru/dont-delete-this-post/');
$form = substr($form, strpos($form, 'et_pb_code et_pb_module  et_pb_code_2')-12);
$form = substr($form, 0, strrpos($form, 'data-field="fld_3092477"  />')).'</script></div></div>';
?>
<!DOCTYPE html>
<!--[if IE 6]>
<html id="ie6" lang="en-US">
<![endif]-->
<!--[if IE 7]>
<html id="ie7" lang="en-US">
<![endif]-->
<!--[if IE 8]>
<html id="ie8" lang="en-US">
<![endif]-->
<!--[if !(IE 6) | !(IE 7) | !(IE 8)  ]><!-->
<html lang="en-US">
<!--<![endif]-->
<head>
	<meta charset="UTF-8" />
			
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="robots" content="noindex,follow" />
	<link rel="pingback" href="http://photty.ru/xmlrpc.php" />

		<!--[if lt IE 9]>
	<script src="http://photty.ru/wp-content/themes/Divi/js/html5.js" type="text/javascript"></script>
	<![endif]-->

	<script type="text/javascript">
		document.documentElement.className = 'js';
	</script>

	<script>var et_site_url='http://photty.ru';var et_post_id='22020';function et_core_page_resource_fallback(a,b){"undefined"===typeof b&&(b=a.sheet.cssRules&&0===a.sheet.cssRules.length);b&&(a.onerror=null,a.onload=null,a.href?a.href=et_site_url+"/?et_core_page_resource="+a.id+et_post_id:a.src&&(a.src=et_site_url+"/?et_core_page_resource="+a.id+et_post_id))}
</script><title>Ваша галерея от PHOTTY</title>
<style>
	.download{
		position: absolute;
		display: block;
	    bottom: 50px;
	    left: 10px;
	    background: #fff;
	    width: 50px;
	    height: 50px;
	    opacity: .6;
	    cursor: pointer;
	    border-radius: 5px;
	    background-image: url(../download.png);
	    background-repeat: no-repeat;
	    background-size: 40px;
	    background-position: center;
	}

	.download:hover{
		opacity: 1;
	}

	body .caldera-grid .alert-danger, body .caldera-grid .alert-error{
		background-color:#dff0d8;border-color:#a3d48e;color:#3c763d
	}

	.et_pb_image_container img, .et_pb_post a img{
		width: 100%;
	}

</style>
<? if($data['link2']==''){ ?>
<style>
	.et_pb_gallery_image{
		position: relative;
	}
	.et_pb_gallery_image:before{
		content: '';
		position: absolute;
		width: 100%;
		height: 100%;
		background: url('../logo.png');
		background-position: right bottom 10px;
	    background-size: 50px;
	    background-repeat: no-repeat;
	}

	.mfp-figure figure{
		position: relative;
	}
	.mfp-figure figure:before{
		content: '';
		position: absolute;
		width: 100%;
		height: 100%;
		background: url('../logo.png');
		background-position: right bottom 60px;
	    background-size: 100px;
	    background-repeat: no-repeat;
	}
</style>
<? } ?>

<!-- All in One SEO Pack 2.3.2.3 by Michael Torbert of Semper Fi Web Design[1016,1050] -->
<meta name="robots" content="noindex,follow" />
<!-- /all in one seo pack -->
<link rel="dns-prefetch" href="//connect.facebook.net" />
<link rel='dns-prefetch' href='//connect.facebook.net' />
<link rel='dns-prefetch' href='//fonts.googleapis.com' />
<link rel='dns-prefetch' href='//s.w.org' />
		<script type="text/javascript">
			window._wpemojiSettings = {"baseUrl":"https:\/\/s.w.org\/images\/core\/emoji\/2.3\/72x72\/","ext":".png","svgUrl":"https:\/\/s.w.org\/images\/core\/emoji\/2.3\/svg\/","svgExt":".svg","source":{"concatemoji":"http:\/\/photty.ru\/wp-includes\/js\/wp-emoji-release.min.js?ver=4.8.1"}};
			!function(a,b,c){function d(a){var b,c,d,e,f=String.fromCharCode;if(!k||!k.fillText)return!1;switch(k.clearRect(0,0,j.width,j.height),k.textBaseline="top",k.font="600 32px Arial",a){case"flag":return k.fillText(f(55356,56826,55356,56819),0,0),b=j.toDataURL(),k.clearRect(0,0,j.width,j.height),k.fillText(f(55356,56826,8203,55356,56819),0,0),c=j.toDataURL(),b===c&&(k.clearRect(0,0,j.width,j.height),k.fillText(f(55356,57332,56128,56423,56128,56418,56128,56421,56128,56430,56128,56423,56128,56447),0,0),b=j.toDataURL(),k.clearRect(0,0,j.width,j.height),k.fillText(f(55356,57332,8203,56128,56423,8203,56128,56418,8203,56128,56421,8203,56128,56430,8203,56128,56423,8203,56128,56447),0,0),c=j.toDataURL(),b!==c);case"emoji4":return k.fillText(f(55358,56794,8205,9794,65039),0,0),d=j.toDataURL(),k.clearRect(0,0,j.width,j.height),k.fillText(f(55358,56794,8203,9794,65039),0,0),e=j.toDataURL(),d!==e}return!1}function e(a){var c=b.createElement("script");c.src=a,c.defer=c.type="text/javascript",b.getElementsByTagName("head")[0].appendChild(c)}var f,g,h,i,j=b.createElement("canvas"),k=j.getContext&&j.getContext("2d");for(i=Array("flag","emoji4"),c.supports={everything:!0,everythingExceptFlag:!0},h=0;h<i.length;h++)c.supports[i[h]]=d(i[h]),c.supports.everything=c.supports.everything&&c.supports[i[h]],"flag"!==i[h]&&(c.supports.everythingExceptFlag=c.supports.everythingExceptFlag&&c.supports[i[h]]);c.supports.everythingExceptFlag=c.supports.everythingExceptFlag&&!c.supports.flag,c.DOMReady=!1,c.readyCallback=function(){c.DOMReady=!0},c.supports.everything||(g=function(){c.readyCallback()},b.addEventListener?(b.addEventListener("DOMContentLoaded",g,!1),a.addEventListener("load",g,!1)):(a.attachEvent("onload",g),b.attachEvent("onreadystatechange",function(){"complete"===b.readyState&&c.readyCallback()})),f=c.source||{},f.concatemoji?e(f.concatemoji):f.wpemoji&&f.twemoji&&(e(f.twemoji),e(f.wpemoji)))}(window,document,window._wpemojiSettings);
		</script>
		<meta content="Divi v.2.7.5" name="generator"/><style type="text/css">
img.wp-smiley,
img.emoji {
	display: inline !important;
	border: none !important;
	box-shadow: none !important;
	height: 1em !important;
	width: 1em !important;
	margin: 0 .07em !important;
	vertical-align: -0.1em !important;
	background: none !important;
	padding: 0 !important;
}
</style>
<link rel='stylesheet' id='dashicons-css'  href='http://photty.ru/wp-includes/css/dashicons.min.css?ver=4.8.1' type='text/css' media='all' />
<link rel='stylesheet' id='admin-bar-css'  href='http://photty.ru/wp-includes/css/admin-bar.min.css?ver=4.8.1' type='text/css' media='all' />
<link rel='stylesheet' id='menu-icons-extra-css'  href='http://photty.ru/wp-content/plugins/menu-icons/css/extra.min.css?ver=0.9.2' type='text/css' media='all' />
<link rel='stylesheet' id='menu-image-css'  href='http://photty.ru/wp-content/plugins/menu-image/menu-image.css?ver=1.1' type='text/css' media='all' />
<link rel='stylesheet' id='twenty-twenty-css'  href='http://photty.ru/wp-content/plugins/smart-before-after-viewer/includes/twentytwenty/css/twentytwenty.min.css?ver=4.8.1' type='text/css' media='all' />
<link rel='stylesheet' id='divi-fonts-css'  href='http://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800&#038;subset=latin,latin-ext' type='text/css' media='all' />
<link rel='stylesheet' id='et-gf-comfortaa-css'  href='http://fonts.googleapis.com/css?family=Comfortaa:400,300,700&#038;subset=latin,cyrillic-ext,greek,latin-ext,cyrillic' type='text/css' media='all' />
<link rel='stylesheet' id='divi-style-css'  href='http://photty.ru/wp-content/themes/Divi/style.css?ver=2.7.5' type='text/css' media='all' />
<link rel='stylesheet' id='et-shortcodes-css-css'  href='http://photty.ru/wp-content/themes/Divi/epanel/shortcodes/css/shortcodes.css?ver=2.7.5' type='text/css' media='all' />
<link rel='stylesheet' id='cf-front-css'  href='http://photty.ru/wp-content/plugins/caldera-forms/assets/build/css/caldera-forms-front.min.css?ver=1.5.5' type='text/css' media='all' />
<link rel='stylesheet' id='et-shortcodes-responsive-css-css'  href='http://photty.ru/wp-content/themes/Divi/epanel/shortcodes/css/shortcodes_responsive.css?ver=2.7.5' type='text/css' media='all' />
<link rel='stylesheet' id='magnific-popup-css'  href='http://photty.ru/wp-content/themes/Divi/includes/builder/styles/magnific_popup.css?ver=2.7.5' type='text/css' media='all' />
<script type='text/javascript' src='http://photty.ru/wp-includes/js/jquery/jquery.js?ver=1.12.4'></script>
<script type='text/javascript' src='http://photty.ru/wp-includes/js/jquery/jquery-migrate.min.js?ver=1.4.1'></script>
<script type='text/javascript' src='http://photty.ru/wp-content/plugins/WP_Estimation_Form/assets/js/lfb_frontend.min.js?ver=9.502'></script>
<script type='text/javascript' src='http://photty.ru/wp-content/plugins/smart-before-after-viewer/includes/twentytwenty/js/jquery.event.move.min.js?ver=4.8.1'></script>
<script type='text/javascript' src='http://photty.ru/wp-content/plugins/smart-before-after-viewer/includes/twentytwenty/js/jquery.twentytwenty.min.js?ver=4.8.1'></script>
<link rel='https://api.w.org/' href='http://photty.ru/wp-json/' />
<link rel="EditURI" type="application/rsd+xml" title="RSD" href="http://photty.ru/xmlrpc.php?rsd" />
<link rel="wlwmanifest" type="application/wlwmanifest+xml" href="http://photty.ru/wp-includes/wlwmanifest.xml" /> 
<link rel='next' title='M1972' href='http://photty.ru/m1972/' />
<meta name="generator" content="WordPress 4.8.1" />
<link rel='shortlink' href='http://photty.ru/?p=22020' />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />		<style id="theme-customizer-css">
					@media only screen and ( min-width: 767px ) {
				body, .et_pb_column_1_2 .et_quote_content blockquote cite, .et_pb_column_1_2 .et_link_content a.et_link_main_url, .et_pb_column_1_3 .et_quote_content blockquote cite, .et_pb_column_3_8 .et_quote_content blockquote cite, .et_pb_column_1_4 .et_quote_content blockquote cite, .et_pb_blog_grid .et_quote_content blockquote cite, .et_pb_column_1_3 .et_link_content a.et_link_main_url, .et_pb_column_3_8 .et_link_content a.et_link_main_url, .et_pb_column_1_4 .et_link_content a.et_link_main_url, .et_pb_blog_grid .et_link_content a.et_link_main_url, body .et_pb_bg_layout_light .et_pb_post p,  body .et_pb_bg_layout_dark .et_pb_post p { font-size: 18px; }
				.et_pb_slide_content, .et_pb_best_value { font-size: 20px; }
			}
																					#main-header .nav li ul { background-color: rgba(255,255,255,0.77); }
									#top-header, #et-secondary-nav li ul { background-color: rgba(30,143,191,0.89); }
																	#top-header, #top-header a, #et-secondary-nav li li a, #top-header .et-social-icon a:before {
									font-size: 13px;
															}
							#top-menu li a { font-size: 17px; }
			body.et_vertical_nav .container.et_search_form_container .et-search-form input { font-size: 17px !important; }
		
		
																#footer-widgets .footer-widget li:before { top: 12.3px; }							.et-fixed-header#main-header { box-shadow: none !important; }
								
		
																														
		@media only screen and ( min-width: 981px ) {
							.et_pb_section { padding: 2% 0; }
				.et_pb_section.et_pb_section_first { padding-top: inherit; }
				.et_pb_fullwidth_section { padding: 0; }
										.et_pb_row { padding: 1% 0; }
																.et_header_style_left #et-top-navigation, .et_header_style_split #et-top-navigation  { padding: 27px 0 0 0; }
				.et_header_style_left #et-top-navigation nav > ul > li > a, .et_header_style_split #et-top-navigation nav > ul > li > a { padding-bottom: 27px; }
				.et_header_style_split .centered-inline-logo-wrap { width: 54px; margin: -54px 0; }
				.et_header_style_split .centered-inline-logo-wrap #logo { max-height: 54px; }
				.et_pb_svg_logo.et_header_style_split .centered-inline-logo-wrap #logo { height: 54px; }
				.et_header_style_centered #top-menu > li > a { padding-bottom: 10px; }
				.et_header_style_slide #et-top-navigation, .et_header_style_fullscreen #et-top-navigation { padding: 18px 0 18px 0 !important; }
									.et_header_style_centered #main-header .logo_container { height: 54px; }
														#logo { max-height: 100%; }
				.et_pb_svg_logo #logo { height: 100%; }
																.et_header_style_centered.et_hide_primary_logo #main-header:not(.et-fixed-header) .logo_container, .et_header_style_centered.et_hide_fixed_logo #main-header.et-fixed-header .logo_container { height: 9.72px; }
										.et_header_style_left .et-fixed-header #et-top-navigation, .et_header_style_split .et-fixed-header #et-top-navigation { padding: 15px 0 0 0; }
				.et_header_style_left .et-fixed-header #et-top-navigation nav > ul > li > a, .et_header_style_split .et-fixed-header #et-top-navigation nav > ul > li > a  { padding-bottom: 15px; }
				.et_header_style_centered header#main-header.et-fixed-header .logo_container { height: 30px; }
				.et_header_style_split .et-fixed-header .centered-inline-logo-wrap { width: 30px; margin: -30px 0;  }
				.et_header_style_split .et-fixed-header .centered-inline-logo-wrap #logo { max-height: 30px; }
				.et_pb_svg_logo.et_header_style_split .et-fixed-header .centered-inline-logo-wrap #logo { height: 30px; }
				.et_header_style_slide .et-fixed-header #et-top-navigation, .et_header_style_fullscreen .et-fixed-header #et-top-navigation { padding: 6px 0 6px 0 !important; }
													.et-fixed-header#top-header, .et-fixed-header#top-header #et-secondary-nav li ul { background-color: rgba(30,143,191,0.89); }
										.et-fixed-header#main-header, .et-fixed-header#main-header .nav li ul, .et-fixed-header .et-search-form { background-color: rgba(255,255,255,0.78); }
										.et-fixed-header #top-menu li a { font-size: 17px; }
												
					}
		@media only screen and ( min-width: 1350px) {
			.et_pb_row { padding: 13px 0; }
			.et_pb_section { padding: 27px 0; }
			.single.et_pb_pagebuilder_layout.et_full_width_page .et_post_meta_wrapper { padding-top: 40px; }
			.et_pb_section.et_pb_section_first { padding-top: inherit; }
			.et_pb_fullwidth_section { padding: 0; }
		}
		@media only screen and ( max-width: 980px ) {
																				}
		@media only screen and ( max-width: 767px ) {
														}
	</style>

					<style class="et_heading_font">
				h1, h2, h3, h4, h5, h6 {
					font-family: 'Comfortaa', cursive;				}
				</style>
							<style class="et_body_font">
				body, input, textarea, select {
					font-family: 'Comfortaa', cursive;				}
				</style>
			
	
	<style id="module-customizer-css">
			</style>

	<link rel="shortcut icon" href="http://photty.ru/wordpress/wp-content/uploads/2016/01/cropped-Photty-blue_mark.png" /><meta property="og:site_name" content="PHOTTY" />
<meta property="og:image" content="<?=$photos_big['_embedded']['items'][0]['preview']?>" />
<meta property="og:image:width" content="1200" />
<meta property="og:image:height" content="800" />
<style type="text/css" media="print">#wpadminbar { display:none; }</style>
<style type="text/css" media="screen">
	html { margin-top: 32px !important; }
	* html body { margin-top: 32px !important; }
	@media screen and ( max-width: 782px ) {
		html { margin-top: 46px !important; }
		* html body { margin-top: 46px !important; }
	}
</style>
<!-- Скрипт Universal Analytics -->
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-73114015-1', 'auto');
  ga('send', 'pageview');

</script>
<!-- Скрипт совместного подключения Calltouch и Universal Analytics -->
<script type="text/javascript">
function ct(w,d,e,c){
var a='all',b='tou',src=b+'c'+'h';src='m'+'o'+'d.c'+a+src;
var jsHost="https://"+src,s=d.createElement(e),p=d.getElementsByTagName(e)[0];
s.async=1;s.src=jsHost+"."+"r"+"u/d_client.js?param;"+(c?"client_id"+c+";":"")
+"ref"+escape(d.referrer)+";url"+escape(d.URL)+";cook"+escape(d.cookie)+";";
p.parentNode.insertBefore(s,p);
if(!w.jQuery){var jq=d.createElement(e);
jq.src=jsHost+"."+"r"+'u/js/jquery-1.7.min.js';
p.parentNode.insertBefore(jq,p);}}
if(!!window.GoogleAnalyticsObject){window[window.GoogleAnalyticsObject](function(tracker){
if (!!window[window.GoogleAnalyticsObject].getAll()[0])
{ct(window,document,'script', window[window.GoogleAnalyticsObject].getAll()[0].get('clientId'))}
else{ct(window,document,'script', null);}});
}else{ct(window,document,'script', null);}
</script>

<!-- Facebook Pixel Code -->
<script>
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
document,'script','https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '1414106778683354'); // Insert your pixel ID here.
fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id=1414106778683354&ev=PageView&noscript=1"
/></noscript>
<!-- DO NOT MODIFY -->
<!-- End Facebook Pixel Code -->
<link rel="icon" href="http://photty.ru/wp-content/uploads//2016/08/cropped-Photty_LOGO-32x32.png" sizes="32x32" />
<link rel="icon" href="http://photty.ru/wp-content/uploads//2016/08/cropped-Photty_LOGO-192x192.png" sizes="192x192" />
<link rel="apple-touch-icon-precomposed" href="http://photty.ru/wp-content/uploads//2016/08/cropped-Photty_LOGO-180x180.png" />
<meta name="msapplication-TileImage" content="http://photty.ru/wp-content/uploads//2016/08/cropped-Photty_LOGO-270x270.png" />
<style type="text/css" id="et-custom-css">
#footer-info {display:none;}
</style>			
							<style id="tt-easy-google-font-styles" type="text/css">
			
																						p {
													}
								
																										h1 {
													}
								
																										h2 {
													}
								
																										h3 {
													}
								
																										h4 {
													}
								
																										h5 {
													}
								
																										h6 {
													}
								
										
							</style>
						</head>
<body class="post-template-default single single-post postid-22020 single-format-standard logged-in admin-bar no-customize-support chrome et_pb_button_helper_class et_fullwidth_nav et_fullwidth_secondary_nav et_fixed_nav et_show_nav et_cover_background et_pb_gutter osx et_pb_gutters3 et_primary_nav_dropdown_animation_expand et_secondary_nav_dropdown_animation_fade et_pb_footer_columns4 et_header_style_left et_pb_pagebuilder_layout et_full_width_page">
	<div style="display: none">
	<? 
print_r($an);
	?>
	</div>
	<div style="display: none">
	<? 
print_r($an2);
	?>
	</div>
	<div id="page-container">

	
	
		<header id="main-header" data-height-onload="54">
			<div class="container clearfix et_menu_container">
							<div class="logo_container">
					<span class="logo_helper"></span>
					<a href="http://photty.ru/">
						<img src="http://photty.ru/wp-content/uploads//2017/09/Photty_LOGO-400-for-web.png" alt="PHOTTY" id="logo" data-height-percentage="100" />
					</a>
				</div>
				<div id="et-top-navigation" data-height="54" data-fixed-height="30">
											<nav id="top-menu-nav">
						<ul id="top-menu" class="nav"><li id="menu-item-714" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-714"><a href="tel:+74997033992" class="menu-image-title-after menu-image-not-hovered"><img width="36" height="36" src="http://photty.ru/wp-content/uploads//2016/02/phone-icon1-36x36.png" class="menu-image menu-image-title-after" alt="" srcset="http://photty.ru/wp-content/uploads/2016/02/phone-icon1-36x36.png 36w, http://photty.ru/wp-content/uploads/2016/02/phone-icon1-150x150.png 150w, http://photty.ru/wp-content/uploads/2016/02/phone-icon1-298x300.png 298w, http://photty.ru/wp-content/uploads/2016/02/phone-icon1-24x24.png 24w, http://photty.ru/wp-content/uploads/2016/02/phone-icon1-48x48.png 48w, http://photty.ru/wp-content/uploads/2016/02/phone-icon1.png 787w" sizes="(max-width: 36px) 100vw, 36px" /><span class="menu-image-title">+7 499 703 3992</span></a></li>
<li id="menu-item-944" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-944"><a href="mailto:welcome@photty.ru" class="menu-image-title-after menu-image-not-hovered"><img width="36" height="36" src="http://photty.ru/wp-content/uploads//2016/02/e-mail-icon2-36x36.png" class="menu-image menu-image-title-after" alt="" srcset="http://photty.ru/wp-content/uploads/2016/02/e-mail-icon2-36x36.png 36w, http://photty.ru/wp-content/uploads/2016/02/e-mail-icon2-150x150.png 150w, http://photty.ru/wp-content/uploads/2016/02/e-mail-icon2-298x300.png 298w, http://photty.ru/wp-content/uploads/2016/02/e-mail-icon2-24x24.png 24w, http://photty.ru/wp-content/uploads/2016/02/e-mail-icon2-48x48.png 48w, http://photty.ru/wp-content/uploads/2016/02/e-mail-icon2.png 787w" sizes="(max-width: 36px) 100vw, 36px" /><span class="menu-image-title">welcome@photty.ru</span></a></li>
</ul>						</nav>
					
					
					
					
					<div id="et_mobile_nav_menu">
				<div class="mobile_nav closed">
					<span class="select_page">Select Page</span>
					<span class="mobile_menu_bar mobile_menu_bar_toggle"></span>
				</div>
			</div>				</div> <!-- #et-top-navigation -->
			</div> <!-- .container -->
			<div class="et_search_outer">
				<div class="container et_search_form_container">
					<form role="search" method="get" class="et-search-form" action="http://photty.ru/">
					<input type="search" class="et-search-field" placeholder="Search &hellip;" value="" name="s" title="Search for:" />					</form>
					<span class="et_close_search_field"></span>
				</div>
			</div>
		</header> <!-- #main-header -->

		<div id="et-main-area">
<div id="main-content">
	<div class="container">
		<div id="content-area" class="clearfix">
			<div id="left-area">
							
				
				<article id="post-22020" class="et_pb_post post-22020 post type-post status-publish format-standard hentry category-clients-gallery category-1">
					
					<div class="entry-content">
					<div class="et_pb_section  et_pb_section_0 et_section_regular">
				
				
					
					<div class=" et_pb_row et_pb_row_0">
				
				<div class="et_pb_column et_pb_column_4_4  et_pb_column_0">
				
				<div class="et_pb_text et_pb_module et_pb_bg_layout_light et_pb_text_align_left  et_pb_text_0">
				
<p>Здравствуйте, <?=$data['name']?>!</p>

			</div> <!-- .et_pb_text --><div class="et_pb_text et_pb_module et_pb_bg_layout_light et_pb_text_align_left  et_pb_text_1">
				
<p>Спасибо, что выбрали PHOTTY!</p>
<p>Доступ к этой странице будет только у тех, кому вы отправите ссылку.<br />
Скачайте все ваши фото одной <a href="#downloadfromyandex">кнопкой</a> с Яндекс.Диска.</p>
<p>&nbsp;</p>

			</div> <!-- .et_pb_text -->
			</div> <!-- .et_pb_column -->
					
			</div> <!-- .et_pb_row -->
				
			</div> <!-- .et_pb_section --><div class="et_pb_section  et_pb_section_1 et_section_regular">
				
				
					
					<div class=" et_pb_row et_pb_row_1">
				
				<div class="et_pb_column et_pb_column_4_4  et_pb_column_1">
				
				<div class="et_pb_module et_pb_gallery et_pb_gallery_0 et_pb_gallery_grid et_pb_bg_layout_light clearfix">
				<div class="et_pb_gallery_items et_post_gallery" data-per_page="12">

				<? foreach ($photos['_embedded']['items'] as $key => $value) { ?>
					<div class="et_pb_gallery_item et_pb_grid_item et_pb_bg_layout_light" download="<?=getDown($link,$value['path'])?>">
					<div class='et_pb_gallery_image landscape'>
						<a href="<?=$photos_big['_embedded']['items'][$key]['preview']?>">
						<img src2="<?=$value['preview']?>" />
						<span class="et_overlay"></span>
					</a>
					</div></div>
				<? } ?>

				</div><!-- .et_pb_gallery_items --><div class='et_pb_gallery_pagination'></div></div><!-- .et_pb_gallery -->
			</div> <!-- .et_pb_column -->
					
			</div> <!-- .et_pb_row --><div class=" et_pb_row et_pb_row_2">
				
				<div class="et_pb_column et_pb_column_1_4  et_pb_column_2">
				
				<div class="et_pb_code et_pb_module  et_pb_code_0">
				
<div class="fb-social-plugin fb-like" data-ref="shortcode" data-href="http://<?=$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']?>" data-share="true" data-width="400"></div>

			</div> <!-- .et_pb_code -->
			</div> <!-- .et_pb_column --><div class="et_pb_column et_pb_column_1_4  et_pb_column_3 et_pb_column_empty">
				
				
			</div> <!-- .et_pb_column --><div class="et_pb_column et_pb_column_1_4  et_pb_column_4 et_pb_column_empty">
				
				
			</div> <!-- .et_pb_column --><div class="et_pb_column et_pb_column_1_4  et_pb_column_5 et_pb_column_empty">
				
				
			</div> <!-- .et_pb_column -->
					
			</div> <!-- .et_pb_row -->
				
			</div> <!-- .et_pb_section --><div class="et_pb_section  et_pb_section_2 et_section_regular">
				
				
					
					<div class=" et_pb_row et_pb_row_4">
				
				<div class="et_pb_column et_pb_column_1_2  et_pb_column_7">
				
				<div class="et_pb_text et_pb_module et_pb_bg_layout_light et_pb_text_align_left  et_pb_text_2">
				
<p>Для печати или хранения в архиве стоит использовать фотографии в полном размере. Пожалуйста, нажмите на кнопку &#8220;СКАЧАТЬ ВСЕ ФОТО&#8221;, чтобы скачать полноразмерные фото.</p>

			</div> <!-- .et_pb_text -->
			</div> <!-- .et_pb_column --><div class="et_pb_column et_pb_column_1_2  et_pb_column_8">
				
				<div class="et_pb_code et_pb_module  et_pb_code_2">
				<a name="downloadfromyandex"></a>
			</div> <!-- .et_pb_code --><div class="et_pb_module et-waypoint et_pb_image et_pb_animation_off et_pb_image_0 et_always_center_on_mobile">
				<a href="<?=$data['href']?>" target="_blank"><img src="http://photty.ru/wp-content/uploads//2017/04/download_button_400px.jpg" alt="" />
			</a>
			</div><div class="et_pb_text et_pb_module et_pb_bg_layout_light et_pb_text_align_left  et_pb_text_3">
				
<p>&nbsp;</p>

			</div> <!-- .et_pb_text -->
			</div> <!-- .et_pb_column -->
					
			</div> <!-- .et_pb_row -->
				
			</div> <!-- .et_pb_section --><div class="et_pb_section  et_pb_section_4 et_pb_with_background et_section_regular">
				
				
					
					<div class=" et_pb_row et_pb_row_5">
				
				<div class="et_pb_column et_pb_column_4_4  et_pb_column_9">
			</div> <!-- .et_pb_column -->
					
			</div> <!-- .et_pb_row --><div class=" et_pb_row et_pb_row_6">
				
				<div class="et_pb_column et_pb_column_4_4  et_pb_column_10">
				
				<?=$form?>
			</div> <!-- .et_pb_column -->
					
			</div> <!-- .et_pb_row -->
				
			</div> <!-- .et_pb_section -->
					</div> <!-- .entry-content -->
					<div class="et_post_meta_wrapper">
					
					
										</div> <!-- .et_post_meta_wrapper -->
				</article> <!-- .et_pb_post -->

						</div> <!-- #left-area -->

					</div> <!-- #content-area -->
	</div> <!-- .container -->
</div> <!-- #main-content -->


			<footer id="main-footer">
				

		
				<div id="et-footer-nav">
					<div class="container">
						<ul id="menu-footer-menu" class="bottom-nav"><li id="menu-item-1876" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-1876"><a href="http://photty.ru/event-photo/" class="menu-image-title-after"><span class="menu-image-title">Фотограф на мероприятие</span></a></li>
<li id="menu-item-1877" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-1877"><a href="http://photty.ru/Family-photo/" class="menu-image-title-after"><span class="menu-image-title">Семейные фотосессии</span></a></li>
<li id="menu-item-1878" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-1878"><a href="http://photty.ru/portrait/" class="menu-image-title-after"><span class="menu-image-title">Портретная съемка</span></a></li>
<li id="menu-item-1879" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-1879"><a href="http://photty.ru/food-photo/" class="menu-image-title-after"><span class="menu-image-title">Съемка блюд</span></a></li>
<li id="menu-item-2538" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-2538"><a href="http://photty.ru/weddings/" class="menu-image-title-after"><span class="menu-image-title">Фотограф на свадьбу</span></a></li>
<li id="menu-item-4215" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-4215"><a href="http://photty.ru/business-photo/" class="menu-image-title-after"><span class="menu-image-title">Деловой портрет</span></a></li>
<li id="menu-item-4216" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-4216"><a href="http://photty.ru/studio-faq/" class="menu-image-title-after"><span class="menu-image-title">Подготовка к студии</span></a></li>
<li id="menu-item-1968" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-1968"><a href="http://photty.ru/pricing" class="menu-image-title-after"><span class="menu-image-title">Расчет стоимости</span></a></li>
<li id="menu-item-2952" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-2952"><a href="http://photty.ru/projects/" class="menu-image-title-after"><span class="menu-image-title">Проекты</span></a></li>
<li id="menu-item-1623" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-1623"><a href="http://photty.ru/faq" class="menu-image-title-after"><span class="menu-image-title">F.A.Q.</span></a></li>
<li id="menu-item-1564" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-1564"><a href="http://photty.ru/dogovor-oferta/" class="menu-image-title-after"><span class="menu-image-title">Договор-оферта</span></a></li>
<li id="menu-item-1880" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-1880"><a href="http://photty.SG" class="menu-image-title-after menu-image-not-hovered"><img width="36" height="24" src="http://photty.ru/wp-content/uploads//2016/08/sg-flag-36x24.png" class="menu-image menu-image-title-after" alt="" srcset="http://photty.ru/wp-content/uploads/2016/08/sg-flag-36x24.png 36w, http://photty.ru/wp-content/uploads/2016/08/sg-flag-24x16.png 24w, http://photty.ru/wp-content/uploads/2016/08/sg-flag-48x32.png 48w, http://photty.ru/wp-content/uploads/2016/08/sg-flag.png 250w" sizes="(max-width: 36px) 100vw, 36px" /><span class="menu-image-title">PHOTTY.SG</span></a></li>
</ul>					</div>
				</div> <!-- #et-footer-nav -->

			
				<div id="footer-bottom">
					<div class="container clearfix">
				
						<p id="footer-info">Designed by <a href="http://www.elegantthemes.com" title="Premium WordPress Themes">Elegant Themes</a> | Powered by <a href="http://www.wordpress.org">WordPress</a></p>
					</div>	<!-- .container -->
				</div>
			</footer> <!-- #main-footer -->
		</div> <!-- #et-main-area -->


	</div> <!-- #page-container -->

	<style type="text/css" id="et-builder-advanced-style">
				
.et_pb_section_4.et_pb_section { background-color:#ffffff !important; }
.et_pb_image_0 { max-width: 200px; text-align: center; }
			</style><div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_GB/sdk.js#xfbml=1&version=v2.10";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>

<script type="text/javascript">
jQuery(document).ready(function(){
jQuery("#footer-info").text(' ');
jQuery("<p>Copyright © Photty.ru. All rights reserved.</p>").insertAfter("#footer-info");
});
</script>

<!-- Yandex.Metrika counter -->
<script type="text/javascript">
    (function (d, w, c) {
        (w[c] = w[c] || []).push(function() {
            try {
                w.yaCounter35894790 = new Ya.Metrika({
                    id:35894790,
                    clickmap:true,
                    trackLinks:true,
                    accurateTrackBounce:true,
                    webvisor:true
                });
            } catch(e) { }
        });

        var n = d.getElementsByTagName("script")[0],
            s = d.createElement("script"),
            f = function () { n.parentNode.insertBefore(s, n); };
        s.type = "text/javascript";
        s.async = true;
        s.src = "https://mc.yandex.ru/metrika/watch.js";

        if (w.opera == "[object Opera]") {
            d.addEventListener("DOMContentLoaded", f, false);
        } else { f(); }
    })(document, window, "yandex_metrika_callbacks");
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/35894790" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->

<!-- BEGIN JIVOSITE CODE {literal} -->
<script type='text/javascript'>
(function(){ var widget_id = 'dTeUdoInhd';
var s = document.createElement('script'); s.type = 'text/javascript'; s.async = true; s.src = '//code.jivosite.com/script/widget/'+widget_id; var ss = document.getElementsByTagName('script')[0]; ss.parentNode.insertBefore(s, ss);})();</script>
<!-- {/literal} END JIVOSITE CODE --><script type='text/javascript' src='http://photty.ru/wp-includes/js/admin-bar.min.js?ver=4.8.1'></script>
<script type='text/javascript' src='http://photty.ru/wp-content/themes/Divi/includes/builder/scripts/frontend-builder-global-functions.js?ver=2.7.5'></script>
<script type='text/javascript' src='http://photty.ru/wp-includes/js/comment-reply.min.js?ver=4.8.1'></script>
<script type='text/javascript' src='http://photty.ru/wp-content/themes/Divi/includes/builder/scripts/jquery.mobile.custom.min.js?ver=2.7.5'></script>
<script type='text/javascript' src='http://photty.ru/wp-content/themes/Divi/js/custom.js?ver=2.7.5'></script>
<script type='text/javascript'>
/* <![CDATA[ */
var FB_WP=FB_WP||{};FB_WP.queue={_methods:[],flushed:false,add:function(fn){FB_WP.queue.flushed?fn():FB_WP.queue._methods.push(fn)},flush:function(){for(var fn;fn=FB_WP.queue._methods.shift();){fn()}FB_WP.queue.flushed=true}};window.fbAsyncInit=function(){FB.init({"xfbml":true});if(FB_WP && FB_WP.queue && FB_WP.queue.flush){FB_WP.queue.flush()}}
/* ]]> */
</script>
<script type="text/javascript">(function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(d.getElementById(id)){return}js=d.createElement(s);js.id=id;js.src="http:\/\/connect.facebook.net\/en_US\/all.js";fjs.parentNode.insertBefore(js,fjs)}(document,"script","facebook-jssdk"));</script>
<script type='text/javascript' src='http://photty.ru/wp-content/themes/Divi/includes/builder/scripts/jquery.fitvids.js?ver=2.7.5'></script>
<script type='text/javascript' src='http://photty.ru/wp-content/themes/Divi/includes/builder/scripts/waypoints.min.js?ver=2.7.5'></script>
<script type='text/javascript' src='http://photty.ru/wp-content/themes/Divi/includes/builder/scripts/jquery.magnific-popup.js?ver=2.7.5'></script>
<script type='text/javascript'>
/* <![CDATA[ */
var et_pb_custom = {"ajaxurl":"http:\/\/photty.ru\/wp-admin\/admin-ajax.php","images_uri":"http:\/\/photty.ru\/wp-content\/themes\/Divi\/images","builder_images_uri":"http:\/\/photty.ru\/wp-content\/themes\/Divi\/includes\/builder\/images","et_frontend_nonce":"23002219f1","subscription_failed":"Please, check the fields below to make sure you entered the correct information.","et_ab_log_nonce":"1568244e69","fill_message":"Please, fill in the following fields:","contact_error_message":"Please, fix the following errors:","invalid":"Invalid email","captcha":"Captcha","prev":"Prev","previous":"Previous","next":"Next","wrong_captcha":"You entered the wrong number in captcha.","is_builder_plugin_used":"","is_divi_theme_used":"1","widget_search_selector":".widget_search","is_ab_testing_active":"","page_id":"22020","unique_test_id":"","ab_bounce_rate":"5","is_cache_plugin_active":"no","is_shortcode_tracking":""};
/* ]]> */
</script>
<script type='text/javascript' src='http://photty.ru/wp-content/themes/Divi/includes/builder/scripts/frontend-builder-scripts.js?ver=2.7.5'></script>
<script type='text/javascript' src='http://photty.ru/wp-includes/js/wp-embed.min.js?ver=4.8.1'></script>
<script type='text/javascript' src='http://photty.ru/wp-content/themes/Divi/includes/builder/scripts/jquery.hashchange.js?ver=2.7.5'></script>
<script type='text/javascript' src='http://photty.ru/wp-content/plugins/caldera-forms/assets/build/js/jquery-baldrick.min.js?ver=1.5.5'></script>
<script type='text/javascript' src='http://photty.ru/wp-content/plugins/caldera-forms/assets/build/js/ajax-core.min.js?ver=1.5.5'></script>
<script type='text/javascript' src='http://photty.ru/wp-content/plugins/caldera-forms/assets/build/js/conditionals.min.js?ver=1.5.5'></script>
<script type='text/javascript' src='http://photty.ru/wp-content/plugins/caldera-forms/assets/build/js/parsley.min.js?ver=1.5.5'></script>
<script type='text/javascript'>
/* <![CDATA[ */
var CF_API_DATA = {"rest":{"root":"http:\/\/photty.ru\/wp-json\/cf-api\/v2\/","tokens":{"nonce":"http:\/\/photty.ru\/wp-json\/cf-api\/v2\/tokens\/form"},"nonce":"77e2d29e91"},"nonce":{"field":"_cf_verify"}};
/* ]]> */
</script>
<script type='text/javascript' src='http://photty.ru/wp-content/plugins/caldera-forms/assets/build/js/caldera-forms-front.min.js?ver=1.5.5'></script>
<div id="fb-root"></div><script type='text/javascript'>
/* <![CDATA[ */
var CF_API_DATA = {"rest":{"root":"http:\/\/photty.ru\/wp-json\/cf-api\/v2\/","tokens":{"nonce":"http:\/\/photty.ru\/wp-json\/cf-api\/v2\/tokens\/form"},"nonce":"77e2d29e91"},"nonce":{"field":"_cf_verify"}};
var CFFIELD_CONFIG = {"1":{"configs":{"fld_3092477":{"type":"button","id":"fld_3092477_1","default":false,"form_id":"CF59a7b52b33d4f","form_id_attr":"caldera_form_1"}},"fields":{"inputs":[{"type":"text","fieldId":"fld_7810243","id":"fld_7810243_1","options":[],"default":false},{"type":"email","fieldId":"fld_2964482","id":"fld_2964482_1","options":[],"default":false},{"type":"paragraph","fieldId":"fld_8002260","id":"fld_8002260_1","options":[],"default":false},{"type":"button","fieldId":"fld_3092477","id":"fld_3092477_1","options":[],"default":false},{"type":"utm","fieldId":"fld_2026098","id":"fld_2026098_1","options":[],"default":false}],"groups":[{"type":"radio","fieldId":"fld_8795905","id":"fld_8795905_1","options":["opt1074215","opt1576425"],"default":false},{"type":"radio","fieldId":"fld_3449161","id":"fld_3449161_1","options":["opt1856209","opt1160274","opt1984047","opt1628076","opt1523888"],"default":false},{"type":"radio","fieldId":"fld_7115678","id":"fld_7115678_1","options":["opt1897713","opt1549450","opt1845292"],"default":false},{"type":"radio","fieldId":"fld_6624765","id":"fld_6624765_1","options":["opt1171556","opt1176627","opt1122486","opt1504124","opt1504116"],"default":false},{"type":"radio","fieldId":"fld_8859246","id":"fld_8859246_1","options":["opt1282475","opt1888484","opt1488766"],"default":false}],"defaults":{"fld_7810243_1":false,"fld_8795905_1":false,"fld_3449161_1":false,"fld_2964482_1":false,"fld_7115678_1":false,"fld_6624765_1":false,"fld_8859246_1":false,"fld_8002260_1":false,"fld_3092477_1":false,"fld_2026098_1":false}},"error_strings":{"mixed_protocol":"Submission URL and current URL protocols do not match. Form may not function properly.","jquery_old":"An out of date version of jQuery is loaded on the page. Form may not function properly."}}};
/* ]]> */
</script>
	<!--[if lte IE 8]>
		<script type="text/javascript">
			document.body.className = document.body.className.replace( /(^|\s)(no-)?customize-support(?=\s|$)/, '' ) + ' no-customize-support';
		</script>
	<![endif]-->
	<!--[if gte IE 9]><!-->
		<script type="text/javascript">
			(function() {
				var request, b = document.body, c = 'className', cs = 'customize-support', rcs = new RegExp('(^|\\s+)(no-)?'+cs+'(\\s+|$)');

						request = true;
		
				b[c] = b[c].replace( rcs, ' ' );
				// The customizer requires postMessage and CORS (if the site is cross domain)
				b[c] += ( window.postMessage && request ? ' ' : ' no-' ) + cs;
			}());
		</script>
	<!--<![endif]-->
			<div id="wpadminbar" class="nojq nojs">
							<a class="screen-reader-shortcut" href="#wp-toolbar" tabindex="1">Skip to toolbar</a>
						<div class="quicklinks" id="wp-toolbar" role="navigation" aria-label="Toolbar" tabindex="0">
				<ul id="wp-admin-bar-root-default" class="ab-top-menu">
		<li id="wp-admin-bar-wp-logo" class="menupop"><a class="ab-item" aria-haspopup="true" href="http://photty.ru/wp-admin/about.php"><span class="ab-icon"></span><span class="screen-reader-text">About WordPress</span></a><div class="ab-sub-wrapper"><ul id="wp-admin-bar-wp-logo-default" class="ab-submenu">
		<li id="wp-admin-bar-about"><a class="ab-item" href="http://photty.ru/wp-admin/about.php">About WordPress</a>		</li></ul><ul id="wp-admin-bar-wp-logo-external" class="ab-sub-secondary ab-submenu">
		<li id="wp-admin-bar-wporg"><a class="ab-item" href="https://wordpress.org/">WordPress.org</a>		</li>
		<li id="wp-admin-bar-documentation"><a class="ab-item" href="https://codex.wordpress.org/">Documentation</a>		</li>
		<li id="wp-admin-bar-support-forums"><a class="ab-item" href="https://wordpress.org/support/">Support Forums</a>		</li>
		<li id="wp-admin-bar-feedback"><a class="ab-item" href="https://wordpress.org/support/forum/requests-and-feedback">Feedback</a>		</li></ul></div>		</li>
		<li id="wp-admin-bar-site-name" class="menupop"><a class="ab-item" aria-haspopup="true" href="http://photty.ru/wp-admin/">PHOTTY</a><div class="ab-sub-wrapper"><ul id="wp-admin-bar-site-name-default" class="ab-submenu">
		<li id="wp-admin-bar-dashboard"><a class="ab-item" href="http://photty.ru/wp-admin/">Dashboard</a>		</li></ul><ul id="wp-admin-bar-appearance" class="ab-submenu">
		<li id="wp-admin-bar-themes"><a class="ab-item" href="http://photty.ru/wp-admin/themes.php">Themes</a>		</li>
		<li id="wp-admin-bar-widgets"><a class="ab-item" href="http://photty.ru/wp-admin/widgets.php">Widgets</a>		</li>
		<li id="wp-admin-bar-menus"><a class="ab-item" href="http://photty.ru/wp-admin/nav-menus.php">Menus</a>		</li>
		<li id="wp-admin-bar-background" class="hide-if-customize"><a class="ab-item" href="http://photty.ru/wp-admin/themes.php?page=custom-background">Background</a>		</li>
		<li id="wp-admin-bar-customize-divi-theme" class="hide-if-no-customize"><a class="ab-item" href="http://photty.ru/wp-admin/customize.php?url=http%3A%2F%2Fphotty.ru%2F20170825-1-ruasean%2F&#038;et_customizer_option_set=theme">Theme Customizer</a>		</li>
		<li id="wp-admin-bar-customize-divi-module" class="hide-if-no-customize"><a class="ab-item" href="http://photty.ru/wp-admin/customize.php?url=http%3A%2F%2Fphotty.ru%2F20170825-1-ruasean%2F&#038;et_customizer_option_set=module">Module Customizer</a>		</li></ul></div>		</li>
		<li id="wp-admin-bar-updates"><a class="ab-item" href="http://photty.ru/wp-admin/update-core.php" title="9 Plugin Updates, 5 Theme Updates"><span class="ab-icon"></span><span class="ab-label">14</span><span class="screen-reader-text">9 Plugin Updates, 5 Theme Updates</span></a>		</li>
		<li id="wp-admin-bar-comments"><a class="ab-item" href="http://photty.ru/wp-admin/edit-comments.php"><span class="ab-icon"></span><span class="ab-label awaiting-mod pending-count count-0" aria-hidden="true">0</span><span class="screen-reader-text">0 comments awaiting moderation</span></a>		</li>
		<li id="wp-admin-bar-new-content" class="menupop"><a class="ab-item" aria-haspopup="true" href="http://photty.ru/wp-admin/post-new.php"><span class="ab-icon"></span><span class="ab-label">New</span></a><div class="ab-sub-wrapper"><ul id="wp-admin-bar-new-content-default" class="ab-submenu">
		<li id="wp-admin-bar-new-post"><a class="ab-item" href="http://photty.ru/wp-admin/post-new.php">Post</a>		</li>
		<li id="wp-admin-bar-new-media"><a class="ab-item" href="http://photty.ru/wp-admin/media-new.php">Media</a>		</li>
		<li id="wp-admin-bar-new-page"><a class="ab-item" href="http://photty.ru/wp-admin/post-new.php?post_type=page">Page</a>		</li>
		<li id="wp-admin-bar-new-project"><a class="ab-item" href="http://photty.ru/wp-admin/post-new.php?post_type=project">Project</a>		</li>
		<li id="wp-admin-bar-new-user"><a class="ab-item" href="http://photty.ru/wp-admin/user-new.php">User</a>		</li></ul></div>		</li>
		<li id="wp-admin-bar-edit"><a class="ab-item" href="http://photty.ru/wp-admin/post.php?post=22020&#038;action=edit">Edit Post</a>		</li>
		<li id="wp-admin-bar-all-in-one-seo-pack" class="menupop"><a class="ab-item" aria-haspopup="true" href="http://photty.ru/wp-admin/admin.php?page=all-in-one-seo-pack/aioseop_class.php">SEO</a><div class="ab-sub-wrapper"><ul id="wp-admin-bar-all-in-one-seo-pack-default" class="ab-submenu">
		<li id="wp-admin-bar-aiosp_edit_22020"><a class="ab-item" href="http://photty.ru/wp-admin/post.php?post=22020&#038;action=edit#aiosp">Edit SEO</a>		</li>
		<li id="wp-admin-bar-all-in-one-seo-pack/modules/aioseop_performance.php"><a class="ab-item" href="http://photty.ru/wp-admin/admin.php?page=all-in-one-seo-pack/modules/aioseop_performance.php">Performance</a>		</li>
		<li id="wp-admin-bar-all-in-one-seo-pack/modules/aioseop_sitemap.php"><a class="ab-item" href="http://photty.ru/wp-admin/admin.php?page=all-in-one-seo-pack/modules/aioseop_sitemap.php">XML Sitemap</a>		</li>
		<li id="wp-admin-bar-aiosp_robots_generator"><a class="ab-item" href="http://photty.ru/wp-admin/admin.php?page=aiosp_robots_generator">Robots.txt</a>		</li>
		<li id="wp-admin-bar-all-in-one-seo-pack/modules/aioseop_feature_manager.php"><a class="ab-item" href="http://photty.ru/wp-admin/admin.php?page=all-in-one-seo-pack/modules/aioseop_feature_manager.php">Feature Manager</a>		</li>
		<li id="wp-admin-bar-aioseop-pro-upgrade"><a class="ab-item" href="http://semperplugins.com/plugins/all-in-one-seo-pack-pro-version/?loc=menu" target="_blank">Upgrade To Pro</a>		</li></ul></div>		</li></ul><ul id="wp-admin-bar-top-secondary" class="ab-top-secondary ab-top-menu">
		<li id="wp-admin-bar-search" class="admin-bar-search"><div class="ab-item ab-empty-item" tabindex="-1"><form action="http://photty.ru/" method="get" id="adminbarsearch"><input class="adminbar-input" name="s" id="adminbar-search" type="text" value="" maxlength="150" /><label for="adminbar-search" class="screen-reader-text">Search</label><input type="submit" class="adminbar-button" value="Search"/></form></div>		</li>
		<li id="wp-admin-bar-my-account" class="menupop with-avatar"><a class="ab-item" aria-haspopup="true" href="http://photty.ru/wp-admin/profile.php">Howdy, <span class="display-name">NOVA</span><img alt='' src='http://1.gravatar.com/avatar/7fa32f20d0b17c7edd5123f6b1c7e9a3?s=26&#038;d=mm&#038;r=g' srcset='http://1.gravatar.com/avatar/7fa32f20d0b17c7edd5123f6b1c7e9a3?s=52&amp;d=mm&amp;r=g 2x' class='avatar avatar-26 photo' height='26' width='26' /></a><div class="ab-sub-wrapper"><ul id="wp-admin-bar-user-actions" class="ab-submenu">
		<li id="wp-admin-bar-user-info"><a class="ab-item" tabindex="-1" href="http://photty.ru/wp-admin/profile.php"><img alt='' src='http://1.gravatar.com/avatar/7fa32f20d0b17c7edd5123f6b1c7e9a3?s=64&#038;d=mm&#038;r=g' srcset='http://1.gravatar.com/avatar/7fa32f20d0b17c7edd5123f6b1c7e9a3?s=128&amp;d=mm&amp;r=g 2x' class='avatar avatar-64 photo' height='64' width='64' /><span class='display-name'>NOVA</span></a>		</li>
		<li id="wp-admin-bar-edit-profile"><a class="ab-item" href="http://photty.ru/wp-admin/profile.php">Edit My Profile</a>		</li>
		<li id="wp-admin-bar-logout"><a class="ab-item" href="http://photty.ru/wp-login.php?action=logout&#038;_wpnonce=1a3322eeac">Log Out</a>		</li></ul></div>		</li></ul>			</div>
						<a class="screen-reader-shortcut" href="http://photty.ru/wp-login.php?action=logout&#038;_wpnonce=1a3322eeac">Log Out</a>
					</div>

		<script>	
	window.addEventListener("load", function(){

		jQuery(document).on('click dblclick', '#fld_3092477_1', function( e ){
			jQuery('#fld_3092477_1_btn').val( e.type ).trigger('change');
		});

	});
</script>
<script type='text/javascript' src='/wp-content/themes/Divi/js/ga.js?ver=1'></script>

<script>
	jQuery(document).ready(function(){
		var opened = '';
		jQuery('.et_pb_gallery_image').click(function(){
			setTimeout(showBtn,500);
			opened = jQuery(this).parents('.et_pb_gallery_item').attr('download')
		})

		jQuery('body').on('click','a.download',function(e){
			e.preventDefault();
			opened = jQuery('a[href="'+jQuery('.mfp-img').attr('src')+'"]').parents('*[download]').attr('download');
			document.location.href=opened;
			console.log(opened);
		})
	})

	function showBtn()
	{
		jQuery('.mfp-figure').append('<a href="#" class="download"></a>');
	}

	setInterval(showImg,1000);

	function showImg(){
		jQuery('.et_pb_gallery_item').each(function(){
			if(jQuery(this).attr('style')!='display: none;'){
				jQuery(this).find('img[src2]').each(function(){
					jQuery(this).attr('src',jQuery(this).attr('src2'));
				})
			}
		})
	}

	jQuery('#CF59a7b52b33d4f_1').submit(function(e){
		e.preventDefault();
		var comment = '';
		var sel = '.caldera_forms_form label, .caldera_forms_form input, .caldera_forms_form textarea'
		var answers = ["<?=$data['title_lead']?>","<?=$data['id']?>","https://phottyru.amocrm.ru/leads/detail/<?=$data['id']?>","http://<?=$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']?>"];
		var custom = <?=$data['custom']?>;
		answers = answers.concat(custom);
		for(var i=0;i<jQuery(sel).length;i++)
		{
			var ob = jQuery(sel).eq(i)[0];
			if(jQuery(ob).hasClass('control-label'))
				comment+='\n'+jQuery(ob).html()+': ';
			if(jQuery(ob).attr('type')=='text')
			{
				answers.push(jQuery(ob).val());
				comment+=jQuery(ob).val();
			}
			if(jQuery(ob).attr('type')=='email')
			{
				answers.push(jQuery(ob).val());
				comment+=jQuery(ob).val();
			}
			if(jQuery(ob)[0].tagName=='TEXTAREA')
			{
				answers.push(jQuery(ob).val());
				comment+=jQuery(ob).val();
			}
			if((jQuery(ob).attr('type')=='radio')&&(jQuery(ob).is(':checked')))
			{
				answers.push(jQuery(ob).val());
				comment+=jQuery(ob).val();
			}
		}
		if(answers[24]=='')
			answers.splice(24, 1);
		console.log(answers);
		jQuery.get('/gallery/table.php',{keys:JSON.stringify(answers)},function(data){
			console.log(data);
		})
		jQuery('body').append('<div id="conspec" style="display: none"></div>');
		jQuery('#conspec').html(comment);
		jQuery('#conspec').find('span').remove();
		//console.log(jQuery('#conspec').html());
		jQuery.post('http://dmitrybondar.ru/auto/amo5.php',{lead_id:<?=$data['id']?>,comment:jQuery('#conspec').html()},function(data){console.log(data)})
	})

	function changeText()
	{
		jQuery('.alert-error').html('Спасибо за ваш отзыв!');
	}

	setInterval(changeText,500);
</script>
</body>
</html>