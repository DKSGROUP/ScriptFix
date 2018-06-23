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
if(false /*$cache*/)
    $photos = $cache;
else
    $photos = file_get_contents($l1);

cache_it($l1, $photos);
$photos = json_decode($photos,true);

$l2 = 'https://cloud-api.yandex.net/v1/disk/public/resources?limit=10000&preview_size=XXXL&public_key='.urlencode($link);

$cache = get_cache($l2);
if(false /*$cache*/)
    $photos_big = $cache;
else
    $photos_big = file_get_contents($l2);

cache_it($l2, $photos_big);
//print_r($photos_big);
$photos_big = json_decode($photos_big,true);
//print_r($photos_big);

$data_link = urlencode($data['link']);
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
    <meta http-equiv="Cache-Control" content="no-cache">
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
            //background: url('../logo.png'); /*скрыли лого с превью*/
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
            //background: url('../logo.png'); /*скрыли лого с фото*/
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
    <link rel='stylesheet' id='menu-icons-extra-css'  href='https://photty.ru/wp-content/plugins/menu-icons/css/extra.min.css?ver=0.9.2' type='text/css' media='all' />
    <link rel='stylesheet' id='menu-image-css'  href='https://photty.ru/wp-content/plugins/menu-image/menu-image.css?ver=1.1' type='text/css' media='all' />
    <link rel='stylesheet' id='divi-fonts-css'  href='//fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800&#038;subset=latin,latin-ext' type='text/css' media='all' />
    <link rel='stylesheet' id='et-gf-comfortaa-css'  href='//fonts.googleapis.com/css?family=Comfortaa:400,300,700&#038;subset=latin,cyrillic-ext,greek,latin-ext,cyrillic' type='text/css' media='all' />
    <link rel='stylesheet' id='divi-style-css'  href='https://photty.ru/wp-content/themes/Divi/style.css?ver=2.7.5' type='text/css' media='all' />
    <link rel='stylesheet' id='et-shortcodes-css-css'  href='https://photty.ru/wp-content/themes/Divi/epanel/shortcodes/css/shortcodes.css?ver=2.7.5' type='text/css' media='all' />
    <link rel='stylesheet' id='et-shortcodes-responsive-css-css'  href='https://photty.ru/wp-content/themes/Divi/epanel/shortcodes/css/shortcodes_responsive.css?ver=2.7.5' type='text/css' media='all' />
    <link rel='stylesheet' id='magnific-popup-css'  href='https://photty.ru/wp-content/themes/Divi/includes/builder/styles/magnific_popup.css?ver=2.7.5' type='text/css' media='all' />
    <script type='text/javascript' src='https://photty.ru/wp-includes/js/jquery/jquery.js?ver=1.12.4'></script>
    <script type='text/javascript' src='https://photty.ru/wp-includes/js/jquery/jquery-migrate.min.js?ver=1.4.1'></script>
    <link rel='https://api.w.org/' href='https://photty.ru/wp-json/' />
    <link rel="EditURI" type="application/rsd+xml" title="RSD" href="https://photty.ru/xmlrpc.php?rsd" />
    <link rel="wlwmanifest" type="application/wlwmanifest+xml" href="https://photty.ru/wp-includes/wlwmanifest.xml" />
    <meta name="generator" content="WordPress 4.8.1" />
    <link rel='shortlink' href='https://photty.ru/?p=22020' />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />		<style id="theme-customizer-css">

        /*Чтобы пагинация не налазила на превью и позволяла кликнуть*/
        .et_pb_gallery_pagination{display:contents;}

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

</head>
<body class="post-template-default et_monarch single single-post postid-22020 single-format-standard no-customize-support chrome et_pb_button_helper_class et_fullwidth_nav et_fullwidth_secondary_nav et_fixed_nav et_show_nav et_cover_background et_pb_gutter osx et_pb_gutters3 et_primary_nav_dropdown_animation_expand et_secondary_nav_dropdown_animation_fade et_pb_footer_columns4 et_header_style_left et_pb_pagebuilder_layout et_full_width_page">
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
                    <ul id="top-menu" class="nav"><li id="menu-item-27115" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-27115"><a href="//photty.ru/portfolio" class="menu-image-title-after"><span class="menu-image-title">Портфолио</span></a></li>
                        <li id="menu-item-27114" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-home menu-item-has-children menu-item-27114"><a href="//photty.ru" class="menu-image-title-after"><span class="menu-image-title">Что мы снимаем</span></a>
                            <ul  class="sub-menu">
                                <li id="menu-item-27117" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-27117"><a href="//photty.ru/event-photo/private-events/" class="menu-image-title-after"><span class="menu-image-title">Частные мероприятия</span></a>
                                    <ul  class="sub-menu">
                                        <li id="menu-item-27344" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-27344"><a href="//photty.ru/adult-bday/" class="menu-image-title-after"><span class="menu-image-title">День Рождения</span></a></li>
                                        <li id="menu-item-27345" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-27345"><a href="//photty.ru/kids-bday/" class="menu-image-title-after"><span class="menu-image-title">Детский День Рождения</span></a></li>
                                        <li id="menu-item-27346" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-27346"><a href="//photty.ru/graduation/" class="menu-image-title-after"><span class="menu-image-title">Выпускной</span></a></li>
                                        <li id="menu-item-27453" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-27453"><a href="//photty.ru/baptizing" class="menu-image-title-after"><span class="menu-image-title">Крещение</span></a></li>
                                    </ul>
                                </li>
                                <li id="menu-item-27118" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-27118"><a href="//photty.ru/event-photo/corporate" class="menu-image-title-after"><span class="menu-image-title">Бизнес мероприятия</span></a>
                                    <ul  class="sub-menu">
                                        <li id="menu-item-27171" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-27171"><a href="//photty.ru/exhibition/" class="menu-image-title-after"><span class="menu-image-title">Выставки</span></a></li>
                                        <li id="menu-item-27205" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-27205"><a href="//photty.ru/conference/" class="menu-image-title-after"><span class="menu-image-title">Конференции</span></a></li>
                                        <li id="menu-item-27347" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-27347"><a href="//photty.ru/corporate-party/" class="menu-image-title-after"><span class="menu-image-title">Корпоративы</span></a></li>
                                    </ul>
                                </li>
                                <li id="menu-item-27069" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-27069"><a href="https://photty.ru/food-photo/" class="menu-image-title-after"><span class="menu-image-title">Блюда</span></a></li>
                                <li id="menu-item-27074" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-27074"><a href="https://photty.ru/portrait/" class="menu-image-title-after"><span class="menu-image-title">Портреты</span></a></li>
                                <li id="menu-item-28216" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-28216"><a href="https://photty.ru/weddings/" class="menu-image-title-after"><span class="menu-image-title">Свадьбы</span></a></li>
                                <li id="menu-item-27070" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-27070"><a href="https://photty.ru/family-photo/" class="menu-image-title-after"><span class="menu-image-title">Cемейное фото</span></a></li>
                                <li id="menu-item-27072" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-27072"><a href="https://photty.ru/product/" class="menu-image-title-after"><span class="menu-image-title">Каталог</span></a></li>
                                <li id="menu-item-27071" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-27071"><a href="https://photty.ru/video/" class="menu-image-title-after"><span class="menu-image-title">Видео</span></a></li>
                            </ul>
                        </li>
                        <li id="menu-item-714" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-714"><a href="tel:+74997033992" class="menu-image-title-after menu-image-not-hovered"><img width="36" height="36" src="https://photty.ru/wp-content/uploads//2018/04/phone-icon1-150x150-36x36-proper.png" class="menu-image menu-image-title-after" alt="позвонить в фотти" srcset="https://photty.ru/wp-content/uploads/2018/04/phone-icon1-150x150-36x36-proper.png 36w, https://photty.ru/wp-content/uploads/2018/04/phone-icon1-150x150-36x36-proper-24x24.png 24w" sizes="(max-width: 36px) 100vw, 36px" /><span class="menu-image-title">+7 499 703 3992</span></a></li>
                        <li id="menu-item-944" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-944"><a href="mailto:welcome@photty.ru" class="menu-image-title-after menu-image-not-hovered"><img width="36" height="36" src="https://photty.ru/wp-content/uploads//2018/04/e-mail-icon2-36x36-proper.png" class="menu-image menu-image-title-after" alt="" srcset="https://photty.ru/wp-content/uploads/2018/04/e-mail-icon2-36x36-proper.png 36w, https://photty.ru/wp-content/uploads/2018/04/e-mail-icon2-36x36-proper-24x24.png 24w" sizes="(max-width: 36px) 100vw, 36px" /><span class="menu-image-title">welcome@photty.ru</span></a></li>
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
                                                                <a href="https://photty.ru/gallery/<?=$name?>">
                                                                    <img src="https://photty.ru/gallery/<?=$name?>"/>
                                                                    <span class="et_overlay"></span>
                                                                </a>
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

                                            <!-- .et_pb_code -->
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
                                                    <a href="#" class="myface-a" style="">

                                                        <img src="https://photty.ru/wp-content/uploads//2017/10/upload-selfie-here-for-web-1.png" alt="" style="width:300px"/>
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
                                                    <a href="<?=$data['href']?>" class="ydisk-down-n" target="_blank"><img src="https://photty.ru/wp-content/uploads//2017/04/download_button_400px.jpg" width="300px" alt="" />
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
                                <div class="et_pb_section et_pb_section_2 et_pb_with_background et_section_regular">




                                    <div class=" et_pb_row et_pb_row_4">
                                        <div class="et_pb_column et_pb_column_4_4  et_pb_column_6 et_pb_css_mix_blend_mode_passthrough et-last-child">


                                            <div class="et_pb_text et_pb_module et_pb_bg_layout_light et_pb_text_align_left  et_pb_text_5">


                                                <div class="et_pb_text_inner">
                                                    <p><a name="orderamocrm"></a></p>
                                                    <h2 style="text-align: center;">ЗАКАЗАТЬ ФОТОГРАФА НА МЕРОПРИЯТИЕ?</h2>
                                                </div>
                                            </div> <!-- .et_pb_text -->
                                        </div> <!-- .et_pb_column -->


                                    </div> <!-- .et_pb_row --><div class=" et_pb_row et_pb_row_5">
                                        <div class="et_pb_column et_pb_column_1_2  et_pb_column_7 et_pb_css_mix_blend_mode_passthrough">


                                            <div class="et_pb_text et_pb_module et_pb_bg_layout_light et_pb_text_align_left  et_pb_text_6">


                                                <div class="et_pb_text_inner">
                                                    <h3></h3>
                                                    <h3>Чтобы заказать фотографа на мероприятие :</h3>
                                                    <ol>
                                                        <li>Укажите имя, телефон и e-mail;</li>
                                                        <li>Отметьте в комментариях формат мероприятия;</li>
                                                        <li>Подтвердите заказ.</li>
                                                    </ol>
                                                    <p style="text-align: left;">Обратите внимание, что мы не сможем связаться с вами, если в форме указаны ошибочные контакты. Спасибо!</p>
                                                    <div id="s3gt_translate_tooltip_mini" class="s3gt_translate_tooltip_mini_box" style="background: initial !important; border: initial !important; border-radius: initial !important; border-spacing: initial !important; border-collapse: initial !important; direction: ltr !important; flex-direction: initial !important; font-weight: initial !important; height: initial !important; letter-spacing: initial !important; min-width: initial !important; max-width: initial !important; min-height: initial !important; max-height: initial !important; margin: auto !important; outline: initial !important; padding: initial !important; position: absolute; table-layout: initial !important; text-align: initial !important; text-shadow: initial !important; width: initial !important; word-break: initial !important; word-spacing: initial !important; overflow-wrap: initial !important; box-sizing: initial !important; display: initial !important; color: inherit !important; font-size: 13px !important; font-family: X-LocaleSpecific, sans-serif, Tahoma, Helvetica !important; line-height: 13px !important; vertical-align: top !important; white-space: inherit !important; left: 833px; top: 258px; opacity: 0.65;"></div>
                                                </div>
                                            </div> <!-- .et_pb_text -->
                                        </div> <!-- .et_pb_column --><div class="et_pb_column et_pb_column_1_2  et_pb_column_8 et_pb_css_mix_blend_mode_passthrough et-last-child">


                                            <div class="et_pb_code et_pb_module  et_pb_code_1">


                                                <div class="et_pb_code_inner">
                                                    <div class="uCalc_88831"></div><script> var widgetOptions88831 = { bg_color: "transparent" }; (function() { var a = document.createElement("script"), h = "head"; a.async = true; a.src = (document.location.protocol == "https:" ? "https:" : "http:") + "//ucalc.pro/api/widget.js?id=88831&t="+Math.floor(new Date()/18e5); document.getElementsByTagName(h)[0].appendChild(a) })();</script>
                                                </div> <!-- .et_pb_code_inner -->
                                            </div> <!-- .et_pb_code -->
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
                    <ul id="menu-footer-menu" class="bottom-nav"><li id="menu-item-1876" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-1876"><a href="//photty.ru/event-photo/" class="menu-image-title-after"><span class="menu-image-title">Фотограф на мероприятие</span></a></li>
                        <li id="menu-item-27507" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-27507"><a href="//photty.ru/magnets/" class="menu-image-title-after"><span class="menu-image-title">Магниты и печать на выезде</span></a></li>
                        <li id="menu-item-1877" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-1877"><a href="//photty.ru/Family-photo/" class="menu-image-title-after"><span class="menu-image-title">Семейные фотосессии</span></a></li>
                        <li id="menu-item-1878" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-1878"><a href="//photty.ru/portrait/" class="menu-image-title-after"><span class="menu-image-title">Портретная съемка</span></a></li>
                        <li id="menu-item-1879" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-1879"><a href="//photty.ru/food-photo/" class="menu-image-title-after"><span class="menu-image-title">Съемка блюд</span></a></li>
                        <li id="menu-item-26569" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-26569"><a href="//photty.ru/product/" class="menu-image-title-after"><span class="menu-image-title">Предметная съемка</span></a></li>
                        <li id="menu-item-2538" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-2538"><a href="//photty.ru/weddings/" class="menu-image-title-after"><span class="menu-image-title">Фотограф на свадьбу</span></a></li>
                        <li id="menu-item-26511" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-26511"><a href="//photty.ru/video/" class="menu-image-title-after"><span class="menu-image-title">Видео</span></a></li>
                        <li id="menu-item-4215" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-4215"><a href="//photty.ru/business-photo/" class="menu-image-title-after"><span class="menu-image-title">Деловой портрет</span></a></li>
                        <li id="menu-item-4216" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-4216"><a href="//photty.ru/studio-faq/" class="menu-image-title-after"><span class="menu-image-title">Подготовка к студии</span></a></li>
                        <li id="menu-item-1968" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-1968"><a href="//photty.ru/pricing" class="menu-image-title-after"><span class="menu-image-title">Расчет стоимости</span></a></li>
                        <li id="menu-item-2952" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-2952"><a href="//photty.ru/projects/" class="menu-image-title-after"><span class="menu-image-title">Проекты</span></a></li>
                        <li id="menu-item-26595" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-26595"><a href="//photty.ru/articles/" class="menu-image-title-after"><span class="menu-image-title">Статьи</span></a></li>
                        <li id="menu-item-1623" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-1623"><a href="//photty.ru/faq" class="menu-image-title-after"><span class="menu-image-title">F.A.Q.</span></a></li>
                        <li id="menu-item-1564" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-1564"><a href="//photty.ru/dogovor-oferta/" class="menu-image-title-after"><span class="menu-image-title">Договор-оферта</span></a></li>
                        <li id="menu-item-26622" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-26622"><a href="//photty.ru/contacts" class="menu-image-title-after"><span class="menu-image-title">Контакты</span></a></li>
                        <li id="menu-item-26956" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-26956"><a href="//photty.ru/about/" class="menu-image-title-after"><span class="menu-image-title">О Компании</span></a></li>
                        <li id="menu-item-26734" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-26734"><a href="//photty.ru/agreement/" class="menu-image-title-after"><span class="menu-image-title">Согласие на обработку персональных данных</span></a></li>
                        <li id="menu-item-26922" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-26922"><a href="https://www.instagram.com/photty.ru/" class="menu-image-title-after menu-image-not-hovered"><img width="25" height="25" src="https://photty.ru/wp-content/uploads//2018/04/instagram-logo-for-web.jpg" class="menu-image menu-image-title-after" alt="фотти инстаграм" srcset="https://photty.ru/wp-content/uploads/2018/04/instagram-logo-for-web.jpg 25w, https://photty.ru/wp-content/uploads/2018/04/instagram-logo-for-web-24x24.jpg 24w" sizes="(max-width: 25px) 100vw, 25px" /><span class="menu-image-title">Insta</span></a></li>
                        <li id="menu-item-26923" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-26923"><a href="https://facebook.com/photty.ru" class="menu-image-title-after menu-image-not-hovered"><img width="25" height="25" src="https://photty.ru/wp-content/uploads//2018/04/facebook-logo-for-web-proper-new.jpg" class="menu-image menu-image-title-after" alt="facebook photty" srcset="https://photty.ru/wp-content/uploads/2018/04/facebook-logo-for-web-proper-new.jpg 25w, https://photty.ru/wp-content/uploads/2018/04/facebook-logo-for-web-proper-new-24x24.jpg 24w" sizes="(max-width: 25px) 100vw, 25px" /><span class="menu-image-title">fb</span></a></li>
                        <li id="menu-item-26934" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-26934"><a href="https://vk.com/phottyru" class="menu-image-title-after menu-image-not-hovered"><img width="25" height="25" src="https://photty.ru/wp-content/uploads//2018/04/vk-logo-for-web.jpg" class="menu-image menu-image-title-after" alt="вконтакте фотти" srcset="https://photty.ru/wp-content/uploads/2018/04/vk-logo-for-web.jpg 25w, https://photty.ru/wp-content/uploads/2018/04/vk-logo-for-web-24x24.jpg 24w" sizes="(max-width: 25px) 100vw, 25px" /><span class="menu-image-title">vk</span></a></li>
                        <li id="menu-item-1880" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-1880"><a href="//photty.SG" class="menu-image-title-after menu-image-not-hovered"><img width="36" height="24" src="https://photty.ru/wp-content/uploads//2018/04/sg-flag-36x24-proper-36x24.png" class="menu-image menu-image-title-after" alt="photty singapore" srcset="https://photty.ru/wp-content/uploads/2018/04/sg-flag-36x24-proper.png 36w, https://photty.ru/wp-content/uploads/2018/04/sg-flag-36x24-proper-24x16.png 24w" sizes="(max-width: 36px) 100vw, 36px" /><span class="menu-image-title">PHOTTY.SG</span></a></li>
                    </ul>					</div>
            </div> <!-- #et-footer-nav -->



        </footer> <!-- #main-footer -->
    </div> <!-- #et-main-area -->


</div> <!-- #page-container -->

<style type="text/css" id="et-builder-advanced-style">

    .et_pb_section_4.et_pb_section { background-color:#ffffff !important; }
    .et_pb_image_0 { max-width: 200px; text-align: center; }
</style><div id="fb-root"></div>

<script type="text/javascript">
    jQuery(document).ready(function(){
        jQuery("#footer-info").text(' ');
        jQuery("<p>Copyright © Photty.ru. All rights reserved.</p>").insertAfter("#footer-info");
    });
</script>

<script type='text/javascript' src='https://photty.ru/wp-content/themes/Divi/includes/builder/scripts/frontend-builder-global-functions.js?ver=2.7.5'></script>
<script type='text/javascript' src='https://photty.ru/wp-includes/js/comment-reply.min.js?ver=4.8.1'></script>
<script type='text/javascript' src='https://photty.ru/wp-content/themes/Divi/includes/builder/scripts/jquery.mobile.custom.min.js?ver=2.7.5'></script>
<script type='text/javascript' src='https://photty.ru/wp-content/themes/Divi/js/custom.js?ver=2.7.5'></script>
<script type='text/javascript' src='https://photty.ru/wp-content/themes/Divi/includes/builder/scripts/jquery.fitvids.js?ver=2.7.5'></script>
<script type='text/javascript' src='https://photty.ru/wp-content/themes/Divi/includes/builder/scripts/waypoints.min.js?ver=2.7.5'></script>
<script type='text/javascript' src='https://photty.ru/wp-content/themes/Divi/includes/builder/scripts/jquery.magnific-popup.js?ver=2.7.5'></script>
<script type='text/javascript'>
    /* <![CDATA[ */
    var et_pb_custom = {"ajaxurl":"https:\/\/photty.ru\/wp-admin\/admin-ajax.php","images_uri":"https:\/\/photty.ru\/wp-content\/themes\/Divi\/images","builder_images_uri":"https:\/\/photty.ru\/wp-content\/themes\/Divi\/includes\/builder\/images","et_frontend_nonce":"23002219f1","subscription_failed":"Please, check the fields below to make sure you entered the correct information.","et_ab_log_nonce":"1568244e69","fill_message":"Please, fill in the following fields:","contact_error_message":"Please, fix the following errors:","invalid":"Invalid email","captcha":"Captcha","prev":"Prev","previous":"Previous","next":"Next","wrong_captcha":"You entered the wrong number in captcha.","is_builder_plugin_used":"","is_divi_theme_used":"1","widget_search_selector":".widget_search","is_ab_testing_active":"","page_id":"22020","unique_test_id":"","ab_bounce_rate":"5","is_cache_plugin_active":"no","is_shortcode_tracking":""};
    /* ]]> */
</script>
<script type="text/javascript" src="https://photty.ru/wp-content/themes/Divi/includes/builder/scripts/frontend-builder-scripts.js?ver=3.0.106"></script>
<script type="text/javascript" src="https://photty.ru/wp-content/plugins/divi-builder/core/admin/js/common.js?ver=3.2.1"></script>
<script type="text/javascript" src="https://photty.ru/wp-includes/js/wp-embed.min.js?ver=4.8.6"></script>
<script type="text/javascript" src="https://photty.ru/wp-content/themes/Divi/includes/builder/scripts/jquery.hashchange.js?ver=3.0.106"></script>

<div id="fb-root"></div>
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


<!--Вручную подключили последнюю версию jQuery для корректной работы скриптов-->
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>


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
            opened = jQuery(this).parents('.et_pb_gallery_item').attr('download');
        });

        jQuery('body').on('click','a.download',function(e){
            e.preventDefault();
            opened = jQuery('a[href="'+jQuery('.mfp-img').attr('src')+'"]').parents('*[download]').attr('download');
            document.location.href=opened;
            console.log(opened);
        });

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
            jQuery('.et_pb_gallery_item.hiddenUploaded .et_pb_gallery_image span').click();
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
        });

        setInterval(function() {
            jQuery.post('/gallery/ydisk_save.php', {data: jQuery('.data-ydisk').html()}, function (data) {
                jQuery('.ydisk-down-a').attr('href', data);
            });
            jQuery.post('/gallery/ydisk_save.php', {data: "<?=$data_link?>"}, function (data) {
                jQuery('.ydisk-down-n').attr('href', data);
            });
        },30*1000);

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