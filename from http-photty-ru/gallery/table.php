<?
echo 'https://script.google.com/macros/s/AKfycbywXZOo3WbGyCg0HylXvn27vH4H9dYwV1-gZpYGEFadddzNY60/exec?keys='.$_GET['keys'];
echo file_get_contents('https://script.google.com/macros/s/AKfycbywXZOo3WbGyCg0HylXvn27vH4H9dYwV1-gZpYGEFadddzNY60/exec?keys='.urlencode($_GET['keys']));
//echo file_get_contents('https://script.google.com/macros/s/AKfycbywXZOo3WbGyCg0HylXvn27vH4H9dYwV1-gZpYGEFadddzNY60/exec?keys=[1,2,3]');
?>