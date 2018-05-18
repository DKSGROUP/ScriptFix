<?
$accounts = array(
    array('nova.agency.realty@yandex.kz','d3248fa181cf'),
    array('nova.agency.realty@yandex.com','qazwsxcde123'),
    array('nova.agency.realty@yandex.ua','d3248fa181cf'),
    array('nova.agency.realty@ya.ru','d3248fa181cf'),
    array('nova.agency.auto@yandex.ua','d3248fa181cf'),
    array('nova.agency.auto@yandex.kz','d3248fa181cf'),
    array('nova.agency.auto@yandex.by','d3248fa181cf'),
    array('nova.agency.auto@yandex.com','d3248fa181cf'),
    array('nova.agency.auto@ya.ru','d3248fa181cf'),
    array('crm.ezhov2018@yandex.ru','d3248fa181cf'),
    array('tchernyh.auto-crm@yandex.ru','d3248fa181cf'),
    array('panchuk.smart-crm2018@yandex.ru','d3248fa181cf'),
    array('udakov.crm2018@yandex.ru','d3248fa181cf'),
    array('pascha.belii-crm2018@yandex.ru','d3248fa181cf')
);
$current = (int)file_get_contents('/home/i/intime/public_html/auto/current.txt');
echo $current.'now';

function auth()
{
    global $accounts;
    global $current;
    echo $accounts[$current][0].'auth';
    //echo $accounts[$current][0];
	$cookies = send_post_get_cookie("https://www.amocrm.ru/account/api_auth.php?type=json","TOP_AUTH_FORM=Y&USER_LOGIN=".$accounts[$current][0]."&USER_PASSWORD=".$accounts[$current][1]."&USER_REMEMBER=N&captcha_word&captcha_sid");
	return $cookies;
}

function checkCust()
{
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL,"https://www.amocrm.ru/partners/private/works/list/");
	curl_setopt($ch, CURLOPT_COOKIEJAR, "c.txt");
	curl_setopt($ch, CURLOPT_COOKIEFILE, "c.txt");

	// in real life you should use something like:
	// curl_setopt($ch, CURLOPT_POSTFIELDS, 
	//          http_build_query(array('postvar1' => 'value1')));

	// receive server response ...
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$server_output = curl_exec($ch);
    //echo $server_output;
    //echo $server_output=='';
    curl_close ($ch);

    echo 'check';

	if(strpos($server_output, '/partners/private/works/detail/')===false)
		return 0;
	else
		return substr($server_output, strpos($server_output, '/partners/private/works/detail/'), 39);

}

function send_post_get_cookie($URL='', $PostData=Array(), $cookie='')
{
    // Отсекаем пустые вызовы:
    if (strlen($URL)<=0) return false;
    // Скопировал строку из FireBug:
    $ua = 'User-Agent: Mozilla/5.0 (Windows; U; Windows NT 6.1; ru; rv:1.9.2.13) Gecko/20101203 MRA 5.7 (build 03796) Firefox/3.6.13';
    // Инициализация объекта:
    $ch = curl_init($URL);
    // показывать заголовки (в них куки):
    curl_setopt($ch, CURLOPT_HEADER, 1); 
    // не показывать тело страницы (для экономии траффика):
    curl_setopt($ch, CURLOPT_NOBODY, 1); 
    // это чтобы прикинуться браузером:
    curl_setopt($ch, CURLOPT_USERAGENT, $ua);
    // можно ставить еще вот это, если удаленный сервер проверяет:
    // curl_setopt($ch, CURLOPT_REFERER, $URL);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_COOKIEJAR, "c.txt");
	curl_setopt($ch, CURLOPT_COOKIEFILE, "c.txt");
    // включение полей POST в запрос:
    curl_setopt($ch, CURLOPT_POSTFIELDS, $PostData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // если нужны печеньки, установим:
    if (strlen($cookie)>0)
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
    // запускаем запрос:
        curl_exec ($ch);
        curl_close ($ch);
}

function getCapchaImg($url)
{
    global $accounts;
    global $current;
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL,"https://www.amocrm.ru".$url);
	curl_setopt($ch, CURLOPT_COOKIEJAR, "c.txt");
	curl_setopt($ch, CURLOPT_COOKIEFILE, "c.txt");

	// in real life you should use something like:
	// curl_setopt($ch, CURLOPT_POSTFIELDS, 
	//          http_build_query(array('postvar1' => 'value1')));

	// receive server response ...
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$server_output = curl_exec ($ch);
    logData($server_output, "getcapcha");
    if(strpos($server_output, 'Вы не можете принять')!==false)
    {
        //mess('Больше 2х заявок! Меняем аккаунт.');
        $current ++;
        if($current>=count($accounts))
            $current = 0;
        file_put_contents('/home/i/intime/public_html/auto/current.txt', (string)$current);
        echo $current.'change';
        if($current==0)
            die();
        auth();
        main();
        return false;
    }
    //echo $server_output;
	$a1 = strpos($server_output, "/bitrix/tools/captcha.php?captcha_sid=") + 38;
	$a2 = strpos($server_output, '" style="width:180px') - $a1;

	$sid = substr($server_output, $a1, $a2);
	curl_close ($ch);

	$data = file_get_contents('https://amocrm.ru/bitrix/tools/captcha.php?captcha_sid='.$sid);
	$text = antiCapcha($data);
	submitForm($url, $sid, $text);
	//echo $server_output;
}

function antiCapcha($data)
{
	$PostData = array("clientKey"=>'e6f2fae9c2fed0989ea9c09e42362df3', "task"=>array("type"=>'ImageToTextTask',"body"=>base64_encode($data)));
    // Инициализация объекта:
    $ch = curl_init("https://api.anti-captcha.com/createTask");
    curl_setopt($ch, CURLOPT_POST, 1);
    // включение полей POST в запрос:
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($PostData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // запускаем запрос:
    $data = curl_exec($ch);
    $data = json_decode($data);
    curl_close ($ch);
    $a = true;
    $text;
    while($a)
    {
    	$r = checkAnticapcha($data->taskId);
    	if($r!==false)
    	{
    		$text = $r->solution->text;
    		$a = false;
    	}
    	sleep(2);
    }
    return $text;
}

function checkAnticapcha($taskId)
{
	$PostData = array("clientKey"=>'e6f2fae9c2fed0989ea9c09e42362df3', "taskId"=>$taskId);
    // Инициализация объекта:
    $ch = curl_init("https://api.anti-captcha.com/getTaskResult");
    curl_setopt($ch, CURLOPT_POST, 1);
    // включение полей POST в запрос:
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($PostData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // запускаем запрос:
    $data = curl_exec($ch);
    $data = json_decode($data);
    if($data->status=="processing")
    	return false;
    else
    	return $data;
    curl_close ($ch);
}

function submitForm($url, $captcha_sid, $captcha_word)
{
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL,"https://www.amocrm.ru".$url."?captcha_sid=".$captcha_sid."&captcha_word=".$captcha_word);
	curl_setopt($ch, CURLOPT_COOKIEJAR, "c.txt");
	curl_setopt($ch, CURLOPT_COOKIEFILE, "c.txt");

	// in real life you should use something like:
	// curl_setopt($ch, CURLOPT_POSTFIELDS, 
	//          http_build_query(array('postvar1' => 'value1')));

	// receive server response ...
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$server_output = curl_exec ($ch);
	//echo $server_output;

	curl_close ($ch);
	//mess("Заявка захвачена");
    getData($url);
}

function getData($url)
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL,"https://www.amocrm.ru".$url);
    curl_setopt($ch, CURLOPT_COOKIEJAR, "c.txt");
    curl_setopt($ch, CURLOPT_COOKIEFILE, "c.txt");

    // in real life you should use something like:
    // curl_setopt($ch, CURLOPT_POSTFIELDS, 
    //          http_build_query(array('postvar1' => 'value1')));

    // receive server response ...
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $server_output = curl_exec ($ch);
    if(strpos($server_output, 'взята в работу'))
    {
        mess('Опоздали :(');
        return false;
    }
    logData($server_output, "getdata");


    $phone = cutStr($server_output,'name="contact_phone" type="text"',' disabled>');
    $phone = cutStr($phone,'value="','"');
    $city = cutStr($server_output,"for='brief_cities'>Города</label>",'/p>');
    $city = cutStr($city,"<p class='brief'>",'<');
    $comments = cutStr($server_output,'id="brief_comment"','/textarea>');
    $comments = cutStr($comments,'disabled>','<');
    $fio = cutStr($server_output,'name="contact_name" type="text"',' disabled>');
    $fio = cutStr($fio,'value="','"');
    $company = cutStr($server_output,'name="contact_company" type="text"',' disabled>');
    $company = cutStr($company,'value="','"');
    $email = cutStr($server_output,'name="contact_email" type="text"',' disabled>');
    $email = cutStr($email,'value="','"');
    amocrm($company, $fio, $phone, $email, $comments);
    //file_put_contents('call.log',file_get_contents("https://whitesaas.com/api?action=call&callback=jWS21406038657306439856_1501884316345&phone=79629772435&department=&customtext=&phoneMask=%2B_(___)___-__-__&shownOn=onbtn&url=http%3A%2F%2Fnova-agency.ru%2F&device=pc&code=9fec7bc55324529fb1f5345b3d22381d&visitorId=372700119&visitId=652420646&googleClientId=1725972598.1500154733&killerId=86775&invaderStatId=false&instinctStatId=false&generatorLeadId=false&roistatPromo=false&advertiseId=false&calltrackingId=false&lpgeneratorId=false&leadvertexId=false&_=1501884316349"));
    mess('Данные: '.$fio.', '.$phone.', '.$email.', '.$city.', '.$comments);
}

function amocrm($company, $fio, $tel, $email, $comments)
{
    $company = urlencode($company);
    $fio = urlencode($fio);
    $tel = urlencode($tel);
    $email = urlencode($email);
    $url = 'https://photty.ru/api/photty_gallery/amo.php?company='.$company.'&fio='.$fio.'&tel='.$tel.'&email='.$email.'&comments='.urlencode($comments);
    echo $url;
    file_get_contents($url);
}

function cutStr($str,$a1,$a2)
{
    $str = substr($str, strpos($str, $a1)+strlen($a1));
    $str = substr($str, 0, strpos($str, $a2));
    return $str;
}

function logData($data, $url)
{
    file_put_contents('/home/i/intime/public_html/auto/logs/'.date('d-m-Y-H-i-s').$url.'.txt', $data);
}

function mess($mess)
{
	$ch = curl_init("https://dmitrybondar.ru/projects/telegram/bot1/bot_form.php");
    curl_setopt($ch, CURLOPT_POST, 1);
    // включение полей POST в запрос:
    curl_setopt($ch, CURLOPT_POSTFIELDS, "message=".$mess."&id=-249069342");
    //curl_setopt($ch, CURLOPT_POSTFIELDS, "message=".$mess."&id=-181332206");
    // ."&id=-249069342"
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // запускаем запрос:
    $data = curl_exec($ch);
    curl_close ($ch);
}
?>