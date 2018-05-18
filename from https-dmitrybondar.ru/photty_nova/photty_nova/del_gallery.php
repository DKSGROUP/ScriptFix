<?
header('Access-Control-Allow-Origin: *');

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
		'Content-Type: application/json','Content-Length: '.strlen($data_string), 'Authorization:Token pugxyJsPCg2JMbbkkYTDmY7YCfTNYAuU'
	));

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
		'Authorization:Token pugxyJsPCg2JMbbkkYTDmY7YCfTNYAuU'
	));

	$server_output = curl_exec ($ch);

	curl_close ($ch);

	return $server_output;
}

function deleteFF($url)
{
	//echo $url.'\n';
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL,$url);

	// receive server response ...
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Authorization:Token pugxyJsPCg2JMbbkkYTDmY7YCfTNYAuU'
	));

	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");

	$server_output = curl_exec ($ch);
	echo $server_output;

	curl_close ($ch);

	return $server_output;
}

if((!isset($_POST['link']))||(!isset($_POST['id'])))
	die;
//delete from FF
function delFF()
{
	echo 'del';
	$list = json_decode(get('https://api.findface.pro/v1/faces/gallery/'.$_POST['link'].'/'),true);
	foreach ($list['results'] as $key => $value) {
		echo $value['id'].',';
		deleteFF('https://api.findface.pro/v1/faces/id/'.$value['id'].'/');
	}
	if(count($list['results'])>0)
	{
		delFF();
	}else{
		file_get_contents('https://photty.ru/gallery/amo5.php?lead_id='.$_POST['id_lead'].'&comment='.urlencode('Удаление галереи завершено'));
	}
}
delFF();
//delete from site
echo file_get_contents('https://photty.ru/gallery/del.php?id='.urlencode($_POST['id']).'&link='.$_POST['link']);
?>