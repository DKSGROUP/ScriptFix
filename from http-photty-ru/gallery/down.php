<?
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
$down = file_get_contents('https://cloud-api.yandex.net/v1/disk/public/resources/download?public_key='.urlencode($_GET['link']).'&path='.urlencode($_GET['path']));
//print_r($down);
$down = json_decode($down,true);
header('Location: '.$down['href']);
?>