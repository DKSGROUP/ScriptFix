<?
header('Access-Control-Allow-Origin: *');

echo file_get_contents('https://photty.ru/gallery/new.php?title='.urlencode($_POST['title']).'&name='.urlencode($_POST['name']).'&link='.urlencode($_POST['link']).'&link2='.urlencode($_POST['link2']).'&id='.urlencode($_POST['id']).'&title_lead='.urlencode($_POST['title_lead']).'&custom='.urlencode($_POST['custom']));
?>