<?
$data = json_decode($_POST['data'],true);
$faceImages = $data['faceImages'];
$name = $data['id'];
$linkPos = $data['linkPos'];
$face = $data['face'];

$f = file_get_contents('data/nova'.$name.$face.'.txt');
if($f)
{
	die($f);
}

//echo 'start';

if(count($faceImages)>0)
{
	function getYandex($url,$token)
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL,$url);

		// receive server response ...
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Authorization: OAuth '.$token
		));

		$server_output = curl_exec ($ch);

		curl_close ($ch);

		return $server_output;
	}

	function postYandex($url,$data,$token)
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($data));

		// receive server response ...
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json','Content-Length: '.strlen($data_string),'Authorization: OAuth '.$token
		));

		$server_output = curl_exec ($ch);

		if(curl_getinfo($ch,CURLINFO_HTTP_CODE)=='202')
		{
			//check

		}

		curl_close ($ch);

		return $server_output;
	}

	function putYandex($url,$token)
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL,$url);

		// receive server response ...
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_PUT, true);

		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Authorization: OAuth '.$token
		));

		$server_output = curl_exec ($ch);

		curl_close ($ch);

		return $server_output;
	}

	//if isset!!!

	$ydisk = json_decode(file_get_contents('data/ydisk.json'),true);
	//save to download
	foreach ($faceImages as $key => $value) {
		postYandex('https://cloud-api.yandex.net/v1/disk/public/resources/save-to-disk/?public_key='.urlencode('https://yadi.sk/d/'.$linkPos.'/').'&path='.urlencode($value['path']).'&name='.'photty_nova_'.$name.$face.$key.'.jpg',array(),$ydisk['access_token']);
	}
	//create folder
	putYandex('https://cloud-api.yandex.net/v1/disk/resources/?path=phottyru',$ydisk['access_token']);
	putYandex('https://cloud-api.yandex.net/v1/disk/resources/?path='.urlencode('phottyru/findface'),$ydisk['access_token']);
	putYandex('https://cloud-api.yandex.net/v1/disk/resources/?path='.urlencode('phottyru/findface/'.$name),$ydisk['access_token']);
	putYandex('https://cloud-api.yandex.net/v1/disk/resources/?path='.urlencode('phottyru/findface/'.$name.'/face'.$face),$ydisk['access_token']);
	//move to the folder
	foreach ($faceImages as $key => $value) {
		postYandex('https://cloud-api.yandex.net/v1/disk/resources/move?from=/Downloads/photty_nova_'.$name.$face.$key.'.jpg'.'&path=/phottyru/findface/'.$name.'/face'.$face.'/'.$value['name'],array(),$ydisk['access_token']);
	}
	$d = putYandex('https://cloud-api.yandex.net/v1/disk/resources/publish?path='.urlencode('phottyru/findface/'.$name.'/face'.$face),$ydisk['access_token']);
	$d = json_decode($d,true);
	$d = getYandex($d['href'],$ydisk['access_token']);
	$d = json_decode($d,true);
	$down = file_get_contents('https://cloud-api.yandex.net/v1/disk/public/resources/download?public_key='.urlencode($d["public_url"]));
	$down = json_decode($down,true);
	//$data['href'] = $down['href'];
	file_put_contents('data/nova'.$name.$face.'.txt',$down['href']);
	echo $down['href'];
}
?>