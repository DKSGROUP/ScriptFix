<?
#Массив с параметрами, которые нужно передать методом POST к API системы
$user=array(
  'USER_LOGIN'=>'anton@photty.com', #Ваш логин (электронная почта)
  'USER_HASH'=>'38e7e223ec1b2de45019f9407fcce5a6' #Хэш для доступа к API (смотрите в профиле пользователя)
);

$comment = $_REQUEST['comment'];
$lead_id = $_REQUEST['lead_id'];

$an = meth('auth.php?type=json',$user);
if($an['auth']==1)
{
	if(isset($_REQUEST['ph']))
	{
		$name = $_REQUEST['name'];
		$tel = $_REQUEST['tel'];
		$email = $_REQUEST['email'];
		$comment = $_REQUEST['comment'];
		$tags = $_REQUEST['tags'];
		$url = $_REQUEST['url'];
		$an = addDeal($name, 0, 13010613, 1699348,$tags,$url);
		addContact($name, $an['leads']['add'][0]['id'], $tel, $email);
		addNote($an['leads']['add'][0]['id'], $comment, 1699348);
	} else if(isset($_REQUEST['fi'])){
		$an = findDeal($_REQUEST['or']);
		echo json_encode($an['leads'][0]);
	} else if(isset($_REQUEST['up'])){
		$an = findDeal($_REQUEST['or']);
		$an = $an['leads'][0]['id'];
		updateDeal($an, $_REQUEST['tags'], $_REQUEST['sum']);
	}else{
		echo 'Авторизация прошла успешно';
		echo $lead_id;
		addNote($lead_id, $comment, 1699348);
	}
}

function updateDeal($id, $tags, $price)
{
	$leads = array('request'=>array('leads'=>array('update'=>array())));
	$leads['request']['leads']['update']=array(
	  array(
	  	'id'=>$id,
	  	'last_modified'=>time(),
	  	'status_id'=>142,
	  	'price'=>$price
	  )
	);
	return meth('v2/json/leads/set',$leads);
}

function findDeal($query)
{
	return methGet('v2/json/leads/list?query='.$query);
}

function addContact($name, $deal, $tel, $email)
{
	$contact = array('request'=>array('contacts'=>array('add'=>array())));
	$contact['request']['contacts']['add']=array(
	  array(
	    'name'=>$name, #Имя контакта
	    //'last_modified'=>1298904164, //optional
	    'linked_leads_id'=>array( #Список с айдишниками сделок контакта
	      $deal
	    ),
	    'custom_fields'=>array(
	      array(
	        #Телефоны
	        'id'=>1589084, #Уникальный идентификатор заполняемого дополнительного поля
	        'values'=>array(
	          array(
	            'value'=>$tel,
	            'enum'=>'MOB' #Мобильный
	          )
	        )
	      ),
	      array(
	        #E-mails
	        'id'=>1589086,
	        'values'=>array(
	          array(
	            'value'=>$email,
	            'enum'=>'WORK', #Рабочий
	          )
	        )
	      )
	    )
	  )
	);
	return meth('v2/json/contacts/set',$contact);
}

function addDeal($name, $price, $status_id, $user_id, $tags, $url)
{
	$leads = array('request'=>array('leads'=>array('add'=>array())));
	$leads['request']['leads']['add']=array(
	  array(
	    'name'=>$name,
	    //'date_create'=>1298904164, //optional
	    'status_id'=>$status_id,
	    'price'=>$price,
	    'tags'=>$tags,
	    'responsible_user_id'=>$user_id,
	    'custom_fields'=>array(
	    	array(
	    		'id'=>1944701,
	    		'values'=>array(
		          array(
		            'value'=>$url
		          )
		        )
	    	)
	    )
	  )
	);
	return meth('v2/json/leads/set',$leads);
}

function addNote($deal, $comments, $user)
{
	$notes = array('request'=>array('notes'=>array('add'=>array())));
	$notes['request']['notes']['add']=array(
	  array(
	    'element_id'=>$deal,
	    'element_type'=>2,
	    'note_type'=>4,
	    'text'=>$comments,
	  )
	);
	return meth('v2/json/notes/set',$notes);
}

function addTask($name, $deal, $user_id)
{
	$tasks = array('request'=>array('tasks'=>array('add'=>array())));
	$tasks['request']['tasks']['add']=array(
	  #Привязываем к сделке
	  array(
	    'element_id'=>$deal, #ID сделки
	    'element_type'=>2, #Показываем, что это - сделка, а не контакт
	    'task_type'=>1, #Звонок
	    'text'=>$name,
	    'responsible_user_id'=>$user_id,
	    'complete_till'=>time()+300
	  )
	);
	return meth('v2/json/tasks/set',$tasks);
}

function meth($meth, $data)
{
	$subdomain='phottyru'; #Наш аккаунт - поддомен
 
	#Формируем ссылку для запроса
	$link='https://'.$subdomain.'.amocrm.ru/private/api/'.$meth;

	$curl=curl_init(); #Сохраняем дескриптор сеанса cURL
	#Устанавливаем необходимые опции для сеанса cURL
	curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
	curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-API-client/1.0');
	curl_setopt($curl,CURLOPT_URL,$link);
	curl_setopt($curl,CURLOPT_CUSTOMREQUEST,'POST');
	curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($data));
	curl_setopt($curl,CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
	curl_setopt($curl,CURLOPT_HEADER,false);
	curl_setopt($curl,CURLOPT_COOKIEFILE,dirname(__FILE__).'/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
	curl_setopt($curl,CURLOPT_COOKIEJAR,dirname(__FILE__).'/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
	curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
	curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
	 
	$out=curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
	$code=curl_getinfo($curl,CURLINFO_HTTP_CODE); #Получим HTTP-код ответа сервера
	curl_close($curl); #Завершаем сеанс cURL

	$code=(int)$code;
	$errors=array(
	  301=>'Moved permanently',
	  400=>'Bad request',
	  401=>'Unauthorized',
	  403=>'Forbidden',
	  404=>'Not found',
	  500=>'Internal server error',
	  502=>'Bad gateway',
	  503=>'Service unavailable'
	);
	try
	{
	  #Если код ответа не равен 200 или 204 - возвращаем сообщение об ошибке
	  if($code!=200 && $code!=204)
	    throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error',$code);
	}
	catch(Exception $E)
	{
	  die('Ошибка: '.$E->getMessage().PHP_EOL.'Код ошибки: '.$E->getCode());
	}
	 
	/**
	 * Данные получаем в формате JSON, поэтому, для получения читаемых данных,
	 * нам придётся перевести ответ в формат, понятный PHP
	 */
	$Response=json_decode($out,true);
	$Response=$Response['response'];
	return $Response;
}

function methGet($meth)
{
	$subdomain='phottyru'; #Наш аккаунт - поддомен
 
	#Формируем ссылку для запроса
	$link='https://'.$subdomain.'.amocrm.ru/private/api/'.$meth;

	$curl=curl_init(); #Сохраняем дескриптор сеанса cURL
	#Устанавливаем необходимые опции для сеанса cURL
	curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
	curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-API-client/1.0');
	curl_setopt($curl,CURLOPT_URL,$link);
	curl_setopt($curl,CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
	curl_setopt($curl,CURLOPT_HEADER,false);
	curl_setopt($curl,CURLOPT_COOKIEFILE,dirname(__FILE__).'/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
	curl_setopt($curl,CURLOPT_COOKIEJAR,dirname(__FILE__).'/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
	curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
	curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
	 
	$out=curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
	$code=curl_getinfo($curl,CURLINFO_HTTP_CODE); #Получим HTTP-код ответа сервера
	curl_close($curl); #Завершаем сеанс cURL

	$code=(int)$code;
	$errors=array(
	  301=>'Moved permanently',
	  400=>'Bad request',
	  401=>'Unauthorized',
	  403=>'Forbidden',
	  404=>'Not found',
	  500=>'Internal server error',
	  502=>'Bad gateway',
	  503=>'Service unavailable'
	);
	try
	{
	  #Если код ответа не равен 200 или 204 - возвращаем сообщение об ошибке
	  if($code!=200 && $code!=204)
	    throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error',$code);
	}
	catch(Exception $E)
	{
	  die('Ошибка: '.$E->getMessage().PHP_EOL.'Код ошибки: '.$E->getCode());
	}
	 
	/**
	 * Данные получаем в формате JSON, поэтому, для получения читаемых данных,
	 * нам придётся перевести ответ в формат, понятный PHP
	 */
	$Response=json_decode($out,true);
	$Response=$Response['response'];
	return $Response;
}

?>