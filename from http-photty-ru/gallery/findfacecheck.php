<?
set_time_limit(60*60);
header('Access-Control-Allow-Origin: *');

 // включаем отображение всех ошибок, кроме E_NOTICE
error_reporting(E_ALL);
ini_set('display_errors', 1);
 
// наш обработчик ошибок
function myHandler($level, $message, $file, $line, $context) {
    // в зависимости от типа ошибки формируем заголовок сообщения
    switch ($level) {
        case E_WARNING:
            $type = 'Warning';
            break;
        case E_NOTICE:
            $type = 'Notice';
            break;
        default;
            // это не E_WARNING и не E_NOTICE
            // значит мы прекращаем обработку ошибки
            // далее обработка ложится на сам PHP
            return false;
    }
    // выводим текст ошибки
    logIt("<h2>$type: $message</h2>");
    logIt("<p><strong>File</strong>: $file:$line</p>");
    logIt("<p><strong>Context</strong>: $". join(', $', array_keys($context))."</p>");
    // сообщаем, что мы обработали ошибку, и дальнейшая обработка не требуется
    return true;
}
 
// регистрируем наш обработчик, он будет срабатывать для всех типов ошибок
set_error_handler('myHandler', E_ALL);

function logIt($txt)
{
	return false;
	file_put_contents('data/log.log', file_get_contents('data/log.log').chr(10).chr(10).date('d.m.y H:i').chr(10).$txt);
}

function q($txt)
{
	return false;
	file_put_contents('data/q.log', file_get_contents('data/q.log').chr(10).chr(10).date('d.m.y H:i').chr(10).$txt);
}

logIt('start');  

$ff=false;
$link = '';
$status = 0;
$sum = 0;
$free = false;
foreach ($_REQUEST['leads']['update'][0]['custom_fields'] as $key => $value) {
	if($value['id']=='1965375')
	{
		if($value['values'][0]['value']=='Оплачено')
		{
			$status = 1;
			$ff=true;
			$free = true;
			$sum = 0;
		}
		if($value['values'][0]['value']=='Оплата посетителя')
		{
			$status = 1;
			$ff=true;
		}
	}
	if($value['id']=='1964109')
	{
		$link = $value['values'][0]['value'];
	}
	if($value['id']=='1965535')
	{
		if(!$free)
			$sum = $value['values'][0]['value'];
	}
	if($value['id']=='1963951')
	{
		if($link == '')
			$link = $value['values'][0]['value'];
	}
}

$link = str_replace('https://yadi.sk/d/', '', $link);
$link = str_replace('/', '', $link);

$file = file_get_contents('data/'.$link.'.json');
if($file=='')
{
	$file = array();
	file_put_contents('data/'.$link.'.json', json_encode($file));
}else{
	$file = json_decode($file,true);
}

$file['status'] = $status;
$file['sum'] = $sum;

if(!$ff)
{
	file_put_contents('data/'.$link.'.json', json_encode($file));
	die;
}

//create gallery
$glist = json_decode(get('https://api.findface.pro/v1/galleries/'),true);
logIt(json_encode($glist));
$isset = false;
foreach ($glist['results'] as $key => $value) {
	if($value==$link)
	{
		$isset = true;
		break;
	}
}
if(!$isset)
{
	logIt(post('https://api.findface.pro/v1/galleries/',array('name'=>$link)));
}

$gallery = $link;


$list = file_get_contents('https://cloud-api.yandex.net/v1/disk/public/resources?limit=10000&preview_size=1280x1280&public_key='.urlencode('https://yadi.sk/d/'.$link));
//file_put_contents('data/log.log', $list);
$list = json_decode($list,true);

//compare with exist
$newPhotos = array();

foreach ($list['_embedded']['items'] as $key => $value) {
	if(!findArray($file,$value['name']))
		$newPhotos[$value['name']]=$value['preview'];
}

function findArray($ar,$what)
{
	foreach ($ar as $key => $value) {
		if($key==$what)
			return $value;
	}
	return false;
}

foreach ($newPhotos as $key => $value) {
	//identify
	$data = array();
	$data['photo'] = $value;
	$data['mf_selector'] = 'all';
	$an = json_decode(post('https://api.findface.pro/v1/faces/gallery/'.$gallery.'/identify/',$data),true);
	logIt(json_encode($an));
	$file[$key] = array('results'=>array());
	if(!isset($an['results']))
	{
		continue;
	}
	logIt('next');
	logIt('new photo');
	foreach ($an['results'] as $key2 => $value2) {
		logIt(json_encode($value2));
		if(count($value2)>0)
			if($value2[0]['confidence']<0.7)
				$value2 = array();
		if(count($value2)==0)
		{
			logIt('new');
			$data = array();
			$data['photo'] = $value;
			$data['mf_selector'] = 'all';
			$data['bbox'] = array(json_decode($key2,true));
			$data['galleries'] = array($gallery);
			$an = json_decode(post('https://api.findface.pro/v1/face/',$data),true);
			$an['results'][0]['size'] = getimagesize($value);
			$an['results'][0]['normalized']='';
			$an['results'][0]['photo']='';
			$an['results'][0]['photo_hash']='';
			$an['results'][0]['thumbnail']='';
			logIt(json_encode($an['results'][0]));
			array_push($file[$key]['results'], $an['results'][0]);
		}else{
			logIt('isset');
			$an = $value2[0]['face'];
			$coord = json_decode($key2,true);
			$an['x1'] = $coord[0];
			$an['y1'] = $coord[1];
			$an['x2'] = $coord[2];
			$an['y2'] = $coord[3];
			$an['normalized']='';
			$an['photo']='';
			$an['photo_hash']='';
			$an['thumbnail']='';
			$an['size'] = getimagesize($value);
			logIt(json_encode($an));
			array_push($file[$key]['results'], $an);
		}
	}
	file_put_contents('data/'.$link.'.json', json_encode($file));
}

if(count($newPhotos)>0)
	file_get_contents('https://photty.ru/gallery/amo5.php?lead_id='.$_REQUEST['leads']['update'][0]['id'].'&comment='.urlencode('Создание галереи завершено'));

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
	//XJRbkJfxLbtwyAhQ7BQZ-MKRwI6ChuT8

	logIt('запрос');
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

file_put_contents('data/'.$link.'.json', json_encode($file));
?>