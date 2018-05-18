<?
$html = file_get_contents('https://docs.google.com/forms/d/e/1FAIpQLScOmx6mZRKmo_mDXHuMY3Ss3djD9OBFD2SgCUwTQwt-cIhONA/viewform?usp=pp_url&entry.1137581388='.$_GET['name']);
//$html = str_replace('data-item-id="319828331', 'data-item-id="319828331" style="display: none"', $html);
echo $html;
?>