<?
$f = file_get_contents('https://photty.ru/gallery/amo5.php?fi=true&or='.$_GET['cust']);
$f = json_decode($f,true);
$f = $f['custom_fields'];
foreach ($f as $key => $value) {
	if($value['id']==1944701)
	{
		header('Location: '.$value['values'][0]['value']);
	}
}
?>