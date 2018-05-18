<?
ini_set('display_errors','On');
error_reporting('E_ALL');
include 'controllers.php';
auth();
//getData('/partners/private/works/detail/10215520');
//amocrm('test','Иванов Иван','+78887776666','test@test.ru');
//die;
function main()
{
	$cust = checkCust();
	if($cust!==0)
	{
		//mess("Новая заявка");
		echo 'new';
		getCapchaImg($cust);
	}
}

main();

?>