<?
function post($url,$data)
{
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS,$data);

	// receive server response ...
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$server_output = curl_exec ($ch);

	curl_close ($ch);

	return $server_output;
}

echo 'Спасибо, доступ получен';

file_put_contents('data/ydisk.json',post('https://oauth.yandex.ru/token','client_id=add3b6c26a024b8d9a4b690effeed403&client_secret=db1ce9cc73ba43e38c77c43aa6cae524&grant_type=authorization_code&code='.$_GET['code']));
?>