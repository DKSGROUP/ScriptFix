<?
if(empty($_POST['photo']))
	die;

$file = json_decode(file_get_contents('data/main_photo.json'),true);
$file[$_POST['id']] = $_POST['photo'];
$file = json_encode($file);
file_put_contents('data/main_photo.json', $file);
?>