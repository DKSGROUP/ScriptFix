<?

function img_resize($src, $dest, $width, $height, $rgb = 0xFFFFFF, $quality = 100)
{  
    if (!file_exists($src))
        return false;
 
    $size = getimagesize($src);
      
    if ($size === false)
        return false;
 
    $format = strtolower(substr($size['mime'], strpos($size['mime'], '/') + 1));
    $icfunc = 'imagecreatefrom'.$format;
     
    if (!function_exists($icfunc))
        return false;
 
    $x_ratio = $width  / $size[0];
    $y_ratio = $height / $size[1];
     
    if ($height == 0)
    { 
        $y_ratio = $x_ratio;
        $height  = $y_ratio * $size[1];
    }
    elseif ($width == 0)
    { 
        $x_ratio = $y_ratio;
        $width   = $x_ratio * $size[0];
    }
     
    $ratio       = min($x_ratio, $y_ratio);
    $use_x_ratio = ($x_ratio == $ratio);
     
    $new_width   = $use_x_ratio  ? $width  : floor($size[0] * $ratio);
    $new_height  = !$use_x_ratio ? $height : floor($size[1] * $ratio);
    $new_left    = $use_x_ratio  ? 0 : floor(($width - $new_width)   / 2);
    $new_top     = !$use_x_ratio ? 0 : floor(($height - $new_height) / 2);
      
    // если не нужно увеличивать маленькую картинку до указанного размера
    if ($size[0]<$new_width && $size[1]<$new_height)
    {
        $width = $new_width = $size[0];
        $height = $new_height = $size[1];
    }
 
    $isrc  = $icfunc($src);
    $idest = imagecreatetruecolor($width, $height);
      
    imagefill($idest, 0, 0, $rgb);
    imagecopyresampled($idest, $isrc, $new_left, $new_top, 0, 0, $new_width, $new_height, $size[0], $size[1]);
 
    $i = strrpos($dest,'.');
    if (!$i) return '';
    $l = strlen($dest) - $i;
    $ext = substr($dest,$i+1,$l);
     
    switch ($ext)
    {
        case 'jpeg':
        case 'jpg':
        imagejpeg($idest,$dest,$quality);
        break;
        case 'gif':
        imagegif($idest,$dest);
        break;
        case 'png':
        imagepng($idest,$dest);
        break;
    }
 
    imagedestroy($isrc);
    imagedestroy($idest);
 
    return true;  
}

function logIt($txt)
{
	file_put_contents('data/log.log', file_get_contents('data/log.log').chr(10).chr(10).date('d.m.y H:i').chr(10).$txt);
}

function q($txt)
{
	return false;
	file_put_contents('data/q.log', file_get_contents('data/q.log').chr(10).chr(10).date('d.m.y H:i').chr(10).$txt);
}

function post($url,$data)
{
	$data_string = $data;
	$data_string = json_encode($data_string);

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS,$data_string);

	// receive server response ...
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json','Content-Length: '.strlen($data_string), 'Authorization:Token XJRbkJfxLbtwyAhQ7BQZ-MKRwI6ChuT8'
	));

	q('запрос');

	$server_output = curl_exec ($ch);

	curl_close ($ch);

	return $server_output;
}

function get($url)
{
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL,$url);

	// receive server response ...
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Authorization:Token XJRbkJfxLbtwyAhQ7BQZ-MKRwI6ChuT8'
	));

	$server_output = curl_exec ($ch);

	q('запрос');

	curl_close ($ch);

	return $server_output;
}

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

if(isset($_FILES['myface']['name']))
{
	//file_put_contents('data/'.$_FILES['myface']['name'], data);
	$name = "data/img/".time().$_FILES["myface"]["name"];

	$image = imagecreatefromstring(file_get_contents($_FILES['myface']['tmp_name']));
    $exif = exif_read_data($_FILES['myface']['tmp_name']);
    if(!empty($exif['Orientation'])) {
        switch($exif['Orientation']) {
            case 8:
                $image = imagerotate($image,90,0);
                break;
            case 3:
                $image = imagerotate($image,180,0);
                break;
            case 6:
                $image = imagerotate($image,-90,0);
                break;
        }
    }

    imagejpeg($image,$name,90);
//
    imagedestroy($image);

	//move_uploaded_file($_FILES["myface"]["tmp_name"], $name);
	img_resize($name, $name, 1280, 0);
	$data2 = array();
	$data2['photo'] = "https://photty.ru/gallery/".$name;
	$data2['mf_selector'] = 'all';
	logIt(json_encode($data2));
	//identify
	$posUploaded = json_decode(post('https://api.findface.pro/v1/faces/gallery/'.$linkPos.'/identify/',$data2),true);
	$res = array('results'=>array());
	$info = json_encode($posUploaded);
	logIt(json_encode($posUploaded['results']));
	foreach ($posUploaded['results'] as $key2 => $value2) {
		$k = 0;
		foreach ($value2 as $key3 => $value3) {
			if($value3['confidence']>=0.7)
				$k=$key3;
		}
		if(count($value2)>0)
			if($value2[$k]['confidence']<0.7)
				$value2 = array();
		if(count($value2)==0)
		{
			$info.='new';
			$data3 = array();
			$data3['photo'] = $data2['photo'];
			$data3['mf_selector'] = 'all';
			$data3['bbox'] = array(json_decode($key2,true));
			$data3['galleries'] = array($linkPos);
			$an = json_decode(post('https://api.findface.pro/v1/face/',$data3),true);
			$info.=json_encode($an);
			$an['results'][0]['size'] = getimagesize($data2['photo']);
			array_push($res['results'], $an['results'][0]);
		}else{
			$info.='old';
			$an = $value2[$k]['face'];
			$coord = json_decode($key2,true);
			$an['x1'] = $coord[0];
			$an['y1'] = $coord[1];
			$an['x2'] = $coord[2];
			$an['y2'] = $coord[3];
			$an['size'] = getimagesize($data2['photo']);
			array_push($res['results'], $an);
		}
	}
	$posUploaded = $res;
}

$positions = file_get_contents('data/'.$linkPos.'.json');
$positions = json_decode($positions,true);
$status = $positions['status'];
$sum = $positions['sum'];

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

$form = file_get_contents('https://photty.ru/dont-delete-this-post/');
$form = substr($form, strpos($form, 'et_pb_code et_pb_module  et_pb_code_2')-12);
$form = substr($form, 0, strrpos($form, 'data-field="fld_3092477"  />')).'</script></div></div>';

$faceImages = array();

$face = $_GET['face'];
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
	<link rel="pingback" href="https://photty.ru/xmlrpc.php" />

		<!--[if lt IE 9]>
	<script src="https://photty.ru/wp-content/themes/Divi/js/html5.js" type="text/javascript"></script>
	<![endif]-->

	<script type="text/javascript">
		document.documentElement.className = 'js';
	</script>

	<script>var et_site_url='https://photty.ru';var et_post_id='22020';function et_core_page_resource_fallback(a,b){"undefined"===typeof b&&(b=a.sheet.cssRules&&0===a.sheet.cssRules.length);b&&(a.onerror=null,a.onload=null,a.href?a.href=et_site_url+"/?et_core_page_resource="+a.id+et_post_id:a.src&&(a.src=et_site_url+"/?et_core_page_resource="+a.id+et_post_id))}
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
	    background-image: url(https://photty.ru/gallery/download.png);
	    background-repeat: no-repeat;
	    background-size: 40px;
	    background-position: center;
	}

	.tip{
		position: absolute;
	    left: 50px;
	    color: #fff;
	    top: 40px;
	    font-size: 14px;
	    width: 300px;
	    height: 90px;
	    background: rgba(33,33,33,.5);
	    border-radius: 5px 0 0 5px;
	    padding: 20px;
	    line-height: initial;
	    z-index: 999999999;
	}

	.download:hover{
		opacity: 1;
	}

	.face{
		position: absolute;
	    top: 130px;
	    left: 100px;
	    width: 50px;
	    height: 70px;
	    border: 2px solid #007cab;
	    border-radius: 10px;
	    box-shadow: 0 0 3px 2px #fff;
	    display: none;
	    z-index:1000;
	}

	.face:hover{
		box-shadow: none;
		border-color: #fff;
	}

	.mfp-figure:hover .face{
		display: block;
	}

	.mfp-figure.show .face{
		display: block;
	}

	body .caldera-grid .alert-danger, body .caldera-grid .alert-error{
		background-color:#dff0d8;border-color:#a3d48e;color:#3c763d
	}

	.et_pb_image_container img, .et_pb_post a img{
		width: 100%;
	}

	.window_nova{
		position: fixed;
	    top: 0;
	    left: 0;
	    width: 100%;
	    height: 100%;
	    z-index: 100000000;
	    display: none;
	}

	.window_nova .back2{
		position: absolute;
	    top: 0;
	    left: 0;
	    background: rgba(0,0,0,.3);
	    width: 100%;
	    height: 1000%;
	}

	.window_nova .window{
		max-width: 500px;
	    background: #fff;
	    padding: 30px;
	    margin: 140px auto;
	    border-radius: 10px;
	    position: relative;
	    z-index: 2;
	}

	@media (max-width: 600px)
	{
		.window_nova .window{
			margin: 0;
		}

		.window_nova{
			position: absolute;
		}

		.hmob{
			display: none;
		}
	}

	.window_nova .text{
		font-size: 13px;
    	line-height: 17px;
    	margin-bottom: 20px;
	}

	.window_nova input[type="submit"]{
		width: 100%;
	}

	.et_pb_countdown_timer_0.et_pb_countdown_timer h4 { font-weight: bold;font-size: 30px; }
	.et_pb_text_6 { font-style: italic; }
	.et_pb_section_5 { background-image:url(//photty.ru/wp-content/uploads//2017/09/1332x400dark-for-web-3.jpg); }
	.et_pb_section_5.et_pb_section { background-color:#ffffff !important; }
	.et_pb_section_3.et_pb_section { background-color:#ffffff !important; }
	.et_pb_image_0 { max-width: 200px; text-align: center; }

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

	.hiddenUploaded{
		display: none!important;
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
			window._wpemojiSettings = {"baseUrl":"https:\/\/s.w.org\/images\/core\/emoji\/2.3\/72x72\/","ext":".png","svgUrl":"https:\/\/s.w.org\/images\/core\/emoji\/2.3\/svg\/","svgExt":".svg","source":{"concatemoji":"https:\/\/photty.ru\/wp-includes\/js\/wp-emoji-release.min.js?ver=4.8.1"}};
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
<link rel='stylesheet' id='dashicons-css'  href='https://photty.ru/wp-includes/css/dashicons.min.css?ver=4.8.1' type='text/css' media='all' />
<link rel='stylesheet' id='admin-bar-css'  href='https://photty.ru/wp-includes/css/admin-bar.min.css?ver=4.8.1' type='text/css' media='all' />
<link rel='stylesheet' id='menu-icons-extra-css'  href='https://photty.ru/wp-content/plugins/menu-icons/css/extra.min.css?ver=0.9.2' type='text/css' media='all' />
<link rel='stylesheet' id='menu-image-css'  href='https://photty.ru/wp-content/plugins/menu-image/menu-image.css?ver=1.1' type='text/css' media='all' />
<link rel='stylesheet' id='twenty-twenty-css'  href='https://photty.ru/wp-content/plugins/smart-before-after-viewer/includes/twentytwenty/css/twentytwenty.min.css?ver=4.8.1' type='text/css' media='all' />
<link rel='stylesheet' id='divi-fonts-css'  href='http://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800&#038;subset=latin,latin-ext' type='text/css' media='all' />
<link rel='stylesheet' id='et-gf-comfortaa-css'  href='http://fonts.googleapis.com/css?family=Comfortaa:400,300,700&#038;subset=latin,cyrillic-ext,greek,latin-ext,cyrillic' type='text/css' media='all' />
<link rel='stylesheet' id='divi-style-css'  href='https://photty.ru/wp-content/themes/Divi/style.css?ver=2.7.5' type='text/css' media='all' />
<link rel='stylesheet' id='et-shortcodes-css-css'  href='https://photty.ru/wp-content/themes/Divi/epanel/shortcodes/css/shortcodes.css?ver=2.7.5' type='text/css' media='all' />
<link rel='stylesheet' id='cf-front-css'  href='https://photty.ru/wp-content/plugins/caldera-forms/assets/build/css/caldera-forms-front.min.css?ver=1.5.5' type='text/css' media='all' />
<link rel='stylesheet' id='et-shortcodes-responsive-css-css'  href='https://photty.ru/wp-content/themes/Divi/epanel/shortcodes/css/shortcodes_responsive.css?ver=2.7.5' type='text/css' media='all' />
<link rel='stylesheet' id='magnific-popup-css'  href='https://photty.ru/wp-content/themes/Divi/includes/builder/styles/magnific_popup.css?ver=2.7.5' type='text/css' media='all' />
<script type='text/javascript' src='https://photty.ru/wp-includes/js/jquery/jquery.js?ver=1.12.4'></script>
<script type='text/javascript' src='https://photty.ru/wp-includes/js/jquery/jquery-migrate.min.js?ver=1.4.1'></script>
<script type='text/javascript' src='https://photty.ru/wp-content/plugins/WP_Estimation_Form/assets/js/lfb_frontend.min.js?ver=9.502'></script>
<script type='text/javascript' src='https://photty.ru/wp-content/plugins/smart-before-after-viewer/includes/twentytwenty/js/jquery.event.move.min.js?ver=4.8.1'></script>
<script type='text/javascript' src='https://photty.ru/wp-content/plugins/smart-before-after-viewer/includes/twentytwenty/js/jquery.twentytwenty.min.js?ver=4.8.1'></script>
<link rel='https://api.w.org/' href='https://photty.ru/wp-json/' />
<link rel="EditURI" type="application/rsd+xml" title="RSD" href="https://photty.ru/xmlrpc.php?rsd" />
<link rel="wlwmanifest" type="application/wlwmanifest+xml" href="https://photty.ru/wp-includes/wlwmanifest.xml" /> 
<link rel='next' title='M1972' href='https://photty.ru/m1972/' />
<meta name="generator" content="WordPress 4.8.1" />
<link rel='shortlink' href='https://photty.ru/?p=22020' />
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

	<link rel="shortcut icon" href="https://photty.ru/wordpress/wp-content/uploads/2016/01/cropped-Photty-blue_mark.png" /><meta property="og:site_name" content="PHOTTY" />
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
<link rel="icon" href="https://photty.ru/wp-content/uploads//2016/08/cropped-Photty_LOGO-32x32.png" sizes="32x32" />
<link rel="icon" href="https://photty.ru/wp-content/uploads//2016/08/cropped-Photty_LOGO-192x192.png" sizes="192x192" />
<link rel="apple-touch-icon-precomposed" href="https://photty.ru/wp-content/uploads//2016/08/cropped-Photty_LOGO-180x180.png" />
<meta name="msapplication-TileImage" content="https://photty.ru/wp-content/uploads//2016/08/cropped-Photty_LOGO-270x270.png" />
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
							<link rel="stylesheet" href="https://photty.ru/wp-content/plugins/monarch/css/style.css?ver=1.3.21">
						</head>
<body class="post-template-default et_monarch single single-post postid-22020 single-format-standard logged-in admin-bar no-customize-support chrome et_pb_button_helper_class et_fullwidth_nav et_fullwidth_secondary_nav et_fixed_nav et_show_nav et_cover_background et_pb_gutter osx et_pb_gutters3 et_primary_nav_dropdown_animation_expand et_secondary_nav_dropdown_animation_fade et_pb_footer_columns4 et_header_style_left et_pb_pagebuilder_layout et_full_width_page">
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
					<a href="https://photty.ru/">
						<img src="https://photty.ru/wp-content/uploads//2017/09/Photty_LOGO-400-for-web.png" alt="PHOTTY" id="logo" data-height-percentage="100" />
					</a>
				</div>
				<div id="et-top-navigation" data-height="54" data-fixed-height="30">
											<nav id="top-menu-nav">
						<ul id="top-menu" class="nav"><li id="menu-item-714" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-714"><a href="tel:+74997033992" class="menu-image-title-after menu-image-not-hovered"><img width="36" height="36" src="https://photty.ru/wp-content/uploads//2016/02/phone-icon1-36x36.png" class="menu-image menu-image-title-after" alt="" srcset="https://photty.ru/wp-content/uploads/2016/02/phone-icon1-36x36.png 36w, https://photty.ru/wp-content/uploads/2016/02/phone-icon1-150x150.png 150w, https://photty.ru/wp-content/uploads/2016/02/phone-icon1-298x300.png 298w, https://photty.ru/wp-content/uploads/2016/02/phone-icon1-24x24.png 24w, https://photty.ru/wp-content/uploads/2016/02/phone-icon1-48x48.png 48w, https://photty.ru/wp-content/uploads/2016/02/phone-icon1.png 787w" sizes="(max-width: 36px) 100vw, 36px" /><span class="menu-image-title">+7 499 703 3992</span></a></li>
<li id="menu-item-944" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-944"><a href="mailto:welcome@photty.ru" class="menu-image-title-after menu-image-not-hovered"><img width="36" height="36" src="https://photty.ru/wp-content/uploads//2016/02/e-mail-icon2-36x36.png" class="menu-image menu-image-title-after" alt="" srcset="https://photty.ru/wp-content/uploads/2016/02/e-mail-icon2-36x36.png 36w, https://photty.ru/wp-content/uploads/2016/02/e-mail-icon2-150x150.png 150w, https://photty.ru/wp-content/uploads/2016/02/e-mail-icon2-298x300.png 298w, https://photty.ru/wp-content/uploads/2016/02/e-mail-icon2-24x24.png 24w, https://photty.ru/wp-content/uploads/2016/02/e-mail-icon2-48x48.png 48w, https://photty.ru/wp-content/uploads/2016/02/e-mail-icon2.png 787w" sizes="(max-width: 36px) 100vw, 36px" /><span class="menu-image-title">welcome@photty.ru</span></a></li>
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
					<form role="search" method="get" class="et-search-form" action="https://photty.ru/">
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

<? if(isset($_GET['face'])){ ?>

<p>Ура-ура! Мы уже подобрали все фото с вами! Мы на 99% уверены, что вы есть на этих фотографиях. *</p>

<? }else{ ?>
				
<p>Здравствуйте, <?=$data['name']?>!</p>

			</div> <!-- .et_pb_text --><div class="et_pb_text et_pb_module et_pb_bg_layout_light et_pb_text_align_left  et_pb_text_1">
				
<p>Спасибо, что выбрали PHOTTY!</p>
<p>Доступ к этой странице будет только у тех, кому вы отправите ссылку.<br />
<span class="hmob">Скачайте все ваши фото одной <a href="#downloadfromyandex">кнопкой</a> с Яндекс.Диска.</span></p>
<p>&nbsp;</p>

<? } ?>

			</div> <!-- .et_pb_text -->
			</div> <!-- .et_pb_column -->
					
			</div> <!-- .et_pb_row -->
				
			</div> <!-- .et_pb_section --><div class="et_pb_section  et_pb_section_1 et_section_regular">
					
					<div class=" et_pb_row et_pb_row_1">
				
				<div class="et_pb_column et_pb_column_4_4  et_pb_column_1">

				<? if(isset($_GET['face'])){ 
					$d = json_decode(file_get_contents('data/main_photo.json'),true);
					$d = $d[$_GET['face']];
					?>
					<h4>Поиск по лицу из фото:</h4>
					<div class="et_pb_gallery_item et_pb_grid_item et_pb_bg_layout_light">
						<div class='et_pb_gallery_image landscape'>
							<a href="<?=$d?>"></a>
							<img src="<?=$d?>" style="max-width: 200px;" />
						</div>
					</div>
				<? } ?>
				
				<div class="et_pb_module et_pb_gallery et_pb_gallery_0 et_pb_gallery_grid et_pb_bg_layout_light clearfix">
				<div class="et_pb_gallery_items et_post_gallery" data-per_page="12">

				<?
				$count = 0; 
				foreach ($photos['_embedded']['items'] as $key => $value) { 
					if(isset($_GET['face']))
					{
						if(!isset($positions[$value['name']]))
							continue;
						$isset = false;
						foreach ($positions[$value['name']]['results'] as $key2 => $value2) {
							if($value2['id']==$_GET['face'])
							{
								$isset = true;
								break;
							}
						}
						if(!$isset)
							continue;
						array_push($faceImages, $value);
					}
					$count++;

				?>
					<div class="et_pb_gallery_item et_pb_grid_item et_pb_bg_layout_light" download="<?=getDown($link,$value['path'])?>">
					<div class="positions" style="display: none"><? if((!isset($_GET['face']))&&($status>0)){echo json_encode($positions[$value['name']]);} ?></div>
					<div class='et_pb_gallery_image landscape'>
						<a href="<?=$photos_big['_embedded']['items'][$key]['preview']?>">
						<img src2="<?=$value['preview']?>" />
						<span class="et_overlay"></span>
					</a>
					</div></div>
				<? } 
				if($count==0)
				{ ?>
				<style>
					#main-content{
						display: none;
					}
					#main-content.not-found{
						display: block;
					}
				</style>
				<h3>Не найдено</h3>
				<?
				}
				?>

				<? if(isset($_FILES['myface']['name'])){ ?>
					<div class="et_pb_gallery_item et_pb_grid_item et_pb_bg_layout_light hiddenUploaded" download="https://photty.ru/gallery/<?=$name?>">
						<div class="positions" style="display: none"><?=json_encode($posUploaded)?></div>
						<div class='et_pb_gallery_image landscape'>
							<a href="https://photty.ru/gallery/<?=$name?>"></a>
							<img src="" />
						</div>
					</div>
				<? } ?>

				</div><!-- .et_pb_gallery_items --><div class='et_pb_gallery_pagination'></div></div><!-- .et_pb_gallery -->
			</div> <!-- .et_pb_column -->
					
			</div> <!-- .et_pb_row --><div class=" et_pb_row et_pb_row_2">

			<div class="et_pb_text et_pb_module et_pb_bg_layout_light et_pb_text_align_left  et_pb_text_0">

<? if(isset($_GET['face'])){ ?>

<p>* Отбор фотографий по лицу происходит машинным образом., т.е. специальная программа анализирует выбранное вами лицо и подбирает схожие с ним, возможны ошибки.</p>
<br>
<br>

<? } ?>

			</div> <!-- .et_pb_text -->
				
				<div class="">
				
				<div class="et_pb_code et_pb_module  et_pb_code_0">

				<div class="et_pb_code et_pb_module  et_pb_code_0">
				<div class="et_social_inline et_social_mobile_on et_social_inline_custom">
				<div class="et_social_networks et_social_autowidth et_social_flip et_social_rectangle et_social_left et_social_no_animation et_social_nospace et_social_outer_dark">
					
					<ul class="et_social_icons_container"><li class="et_social_facebook">
						<a href="http://www.facebook.com/sharer.php?u=<?='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']?>" class="et_social_share" rel="nofollow" data-social_name="facebook" data-post_id="26767" data-social_type="share" data-location="inline">
							<i class="et_social_icon et_social_icon_facebook"></i><span class="et_social_overlay"></span>
						</a>
					</li><li class="et_social_vkontakte">
						<a href="http://vk.com/share.php?url=<?='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']?>" class="et_social_share" rel="nofollow" data-social_name="vkontakte" data-post_id="26767" data-social_type="share" data-location="inline">
							<i class="et_social_icon et_social_icon_vkontakte"></i><span class="et_social_overlay"></span>
						</a>
					</li><li class="et_social_gmail">
						<a href="https://mail.google.com/mail/u/0/?view=cm&amp;fs=1&amp;su=general%20gallery%20sample&amp;body=<?='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']?>" class="et_social_share" rel="nofollow" data-social_name="gmail" data-post_id="26767" data-social_type="share" data-location="inline">
							<i class="et_social_icon et_social_icon_gmail"></i><span class="et_social_overlay"></span>
						</a>
					</li><li class="et_social_like">
						<a href="" class="et_social_share" rel="nofollow" data-social_name="like" data-post_id="26767" data-social_type="like" data-location="inline">
							<i class="et_social_icon et_social_icon_like"></i><span class="et_social_overlay"></span>
						</a>
					</li></ul>
				</div>
			</div>
			</div>

			</div> <!-- .et_pb_code -->
			<? if((!isset($_GET['face']))&&($status>0)){ ?>
			<div class="et_pb_text et_pb_module et_pb_bg_layout_light et_pb_text_align_left  et_pb_text_1">
				
<h2 style="text-align: center;">НАЙДИТЕ СЕБЯ!</h2>
<p>Хотите в один клик найти все фото с собой? Просто выберите себя на фото в галерее и кликните на синий прямоугольник! Как вариант, можно сделать селфи или загрузить собственное фото, по которому программа найдет все ваши изображения с данной съемки.</p>

			</div>
			<? } ?>
			</div> <!-- .et_pb_column --><div class="et_pb_column et_pb_column_1_4  et_pb_column_3 et_pb_column_empty">
				
				
			</div> <!-- .et_pb_column --><div class="et_pb_column et_pb_column_1_4  et_pb_column_4 et_pb_column_empty">
				
				
			</div> <!-- .et_pb_column --><div class="et_pb_column et_pb_column_1_4  et_pb_column_5 et_pb_column_empty">
				
				
			</div> <!-- .et_pb_column -->
					
			</div> <!-- .et_pb_row -->
				
			</div> <!-- .et_pb_section -->
			<? if((!isset($_GET['face']))&&($status>0)){ ?>
			<div class="et_pb_section  et_pb_section_2 et_section_regular">
				
				
					
					<div class=" et_pb_row et_pb_row_4">
				
				<!-- .et_pb_column --><div class="et_pb_column et_pb_column_1_2  et_pb_column_8">
				
				<div class="et_pb_code et_pb_module  et_pb_code_2" style="margin-bottom: 10px;">
				<a name="downloadfromyandex"></a>
			</div> <!-- .et_pb_code --><div class="et_pb_module et-waypoint et_pb_image et_pb_animation_off et_pb_image_0 et_always_center_on_mobile" style="max-width: 100%;">
				<form id="myface-form" method="post" enctype="multipart/form-data">
					<input type="file" name="myface" id="myface" accept="image/*" style="display: none">
				</form>
				<a href="#" class="myface-a" style=""><img src="https://photty.ru/wp-content/uploads//2017/10/upload-selfie-here-for-web-1.png" alt="" style="width:300px"/>
			</a>
			</div><div class="et_pb_text et_pb_module et_pb_bg_layout_light et_pb_text_align_left  et_pb_text_3">
				
<p>&nbsp;</p>

			</div> <!-- .et_pb_text -->
			</div> <!-- .et_pb_column -->

			<div class="et_pb_column et_pb_column_1_2  et_pb_column_7">
				
				<div class="et_pb_text et_pb_module et_pb_bg_layout_light et_pb_text_align_left  et_pb_text_2">
				
<p style="padding-top: 30px;">Также можно найти себя, загрузив “контрольное” фото с собой, например, просто сделав селфи. Загрузите фотографию, кликните на свое изображение, и ваша личная фотогалерея будет готова!</p>

			</div> <!-- .et_pb_text -->
			</div> 
					
			</div> <!-- .et_pb_row -->
				
			</div>
			<? } ?>
			<!-- .et_pb_section --><div class="et_pb_section  et_pb_section_2 et_section_regular hmob">
				
				
					
					<div class=" et_pb_row et_pb_row_4 ydisk-down" <? if(isset($_GET['face'])){ ?> style="display: none" <? } ?>>
				
				<div class="et_pb_column et_pb_column_1_2  et_pb_column_7">
				
				<div class="et_pb_text et_pb_module et_pb_bg_layout_light et_pb_text_align_left  et_pb_text_2">
				
<p>Для печати или хранения в архиве стоит использовать фотографии в полном размере. Пожалуйста, нажмите на кнопку &#8220;СКАЧАТЬ ВСЕ ФОТО&#8221;, чтобы скачать полноразмерные фото.</p>
<div style="display: none" class="data-ydisk">
<?=json_encode(array("faceImages"=>$faceImages,"linkPos"=>$linkPos,"id"=>$_GET['id'],"face"=>$face));?>
</div>

			</div> <!-- .et_pb_text -->
			</div> <!-- .et_pb_column --><div class="et_pb_column et_pb_column_1_2  et_pb_column_8">
				
				<div class="et_pb_code et_pb_module  et_pb_code_2">
				<a name="downloadfromyandex"></a>
			</div> <!-- .et_pb_code --><div class="et_pb_module et-waypoint et_pb_image et_pb_animation_off et_pb_image_0 et_always_center_on_mobile" style="max-width: 300px;">
				<? if(isset($_GET['face'])){ ?>
				<a href="<?=$data['href']?>" class="ydisk-down-a" target="_blank"><img src="https://photty.ru/wp-content/uploads//2017/04/download_button_400px.jpg" width="300px" alt="" />
				<? }else{ ?>
				<a href="<?=$data['href']?>" target="_blank"><img src="https://photty.ru/wp-content/uploads//2017/04/download_button_400px.jpg" width="300px" alt="" />
				<? } ?>
			</a>
			</div><div class="et_pb_text et_pb_module et_pb_bg_layout_light et_pb_text_align_left  et_pb_text_3">
				
<p>&nbsp;</p>

			</div> <!-- .et_pb_text -->
			</div> <!-- .et_pb_column -->
					
			</div> <!-- .et_pb_row -->

			<? if(isset($_GET['face'])){ ?>

			<div class=" et_pb_row et_pb_row_4 loading-ydisk">
				
				<div class="et_pb_column et_pb_column_1_2  et_pb_column_7">
				
				<div class="et_pb_text et_pb_module et_pb_bg_layout_light et_pb_text_align_left  et_pb_text_2">
				
<p>Для печати или хранения в архиве стоит использовать фотографии в полном размере. Пожалуйста, нажмите на кнопку &#8220;СКАЧАТЬ ВСЕ ФОТО&#8221;, чтобы скачать полноразмерные фото.</p>
<div style="display: none" class="data-ydisk">
<?=json_encode(array("faceImages"=>$faceImages,"linkPos"=>$linkPos,"id"=>$_GET['id'],"face"=>$face));?>
</div>

			</div> <!-- .et_pb_text -->
			</div> <!-- .et_pb_column --><div class="et_pb_column et_pb_column_1_2  et_pb_column_8">
				<div class="et_pb_module et-waypoint et_pb_image et_pb_animation_off et_pb_image_0 et_always_center_on_mobile" style="max-width: 300px;">
				<p style="line-height: initial;">Мы собираем папку с вашими фотографиями</p>
				<img src="/gallery/5.gif" alt="" style="margin-top: 20px;">
			</a>
			</div><div class="et_pb_text et_pb_module et_pb_bg_layout_light et_pb_text_align_left  et_pb_text_3">
				
<p>&nbsp;</p>

			</div> <!-- .et_pb_text -->
			</div> <!-- .et_pb_column -->
					
			</div> <!-- .et_pb_row -->

			<? } ?>
				
			</div> <!-- .et_pb_section -->

			<? if(!isset($_GET['face'])){ ?>
			<div class="et_pb_section  et_pb_section_4 et_pb_with_background et_section_regular">
				
				
					
					<div class=" et_pb_row et_pb_row_5">
				
				<div class="et_pb_column et_pb_column_4_4  et_pb_column_9">
			</div> <!-- .et_pb_column -->
					
			</div> <!-- .et_pb_row --><div class=" et_pb_row et_pb_row_6">
				
				<div class="et_pb_column et_pb_column_4_4  et_pb_column_10">
				
				<?=$form?>
			</div> <!-- .et_pb_column -->
					
			</div> <!-- .et_pb_row -->
				
			</div> <!-- .et_pb_section -->
			<? } ?>
			<div class="et_pb_section  et_pb_section_3 et_pb_with_background et_section_regular">
				
				
					
					<div class=" et_pb_row et_pb_row_5">
				
				<div class="et_pb_column et_pb_column_4_4  et_pb_column_7">
				
				<div class="et_pb_text et_pb_module et_pb_bg_layout_light et_pb_text_align_left  et_pb_text_5">
				

<h2 style="text-align: center;">ВЫЗВАТЬ ФОТОГРАФА ПРЯМО СЕЙЧАС?</h2>


			</div> <!-- .et_pb_text -->
			</div> <!-- .et_pb_column -->
					
			</div> <!-- .et_pb_row --><div class=" et_pb_row et_pb_row_6">
				
				<div class="et_pb_column et_pb_column_1_2  et_pb_column_8">
				
				<div class="et_pb_text et_pb_module et_pb_bg_layout_light et_pb_text_align_left  et_pb_text_6">
				

<h3></h3>
<h3>Это просто как раз-два-три!</h3>
<ol>
<li>Заполните форму;</li>
<li>Укажите свои контакты;</li>
<li>Подтвердите менеджеру заказ.</li>
</ol>
<h4>все!</h4>
<p>Пожалуйста, обратите внимание, что мы не сможем связаться с вами, если в форме будут указаны ошибочные контакты. Спасибо!</p>
<p style="text-align: center;">


			</p></div> <!-- .et_pb_text -->
			</div> <!-- .et_pb_column --><div class="et_pb_column et_pb_column_1_2  et_pb_column_9">
				
				<div class="et_pb_code et_pb_module  et_pb_code_3">
				<a name="orderamocrm"></a><div class="caldera-grid" id="caldera_form_2" data-cf-ver="1.5.5" data-cf-form-id="CF59abe5c476e78"><div id="caldera_notices_2" data-spinner="https://photty.ru/wp-admin/images/spinner.gif"></div><form data-instance="2" class="CF59abe5c476e78 caldera_forms_form cfajax-trigger _tisBound" method="POST" enctype="multipart/form-data" role="form" id="CF59abe5c476e78_2" data-target="#caldera_notices_2" data-template="#cfajax_CF59abe5c476e78-tmpl" data-cfajax="CF59abe5c476e78" data-load-element="_parent" data-load-class="cf_processing" data-post-disable="0" data-action="cf_process_ajax_submit" data-request="https://photty.ru/cf-api/CF59abe5c476e78" data-hiderows="true">
<input type="hidden" id="_cf_verify_CF59abe5c476e78" name="_cf_verify" value="f9d6e5249e" data-nonce-time="1507578620"><input type="hidden" name="_wp_http_referer" value="/general-gallery-sample/"><input type="hidden" name="_cf_frm_id" value="CF59abe5c476e78">
<input type="hidden" name="_cf_frm_ct" value="2">
<input type="hidden" name="cfajax" value="CF59abe5c476e78">
<input type="hidden" name="_cf_cr_pst" value="26767">
<div class="hide" style="display:none; overflow:hidden;height:0;width:0;">
<label>Company</label><input type="text" name="company" value="" autocomplete="off" style="background-image: url(&quot;data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAABHklEQVQ4EaVTO26DQBD1ohQWaS2lg9JybZ+AK7hNwx2oIoVf4UPQ0Lj1FdKktevIpel8AKNUkDcWMxpgSaIEaTVv3sx7uztiTdu2s/98DywOw3Dued4Who/M2aIx5lZV1aEsy0+qiwHELyi+Ytl0PQ69SxAxkWIA4RMRTdNsKE59juMcuZd6xIAFeZ6fGCdJ8kY4y7KAuTRNGd7jyEBXsdOPE3a0QGPsniOnnYMO67LgSQN9T41F2QGrQRRFCwyzoIF2qyBuKKbcOgPXdVeY9rMWgNsjf9ccYesJhk3f5dYT1HX9gR0LLQR30TnjkUEcx2uIuS4RnI+aj6sJR0AM8AaumPaM/rRehyWhXqbFAA9kh3/8/NvHxAYGAsZ/il8IalkCLBfNVAAAAABJRU5ErkJggg==&quot;); background-repeat: no-repeat; background-attachment: scroll; background-size: 16px 18px; background-position: 98% 50%;">
</div><div id="CF59abe5c476e78_2-row-1" class="row  first_row"><div class="col-sm-12  single"><div role="field" data-field-wrapper="fld_185917" class="form-group" id="fld_185917_2-wrap">
	<label id="fld_185917Label" for="fld_185917_2" class="control-label">Как вас зовут? <span aria-hidden="true" role="presentation" class="field_required" style="color:#ee0000;">*</span></label>
	<div class="">
		<input required="" type="text" data-field="fld_185917" class=" form-control" id="fld_185917_2" name="fld_185917" value="" data-type="text" aria-required="true" aria-labelledby="fld_185917Label">			</div>
</div>
</div></div><div id="CF59abe5c476e78_2-row-2" class="row "><div class="col-sm-12  single"><div role="field" data-field-wrapper="fld_2254781" class="form-group" id="fld_2254781_2-wrap">
	<label id="fld_2254781Label" for="fld_2254781_2" class="control-label">Телефон для связи <span aria-hidden="true" role="presentation" class="field_required" style="color:#ee0000;">*</span></label>
	<div class="">
		<input placeholder="+74997033992" +99="" 99="" 999="" 9999="" required="" type="phone" data-field="fld_2254781" class=" form-control" id="fld_2254781_2" name="fld_2254781" value="" data-type="phone" aria-required="true" aria-labelledby="fld_2254781Label">			</div>
</div>
<div role="field" data-field-wrapper="fld_7900587" class="form-group" id="fld_7900587_2-wrap">
	<label id="fld_7900587Label" for="fld_7900587_2" class="control-label">Email <span aria-hidden="true" role="presentation" class="field_required" style="color:#ee0000;">*</span></label>
	<div class="">
		<input placeholder="welcome@photty.ru" required="" type="email" data-field="fld_7900587" class=" form-control" id="fld_7900587_2" name="fld_7900587" value="" data-type="email" aria-required="true" aria-labelledby="fld_7900587Label">			</div>
</div>
</div></div><div id="CF59abe5c476e78_2-row-3" class="row  last_row"><div class="col-sm-12  single"><div role="field" data-field-wrapper="fld_4089741" class="form-group" id="fld_4089741_2-wrap">
	<label id="fld_4089741Label" for="fld_4089741_2" class="control-label">Комментарии</label>
	<div class="">
		<textarea name="fld_4089741" value="" data-field="fld_4089741" class="form-control" id="fld_4089741_2" rows="4" aria-labelledby="fld_4089741Label"></textarea>
			</div>
</div>
<div role="field" data-field-wrapper="fld_7167496" class="form-group" id="fld_7167496_2-wrap">
<div class="">
	<input class="btn btn-default" type="submit" name="fld_7167496" id="fld_7167496_2" value="ЗАКАЗАТЬ" data-field="fld_7167496">
</div>
</div>
	<input class="button_trigger_2" type="hidden" name="fld_7167496" id="fld_7167496_2_btn" value="" data-field="fld_7167496">
</div></div></form>
</div>
<p>Нажимая кнопку "ЗАКАЗАТЬ", я даю&nbsp;<a href="//photty.ru/agreement/" target="_blank" rel="noopener">согласие</a>&nbsp;на обработку персональных данных.</p>
			</div> <!-- .et_pb_code -->
			</div> <!-- .et_pb_column -->
					
			</div> <!-- .et_pb_row -->
				
			</div>
			<div class="et_pb_section  et_pb_section_5 et_pb_with_background et_section_regular">
				
				
					
					<div class=" et_pb_row et_pb_row_7">
				
				<div class="et_pb_column et_pb_column_4_4  et_pb_column_9">
				
				<div class="et_pb_module et_pb_countdown_timer et_pb_bg_layout_dark  et_pb_countdown_timer_0" data-end-timestamp="1509483540">
				<div class="et_pb_countdown_timer_container clearfix">
					<h4 class="title">ЗАКАЖИТЕ СЕЙЧАС СЪЕМКУ В СЛЕДУЮЩЕМ МЕСЯЦЕ И ПОЛУЧИТЕ СКИДКУ 10%</h4>
					<div class="days section values" data-short="Day" data-full="Day(s)">
						<p class="value">020</p>
						<p class="label">Day(s)</p>
					</div>
					<div class="sep section"><p>:</p></div>
					<div class="hours section values" data-short="Hrs" data-full="Hour(s)">
						<p class="value">14</p>
						<p class="label">Hour(s)</p>
					</div>
					<div class="sep section"><p>:</p></div>
					<div class="minutes section values" data-short="Min" data-full="Minute(s)">
						<p class="value">03</p>
						<p class="label">Minute(s)</p>
					</div>
					<div class="sep section"><p>:</p></div>
					<div class="seconds section values" data-short="Sec" data-full="Second(s)">
						<p class="value">04</p>
						<p class="label">Second(s)</p>
					</div>
				</div>
			</div>
			</div> <!-- .et_pb_column -->
					
			</div> <!-- .et_pb_row --><div class=" et_pb_row et_pb_row_8">
				
				<div class="et_pb_column et_pb_column_4_4  et_pb_column_10">
				
				<div class="et_pb_text et_pb_module et_pb_bg_layout_dark et_pb_text_align_right  et_pb_text_6">
				

<p>Скидка действительна при 100%-й предоплате съемки в следующем месяце, только для заказов свыше 20'000 руб (до скидки). Скидка не распространяется на подарочные сертификаты.</p>
<p style="text-align: center;">
</p><p style="text-align: center;">


			</p></div> <!-- .et_pb_text -->
			</div> <!-- .et_pb_column -->
					
			</div> <!-- .et_pb_row -->
				
			</div>
					</div> <!-- .entry-content -->
					<div class="et_post_meta_wrapper">
					
					
										</div> <!-- .et_post_meta_wrapper -->
				</article> <!-- .et_pb_post -->

						</div> <!-- #left-area -->

					</div> <!-- #content-area -->
	</div> <!-- .container -->

	</div>

	<style>
		.not-found{
			display: none;
		}
	</style>

	<div id="main-content" class="not-found" style="min-height: 590px;">
	<div class="container">
		<div id="content-area" class="clearfix">
			<div id="left-area">
							
				
				<article id="post-26786" class="et_pb_post post-26786 post type-post status-publish format-standard hentry category-clients-gallery">
					
					<div class="entry-content">
					<div class="et_pb_section  et_pb_section_0 et_section_regular et_pb_section_sticky et_pb_section_sticky_mobile">
				
				
					
					<div class=" et_pb_row et_pb_row_0">
				
				<div class="et_pb_column et_pb_column_4_4  et_pb_column_0 et_pb_row_sticky">
				
				<div class="et_pb_text et_pb_module et_pb_bg_layout_light et_pb_text_align_left  et_pb_text_0">
				
<h2 style="text-align: center;">МЫ НИЧЕГО НЕ НАШЛИ ;(</h2>
<p>&nbsp;</p>

			</div> <!-- .et_pb_text --><div class="et_pb_module et-waypoint et_pb_image et_pb_animation_off et_pb_image_0 et_always_center_on_mobile et-animated">
				<img src="https://photty.ru/wp-content/uploads//2017/10/sad-bird.png" style="max-width: 270px;" alt="">
			
			</div><div class="et_pb_text et_pb_module et_pb_bg_layout_light et_pb_text_align_center  et_pb_text_1">
				
<p>Иногда так бывает: либо у нас действительно нет вашей фотографии, либо программа не смогла вас узнать.</p>
<p><a href="mailto:welcome@photty.ru">Напишите</a> нам, пожалуйста, и мы полностью возместим вам стоимость отбора фотографий.</p>

			</div> <!-- .et_pb_text --><div class="et_pb_module et-waypoint et_pb_image et_pb_animation_off et_pb_image_1 et_always_center_on_mobile et_pb_image_sticky et-animated" style="text-align: center;">
				<a href="../"><img src="//photty.ru/wp-content/uploads//2017/10/back-to-gallery-for-web.png" alt="" style="max-width: 350px;"></a>
			
			</div>
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
						<ul id="menu-footer-menu" class="bottom-nav"><li id="menu-item-1876" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-1876"><a href="https://photty.ru/event-photo/" class="menu-image-title-after"><span class="menu-image-title">Фотограф на мероприятие</span></a></li>
<li id="menu-item-1877" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-1877"><a href="https://photty.ru/Family-photo/" class="menu-image-title-after"><span class="menu-image-title">Семейные фотосессии</span></a></li>
<li id="menu-item-1878" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-1878"><a href="https://photty.ru/portrait/" class="menu-image-title-after"><span class="menu-image-title">Портретная съемка</span></a></li>
<li id="menu-item-1879" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-1879"><a href="https://photty.ru/food-photo/" class="menu-image-title-after"><span class="menu-image-title">Съемка блюд</span></a></li>
<li id="menu-item-2538" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-2538"><a href="https://photty.ru/weddings/" class="menu-image-title-after"><span class="menu-image-title">Фотограф на свадьбу</span></a></li>
<li id="menu-item-4215" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-4215"><a href="https://photty.ru/business-photo/" class="menu-image-title-after"><span class="menu-image-title">Деловой портрет</span></a></li>
<li id="menu-item-4216" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-4216"><a href="https://photty.ru/studio-faq/" class="menu-image-title-after"><span class="menu-image-title">Подготовка к студии</span></a></li>
<li id="menu-item-1968" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-1968"><a href="https://photty.ru/pricing" class="menu-image-title-after"><span class="menu-image-title">Расчет стоимости</span></a></li>
<li id="menu-item-2952" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-2952"><a href="https://photty.ru/projects/" class="menu-image-title-after"><span class="menu-image-title">Проекты</span></a></li>
<li id="menu-item-1623" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-1623"><a href="https://photty.ru/faq" class="menu-image-title-after"><span class="menu-image-title">F.A.Q.</span></a></li>
<li id="menu-item-1564" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-1564"><a href="https://photty.ru/dogovor-oferta/" class="menu-image-title-after"><span class="menu-image-title">Договор-оферта</span></a></li>
<li id="menu-item-1880" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-1880"><a href="https://photty.SG" class="menu-image-title-after menu-image-not-hovered"><img width="36" height="24" src="https://photty.ru/wp-content/uploads//2016/08/sg-flag-36x24.png" class="menu-image menu-image-title-after" alt="" srcset="https://photty.ru/wp-content/uploads/2016/08/sg-flag-36x24.png 36w, https://photty.ru/wp-content/uploads/2016/08/sg-flag-24x16.png 24w, https://photty.ru/wp-content/uploads/2016/08/sg-flag-48x32.png 48w, https://photty.ru/wp-content/uploads/2016/08/sg-flag.png 250w" sizes="(max-width: 36px) 100vw, 36px" /><span class="menu-image-title">PHOTTY.SG</span></a></li>
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
<!-- {/literal} END JIVOSITE CODE --><script type='text/javascript' src='https://photty.ru/wp-includes/js/admin-bar.min.js?ver=4.8.1'></script>
<script type='text/javascript' src='https://photty.ru/wp-content/themes/Divi/includes/builder/scripts/frontend-builder-global-functions.js?ver=2.7.5'></script>
<script type='text/javascript' src='https://photty.ru/wp-includes/js/comment-reply.min.js?ver=4.8.1'></script>
<script type='text/javascript' src='https://photty.ru/wp-content/themes/Divi/includes/builder/scripts/jquery.mobile.custom.min.js?ver=2.7.5'></script>
<script type='text/javascript' src='https://photty.ru/wp-content/themes/Divi/js/custom.js?ver=2.7.5'></script>
<script type='text/javascript'>
/* <![CDATA[ */
var FB_WP=FB_WP||{};FB_WP.queue={_methods:[],flushed:false,add:function(fn){FB_WP.queue.flushed?fn():FB_WP.queue._methods.push(fn)},flush:function(){for(var fn;fn=FB_WP.queue._methods.shift();){fn()}FB_WP.queue.flushed=true}};window.fbAsyncInit=function(){FB.init({"xfbml":true});if(FB_WP && FB_WP.queue && FB_WP.queue.flush){FB_WP.queue.flush()}}
/* ]]> */
</script>
<script type="text/javascript">(function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(d.getElementById(id)){return}js=d.createElement(s);js.id=id;js.src="http:\/\/connect.facebook.net\/en_US\/all.js";fjs.parentNode.insertBefore(js,fjs)}(document,"script","facebook-jssdk"));</script>
<script type='text/javascript' src='https://photty.ru/wp-content/themes/Divi/includes/builder/scripts/jquery.fitvids.js?ver=2.7.5'></script>
<script type='text/javascript' src='https://photty.ru/wp-content/themes/Divi/includes/builder/scripts/waypoints.min.js?ver=2.7.5'></script>
<script type='text/javascript' src='https://photty.ru/wp-content/themes/Divi/includes/builder/scripts/jquery.magnific-popup.js?ver=2.7.5'></script>
<script type='text/javascript'>
/* <![CDATA[ */
var et_pb_custom = {"ajaxurl":"https:\/\/photty.ru\/wp-admin\/admin-ajax.php","images_uri":"https:\/\/photty.ru\/wp-content\/themes\/Divi\/images","builder_images_uri":"https:\/\/photty.ru\/wp-content\/themes\/Divi\/includes\/builder\/images","et_frontend_nonce":"23002219f1","subscription_failed":"Please, check the fields below to make sure you entered the correct information.","et_ab_log_nonce":"1568244e69","fill_message":"Please, fill in the following fields:","contact_error_message":"Please, fix the following errors:","invalid":"Invalid email","captcha":"Captcha","prev":"Prev","previous":"Previous","next":"Next","wrong_captcha":"You entered the wrong number in captcha.","is_builder_plugin_used":"","is_divi_theme_used":"1","widget_search_selector":".widget_search","is_ab_testing_active":"","page_id":"22020","unique_test_id":"","ab_bounce_rate":"5","is_cache_plugin_active":"no","is_shortcode_tracking":""};
/* ]]> */
</script>
<script type='text/javascript' src='https://photty.ru/wp-content/themes/Divi/includes/builder/scripts/frontend-builder-scripts.js?ver=2.7.5'></script>
<script type='text/javascript' src='https://photty.ru/wp-includes/js/wp-embed.min.js?ver=4.8.1'></script>
<script type='text/javascript' src='https://photty.ru/wp-content/themes/Divi/includes/builder/scripts/jquery.hashchange.js?ver=2.7.5'></script>
<script type='text/javascript' src='https://photty.ru/wp-content/plugins/caldera-forms/assets/build/js/jquery-baldrick.min.js?ver=1.5.5'></script>
<script type='text/javascript' src='https://photty.ru/wp-content/plugins/caldera-forms/assets/build/js/ajax-core.min.js?ver=1.5.5'></script>
<script type='text/javascript' src='https://photty.ru/wp-content/plugins/caldera-forms/assets/build/js/conditionals.min.js?ver=1.5.5'></script>
<script type='text/javascript' src='https://photty.ru/wp-content/plugins/caldera-forms/assets/build/js/parsley.min.js?ver=1.5.5'></script>
<script type='text/javascript'>
/* <![CDATA[ */
var CF_API_DATA = {"rest":{"root":"https:\/\/photty.ru\/wp-json\/cf-api\/v2\/","tokens":{"nonce":"https:\/\/photty.ru\/wp-json\/cf-api\/v2\/tokens\/form"},"nonce":"77e2d29e91"},"nonce":{"field":"_cf_verify"}};
/* ]]> */
</script>
<script type='text/javascript' src='https://photty.ru/wp-content/plugins/caldera-forms/assets/build/js/caldera-forms-front.min.js?ver=1.5.5'></script>
<div id="fb-root"></div><script type='text/javascript'>
/* <![CDATA[ */
var CF_API_DATA = {"rest":{"root":"https:\/\/photty.ru\/wp-json\/cf-api\/v2\/","tokens":{"nonce":"https:\/\/photty.ru\/wp-json\/cf-api\/v2\/tokens\/form"},"nonce":"77e2d29e91"},"nonce":{"field":"_cf_verify"}};
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
			

		<script>	
	window.addEventListener("load", function(){

		jQuery(document).on('click dblclick', '#fld_3092477_1', function( e ){
			jQuery('#fld_3092477_1_btn').val( e.type ).trigger('change');
		});

	});
</script>
<script type='text/javascript' src='/wp-content/themes/Divi/js/ga.js?ver=1'></script>

<script>
	var fore;
	jQuery(document).ready(function(){
		var opened = '';
		var openedFace = '';
		jQuery('body').on('click','.face',function(){
			var sum = jQuery('button[sum]').attr('sum');
			var id = jQuery(this).attr('face-id');
			jQuery.post('/gallery/save_main_photo.php',{id:id,photo:jQuery('img.mfp-img').attr('src')},function(){
				jQuery.get('/payment/payed.json',{r:parseInt(Math.random()*1000000)},function(data){
					console.log(data);
					if(data['<?=$_GET['id']?>'+id]==true)
						sum=0;
					if(sum==0)
					{
						window.location.href="/gallery/<?=$_GET['id']?>/"+id+'/?utm_source=gallery&utm_medium=findface';
						return true;
					}
					jQuery('.window_nova').show();
					jQuery(window).scrollTop(0);
					jQuery('.mfp-image-holder').click();
					openedFace = id;
				},'json')
			})
		})
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

		jQuery('.window_nova .back2').click(function(){
			jQuery('.window_nova').hide();
		})

		jQuery('.myface-a').click(function(e){
			e.preventDefault();
			jQuery('#myface').click();
		})

		jQuery('body').on('DOMNodeInserted', '.mfp-content', showFaces);

		jQuery('body').on('click','.mfp-figure',showFaces);

		jQuery('#myface').change(function(){
			if(jQuery(this).val()!='')
				jQuery('#myface-form').submit();
		})

		if(jQuery('.et_pb_gallery_item.hiddenUploaded .et_pb_gallery_image a').length>0)
		{
			jQuery('.et_pb_gallery_item.hiddenUploaded .et_pb_gallery_image a').click();
			setTimeout('jQuery(".mfp-figure").addClass("show")',500);
			//alert(1);
		}

		setInterval("showFaces({target:jQuery('.mfp-content')})",3000);

		jQuery('.pay-form').submit(function(e){
			e.preventDefault();
			var or = (new Date).getTime();
			jQuery.post('/gallery/amo5.php',{name:'findface'+or,email:jQuery(this).find('input[name=email]').val(),tags:'findface',ph:'true',url:"https://photty.ru/gallery/<?=$_GET['id']?>/"+openedFace+'/?utm_source=gallery&utm_medium=findface'});
			jQuery.post('/gallery/amo5.php',{lead_id:<?=$data['id']?>,comment:'Форма распознавания лица\nEmail: '+jQuery(this).find('input[name=email]').val()+'\nНомер заказа:'+or});
			//pay or not
			var sum = jQuery(this).find('button').attr('sum');
			if(sum==0)
				window.location.href="/gallery/<?=$_GET['id']?>/"+openedFace+'/';
			else
			{
				//console.log("https://money.yandex.ru/eshop.xml?shopId=119971&scid=47328&sum="+sum+".00&customerNumber="+or+"&orderNumber="+or+"www<?=$_GET['id']?>www"+openedFace+"&shopDefaultUrl=https://photty.ru/gallery/<?=$_GET['id']?>/&email=test@test.ru");
				window.location.href="https://money.yandex.ru/eshop.xml?shopId=119971&scid=47328&sum="+sum+".00&customerNumber="+or+"&orderNumber="+or+"www<?=$_GET['id']?>www"+openedFace+"&shopDefaultUrl=https://photty.ru/gallery/<?=$_GET['id']?>/";
			}
		})

		jQuery.post('/gallery/ydisk_save.php',{data:jQuery('.data-ydisk').html()},function(data){
			jQuery('.ydisk-down-a').attr('href',data);
			jQuery('.ydisk-down').show();
			jQuery('.loading-ydisk').hide();
		})

		jQuery('#CF59abe5c476e78_2').submit(function(e){
			e.preventDefault();
			jQuery.post('/gallery/amo5.php',{name:jQuery('#fld_185917_2').val(),tel:jQuery('#fld_2254781_2').val(),email:jQuery('#fld_7900587_2').val(),comment:jQuery('#fld_4089741_2').val(),ph:'true'},function(){
				jQuery('#fld_7167496_2').parent().append('<p>Отправлено</p>');
			})
		})
	})

	function showFaces (e) {
		//alert(1);
		fore=e;
		console.log('test');
  		if((!jQuery(e.target).hasClass('mfp-content'))&&(!jQuery(e.target).hasClass('download')))
  			return true;
		var del = (jQuery('.mfp-img').width()/1280)>(jQuery('.mfp-img').height()/1280)?(jQuery('.mfp-img').width()/1280):(jQuery('.mfp-img').height()/1280);
		//del=1;
		var pos = jQuery('a[href="'+jQuery('.mfp-img').attr('src')+'"]').parents('*[download]').find('.positions').html();
		pos = jQuery.parseJSON(pos);
		pos = pos.results;
		console.log(pos);
		jQuery('.mfp-figure .face').remove();
		for(var i in pos)
		{
			if(pos[i]==null)
				continue;
			if(pos[i].id==undefined)
				continue;
			del = jQuery('.mfp-img').width()/pos[i].size[0];
			jQuery('.mfp-figure').append('<div class="face" face-id="'+pos[i].id+'" style="top:'+((pos[i].y1)*del+40)+'px;left:'+pos[i].x1*del+'px;width:'+(pos[i].x2-pos[i].x1)*del+'px;height:'+(pos[i].y2-pos[i].y1)*del+'px;"></div>');
			//alert('top:'+((pos[i].y1)*del+40)+'px;left:'+pos[i].x1*del+'px;width:'+(pos[i].x2-pos[i].x1)*del+'px;height:'+(pos[i].y2-pos[i].y1)*del+'px;');
		}

		//setInterval("$('.mfp-content').trigger('DOMNodeInserted');",3000);
	}

	function showBtn()
	{
		jQuery('.mfp-figure').append('<a href="#" class="download"></a>');
		<? if(($status>0)&&(!isset($_GET['face']))){ ?>
		jQuery('.mfp-container').append('<div class="tip">Вы можете собрать персональную галерею, просто кликните на лицо</div>');
		<? } ?>
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
		jQuery.post('https://photty.ru/amo/amo5.php',{lead_id:<?=$data['id']?>,comment:jQuery('#conspec').html()},function(data){console.log(data)})
	})

	function changeText()
	{
		jQuery('.alert-error').html('Спасибо за ваш отзыв!');
	}

	setInterval(changeText,500);
</script>

<div class="window_nova">
	<div class="back2"></div>
	<div class="window">
		<h3 style="text-align: center">Хотите собрать все свои фото? Просто нажмите на кнопку :)</h3>
		<form class="caldera-grid pay-form">
			<div class="form-group">
				<input type="email" name="email" class="form-control" placeholder="Email адрес" required style="margin-top: 20px;">
			</div>
			<div class="form-group">
				<button class="btn btn-default" sum="<?=$sum?>" style="width: 100%">НАЙТИ МОИ ФОТО<br><?=$sum?> руб.</button>
				<p style="font-size: 8.5px;line-height: initial;margin-top: 15px;text-align: center;">Нажимая на кнопку "НАЙТИ МОИ ФОТО", я даю согласие на обработку персональных данных</p>
			</div>
		</form>
	</div>
</div>
</body>
</html>