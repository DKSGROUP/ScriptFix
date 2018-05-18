<?
header('Access-Control-Allow-Origin: *');

file_put_contents('del.log', json_encode($_REQUEST));

$file = json_decode(file_get_contents('data.json'),true);
unset($file[$_GET['id']]);
$file = json_encode($file);
file_put_contents('data.json', $file);

//cache of faces
echo 'data/'.$_GET['link'].'.json';
file_put_contents('data/'.$_GET['link'].'.json','{}');
?>