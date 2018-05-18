<?
header('Access-Control-Allow-Origin: *');

$name = $_REQUEST['name'];
$link = $_REQUEST['link'];
$link2 = $_REQUEST['link2'];
$id = $_REQUEST['id'];
$title = $_REQUEST['title'];
$title_lead = $_REQUEST['title_lead'];
$custom = $_REQUEST['custom'];

function translit($s) {
  $s = (string) $s; // преобразуем в строковое значение
  $s = strip_tags($s); // убираем HTML-теги
  $s = str_replace(array("\n", "\r"), " ", $s); // убираем перевод каретки
  $s = preg_replace("/\s+/", ' ', $s); // удаляем повторяющие пробелы
  $s = trim($s); // убираем пробелы в начале и конце строки
  $s = strtolower($s); // переводим строку в нижний регистр (иногда надо задать локаль)
  $s = strtr($s, array('а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'j','з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'c','ч'=>'ch','ш'=>'sh','щ'=>'shch','ы'=>'y','э'=>'e','ю'=>'yu','я'=>'ya','ъ'=>'','ь'=>''));
  $s = str_replace(" ", "_", $s);
  $s = str_replace(".", "", $s);
  $s = preg_replace("/[^0-9a-z-_ ]/i", "", $s);
  $s = urlencode($s);
  return $s; // возвращаем результат
}

$data = json_decode(file_get_contents('data.json'),true);
$data[translit($title)] = array('title'=>$title,'name'=>$name,'link'=>$link,'link2'=>$link2,'id'=>$id,'title_lead'=>$title_lead,'custom'=>$custom);
file_put_contents('data.json', json_encode($data));

echo 'https://photty.ru/gallery/'.translit($title).'/';

$l1 = 'https://cloud-api.yandex.net/v1/disk/public/resources?limit=10000&preview_size=250x160&preview_crop=true&public_key='.urlencode($link);
$l2 = 'https://cloud-api.yandex.net/v1/disk/public/resources?limit=10000&preview_size=XXXL&public_key='.urlencode($link);
$l3 = 'https://cloud-api.yandex.net/v1/disk/public/resources?limit=10000&preview_size=250x160&preview_crop=true&public_key='.urlencode($link2);
$l4 = 'https://cloud-api.yandex.net/v1/disk/public/resources?limit=10000&preview_size=XXXL&public_key='.urlencode($link2);

function del_cache($title)
{
  $file = file_get_contents('cache.json');
  $file = json_decode($file,true);
  unset($file[$title]);
  $file = json_encode($file);
  file_put_contents('cache.json', $file);
}

del_cache($l1);
del_cache($l2);
del_cache($l3);
del_cache($l4);

?>