<?php
require 'db.php';
include "vk_api.php";

const VK_KEY = "43e04b5c6040b9c91290329212e7255d35e6525b2e1a3a029ab734058651a586da03c04ba0877a8ae221a";
const ACCESS_KEY = "082287";
const VERSION = "5.81";

$vk = new vk_api(VK_KEY, VERSION);
$data = json_decode(file_get_contents('php://input'));
//print_r($data);
if ($data->type == 'confirmation') {
    exit(ACCESS_KEY);
}
$vk->sendOK();
// ---------- Переменные ----------
$peer_id = $data->object->peer_id;
$id = $data->object->from_id;
$chat_id = $peer_id - 2000000000;
$is_admin = [597334435]; // создаем массив с ID's наших будущих админов через запятую
// ---------- Сообщение ----------
$message = $data->object->text;
$messages = explode(" ", $message);
$cmd = mb_strtolower(str_replace(array("/", "!"), "", $messages[0]));
$args = array_slice($messages, 1);
// ---------- Другое ----------
$reply_message = $data->object->reply_message;
$reply_author = $data->object->reply_message->from_id;
$chat_act = $data->object->action;
$fwd_messages = $object['fwd_messages'];
if(empty($fwd_messages) && !empty($reply_message)) {
  array_push($fwd_messages, $reply_message);
}
if(empty($reply_message) && !empty($fwd_messages)) {
  $reply_message = $fwd_messages[0];
}
if ( !R::testConnection() )
{
    $vk->sendMessage($peer_id, "Нет соединения с базой данных, обратитесь к администраторам ");
    exit;
}
if ($data->type == 'message_new') {
	$userInfo = $vk->request("users.get", ["user_ids" => $id]);
    $first_name = $userInfo[0]['first_name'];
    $first_name2 = $userInfo[0]['last_name'];
    //---------------------------------------------------------
    $user = R::findOne('users', 'user_id = ?', [$id]);
    if($id <= '-') exit; // Т.к. ид не правильный завершили скрипт
    if(!$user){
        $vk->registrationUser($id);
        $vk->sendMessage($peer_id, "@id{$id} ({$first_name}), добро пожаловать )))"); // Уведомили что регистрация прошла успешно
        exit; // Завершили скрипт для избежания ошибок
    }
//--------------------------------------------------------------------------------------------
  if($chat_id > 0){//Проверка на админкиу в чате
    if(!$vk->isChatAdmin($peer_id)){
      $vk->sendMessage($peer_id, "⚠ Мне необходимы права администратора ⚠");
      exit;
    }
    else{// проверка на регистрацию чата
      $chat = R::findOne('settings', 'peer_id = ?', [$peer_id]);
      if(!$chat){
        $vk->registrationChatSettings($peer_id);
      }
    }
  }

//--------------------------------------------------------------------------------------------
    if($user){
        $user['score'] = $user['score'] +1; // Отправили указаному пользователю сумму
        R::store($user); // Записали в базу
		
		if ($user['score'] == 300) {
        	$user['status'] = 1; // 
        	R::store($user); // Записали в базу
			$vk->sendMessage($peer_id, "@id{$id} ({$first_name}), У вас новое звание: Новичок!");
		} elseif ($user['score'] == 600) {
        	$user['status'] = 2; // 
        	R::store($user); // Записали в базу
			$vk->sendMessage($peer_id, "@id{$id} ({$first_name}), У вас новое звание: Постоялец!");
		} elseif ($user['score'] == 1000) {
        	$user['status'] = 3; // 
        	R::store($user); // Записали в базу
			$vk->sendMessage($peer_id, "@id{$id} ({$first_name}), У вас новое звание: Писатель!");
		} elseif ($user['score'] == 2000) {
        	$user['status'] = 4; // 
        	R::store($user); // Записали в базу
			$vk->sendMessage($peer_id, "@id{$id} ({$first_name}), У вас новое звание: Философ!");
		} elseif ($user['score'] == 3000) {	
        	$user['status'] = 5; // 
        	R::store($user); // Записали в базу
			$vk->sendMessage($peer_id, "@id{$id} ({$first_name}), У вас новое звание: Мастер!");
		} elseif ($user['score'] == 5000) {
        	$user['status'] = 6; // 
        	R::store($user); // Записали в базу
			$vk->sendMessage($peer_id, "@id{$id} ({$first_name}), У вас новое звание: Звездный гуру!");
		}
	} 	

  if(in_array($cmd, ['помощь', 'help'])){
    if($chat_id > 0){ // Если это беседа
	  $vk->sendMessage($peer_id, "Команда 'помощь' доступна только в лс боту!");
    }else{ // Если это лс с ботом
	  $vk->sendMessage($peer_id, "Список команд (вводите без скобак[]): <br>баланс - Узнать сколько кремпаев<br>статус - Узнать своё звание и статистику<br>передать [кому] [сколько] - Перадать свои кремпаи<br>онлайн - Узнать онлайн<br>погода [город] - Узнать погоду<br>топ - Топ 5 по сообщениям<br>арт - Рандом картинки<br>видео - Рандомное видео");
    }
  }
  
  if(in_array($cmd, ['бот', 'bot'])){
    if($chat_id > 0){ // Если это беседа
      $vk->sendMessage($peer_id, "Эта версия бота в стадии разработки!<br>Автор данного бота: Бикбай");
    }else{ // Если это лс с ботом
      $vk->sendMessage($peer_id, "Команда 'бот' доступна только в беседах");
    }
  }
  
  
   if(in_array($cmd, ['баланс', 'balans'])){
     $get_user = R::findOne('users', 'user_id = ?', [$id]);
	 $balancebd = $get_user['balance'];
	 $vk->sendMessage($peer_id, "@id{$id} ({$first_name}), Ваш баланс: {$balancebd} кремпаев!");
  }
  
	if(in_array($cmd, ['status', 'статус'])){
    if($chat_id > 0){ // Если это беседа
	 $scoreall = $user['score'];
		if ($user['status'] == 0) {
        	$zvaniestr = "Тень";
		} elseif ($user['status'] == 1) {
        	$zvaniestr = "Новичок";
		} elseif ($user['status'] == 2) {
        	$zvaniestr = "Постоялец";
		} elseif ($user['status'] == 3) {
        	$zvaniestr = "Писатель";
		} elseif ($user['status'] == 4) {
        	$zvaniestr = "Философ";
		} elseif ($user['status'] == 5) {	
        	$zvaniestr = "Мастер";
		} elseif ($user['status'] == 6) {
        	$zvaniestr = "Звездный гуру";
		} elseif ($user['status'] == 7) {
        	$zvaniestr = "Личная сучка админа";
		} elseif ($user['status'] == 8) {
        	$zvaniestr = "Бог Хентая";
		} elseif ($user['status'] == 9) {
        	$zvaniestr = "Кровавый ангел";
		}
	$vk->sendMessage($peer_id, "@id{$id} ({$first_name}), Ваше звание: {$zvaniestr}! <br>Первое появление: ❗{$user['regDate']}<br>Всего сообщений от вас: {$scoreall}");
	
    }else{ // Если это лс с ботом
      $vk->sendMessage($peer_id, "Команда 'статус' доступна только в беседах");
    }
  }
 // ---------- Картинки ----------
	if(in_array($cmd, ['art', 'арт'])){
    if($chat_id > 0){ // Если это беседа
        $img = ['-1_456239099', '-1_456239099'];
		$rand_img = array_rand($img, 1);
		$vk->request('messages.send', ['peer_id' => $peer_id, 'message' => '', 'attachment' => "photo{$img[$rand_img]}"]);
    }else{ // Если это лс с ботом
      $vk->sendMessage($peer_id, "Команда 'арт' доступна только в беседах");
    }
  }
	if(in_array($cmd, ['media', 'видео'])){
    if($chat_id > 0){ // Если это беседа
        $img = ['-1_456239018', '-1_456239018'];
		$rand_img = array_rand($img, 1);
		$vk->request('messages.send', ['peer_id' => $peer_id, 'message' => '', 'attachment' => "video{$img[$rand_img]}"]);
    }else{ // Если это лс с ботом
      $vk->sendMessage($peer_id, "Команда 'media' доступна только в беседах");
    }
  }
  if(in_array($cmd, ['top', 'топ'])){
    $topUser = R::findAll('users' , ' ORDER BY score DESC LIMIT 5 '); //запросили 5 записей с наибольшим значением колонке score в таблице users
	$strtop5;
    foreach ($topUser as $usertop) { //Цикл. Подробнее почитайте в документации к PHP
       $strtop5 .= "@id{$usertop['user_id']} ({$usertop['nick']}) набрал {$usertop['score']} сообщений<br>"; // Выводим нужные данные из полученных пользователей
    }
	$allUsers = R::count('users');
	$vk->sendMessage($peer_id, "Топ 5 по количеству сообщений!<br><br>{$strtop5}<br><br> Всего участников: {$allUsers}"); // Пишем
  }
 // ---------- ---------------------------- админ команда
  if(in_array($cmd, ['kick'])){
	if (in_array($id, $is_admin) || $user['admin'] >= 1) { // С помощью in_array проверяем схожесть переменной $id с массивом с ID's - амдин или нет
    $trUser = $reply_author; // Создали переменную кому перевести
    if($trUser == ''){ // Узнаем указал ли пользователь кому перевести при помощи пересланного сообщения
      if($args[0] == ''){ // Проверили ввели ли нам два аргумента (пользователь и сумма) если пересланного сообщения нет
        $vk->sendMessage($peer_id, "@id{$id} ({$user['nick']}) Вы не указали кого кикнуть!");
        exit; // Завершили скрипт т.к. не указали пользователя для перевода
      }
      if($args[1] == ''){ // Проверили ввели ли нам два аргумента (пользователь и сумма) если пересланного сообщения нет
        $vk->sendMessage($peer_id, "@id{$id} ({$user['nick']}) Вы не указали причину!");
        exit; // Завершили скрипт т.к. не указали пользователя для перевода
      }
	  $trUser = $args[0]; // Записали кому перевести из аргумета
      $Prosina = $args[1]; // заменили в сумме id на сумму перевода
    }
    $trUser = preg_replace('/\D/', '', $trUser); // Убрали все буквы из переменной id пользоавтеля для перевода
    if($trUser == ''){ // проверяем сумму на пустоту (Обязательно)
      $vk->sendMessage($peer_id, "@id{$id} ({$user['nick']})Вы не указали кого кикнуть!");
      exit; // Т.к. сумма не указана мы завершили скрипт
    }else{ // Если сумма указана, то делаем перевод (почти)
      $findTrUser = R::findOne('users', 'user_id = ?', [$trUser]);
      if($findTrUser){
		$newUser2 = R::dispense("usersban"); // Выбрали таблицу
    	$newUser2->user_id = $findTrUser['user_id'];
		$newUser2->user_name = $findTrUser['nick']; 
    	$newUser2->admin_id = $id;
		$newUser2->admin_name = $user['nick']; 
    	$newUser2->prisina = $Prosina;
    	$newUser2->dateBan = date("d.m.Y, H:i:s");
		$newUser2->beseda_id = $chat_id;
    	R::store($newUser2); // Записали в базу
	
        $vk->sendMessage($peer_id, "@id{$id} ({$user['nick']}), выгнал @id{$findTrUser['user_id']} ({$findTrUser['nick']}), по причине: {$Prosina}"); // Пишем что перевод прошел успешно
        $vk->sendMessage($findTrUser['user_id'], "@id{$id} ({$user['nick']}) выгнал вас, по причине: {$Prosina}"); // Пишем пользователю что ему кинули коинов
		$vk->request('messages.removeChatUser', ['chat_id' => $chat_id, 'member_id' => $findTrUser['user_id']]);
      }else{
        $vk->sendMessage($peer_id, "@id{$id} ({$user['nick']}), указанный пользователь не был найден в базе!");
        exit; // в базе нет указаного пользователя из-за чего завершаем скрипт
      }
    }
    } else {
            $vk->sendMessage($peer_id, "Доступно только для личных сучек!");

        }
  }
   if(in_array($cmd, ['setadmin'])){
	if (in_array($id, $is_admin)) { // С помощью in_array проверяем схожесть переменной $id с массивом с ID's - амдин или нет
    $trUser = $reply_author; // Создали переменную кому перевести
    if($trUser == ''){ // Узнаем указал ли пользователь кому перевести при помощи пересланного сообщения
      if($args[0] == ''){ // Проверили ввели ли нам два аргумента (пользователь и сумма) если пересланного сообщения нет
        $vk->sendMessage($peer_id, "@id{$id} ({$user['nick']}) Вы не указали кому выдать!");
        exit; // Завершили скрипт т.к. не указали пользователя для перевода
      }
      if($args[1] == ''){ // Проверили ввели ли нам два аргумента (пользователь и сумма) если пересланного сообщения нет
        $vk->sendMessage($peer_id, "@id{$id} ({$user['nick']}) Вы не указали уровень!");
        exit; // Завершили скрипт т.к. не указали пользователя для перевода
      }
	  $trUser = $args[0]; // Записали кому перевести из аргумета
      $lvladmin = preg_replace('/\D/', '', $args[1]); // заменили в сумме id на сумму перевода
    }
    $trUser = preg_replace('/\D/', '', $trUser); // Убрали все буквы из переменной id пользоавтеля для перевода
    if($trUser == ''){ // проверяем сумму на пустоту (Обязательно)
      $vk->sendMessage($peer_id, "@id{$id} ({$user['nick']})Вы не указали кому выдать!");
      exit; // Т.к. сумма не указана мы завершили скрипт
    }else{ // Если сумма указана, то делаем перевод (почти)
      $findTrUser = R::findOne('users', 'user_id = ?', [$trUser]);
      if($findTrUser){
        $findTrUser['admin'] = $lvladmin; //
		R::store($findTrUser); // Записали в базу
		$upadmin = R::dispense("usersadmin"); // Выбрали таблицу
    	$upadmin->user_id = $findTrUser['user_id'];
		$upadmin->user_name = $findTrUser['nick']; 
    	$upadmin->admin_id = $id;
		$upadmin->admin_name = $user['nick']; 
    	$upadmin->lvl = $lvladmin;
    	$upadmin->dateAdmin = date("d.m.Y, H:i:s");
		$upadmin->beseda_id = $chat_id;
    	R::store($upadmin); // Записали в базу
	
        $vk->sendMessage($peer_id, "@id{$id} ({$user['nick']}), назначил @id{$findTrUser['user_id']} ({$findTrUser['nick']}) администратором {$lvladmin} уровня!"); // Пишем
        $vk->sendMessage($findTrUser['user_id'], "@id{$id} ({$user['nick']}) назначил вас администратором {$lvladmin} уровня!"); // Пишем пользователю
      }else{
        $vk->sendMessage($peer_id, "@id{$id} ({$user['nick']}), указанный пользователь не был найден в базе!");
        exit; // в базе нет указаного пользователя из-за чего завершаем скрипт
      }
    }
    } else {
            //$vk->sendMessage($peer_id, "Доступно только для создателя Бота!");

        }
  }
   if(in_array($cmd, ['setbalans'])){
	if (in_array($id, $is_admin)) { // С помощью in_array проверяем схожесть переменной $id с массивом с ID's - амдин или нет
    $trUser = $reply_author; // Создали переменную кому перевести
    if($trUser == ''){ // Узнаем указал ли пользователь кому перевести при помощи пересланного сообщения
      if($args[0] == ''){ // Проверили ввели ли нам два аргумента (пользователь и сумма) если пересланного сообщения нет
        $vk->sendMessage($peer_id, "@id{$id} ({$user['nick']}) Вы не указали кому выдать!");
        exit; // Завершили скрипт т.к. не указали пользователя для перевода
      }
      if($args[1] == ''){ // Проверили ввели ли нам два аргумента (пользователь и сумма) если пересланного сообщения нет
        $vk->sendMessage($peer_id, "@id{$id} ({$user['nick']}) Вы не указали уровень!");
        exit; // Завершили скрипт т.к. не указали пользователя для перевода
      }
	  $trUser = $args[0]; // Записали кому перевести из аргумета
      $lvladmin = preg_replace('/\D/', '', $args[1]); // заменили в сумме id на сумму перевода
    }
    $trUser = preg_replace('/\D/', '', $trUser); // Убрали все буквы из переменной id пользоавтеля для перевода
    if($trUser == ''){ // проверяем сумму на пустоту (Обязательно)
      $vk->sendMessage($peer_id, "@id{$id} ({$user['nick']})Вы не указали кому выдать!");
      exit; // Т.к. сумма не указана мы завершили скрипт
    }else{ // Если сумма указана, то делаем перевод (почти)
      $findTrUser = R::findOne('users', 'user_id = ?', [$trUser]);
      if($findTrUser){
		$findTrUser['balance'] = $findTrUser['balance'] + $lvladmin;
		R::store($findTrUser); // Записали в базу
		$upadmin = R::dispense("usersbalans"); // Выбрали таблицу
    	$upadmin->user_id = $findTrUser['user_id'];
		$upadmin->user_name = $findTrUser['nick']; 
    	$upadmin->admin_id = $id;
		$upadmin->admin_name = $user['nick']; 
    	$upadmin->balansup = $lvladmin;
    	$upadmin->dateAdmin = date("d.m.Y, H:i:s");
		$upadmin->beseda_id = $chat_id;
    	R::store($upadmin); // Записали в базу
	
        $vk->sendMessage($peer_id, "@id{$id} ({$user['nick']}), выдал @id{$findTrUser['user_id']} ({$findTrUser['nick']}) {$lvladmin} кремпаев!"); // Пишем
        $vk->sendMessage($findTrUser['user_id'], "@id{$id} ({$user['nick']}) выдал вам {$lvladmin} кремпаев!"); // Пишем пользователю
      }else{
        $vk->sendMessage($peer_id, "@id{$id} ({$user['nick']}), указанный пользователь не был найден в базе!");
        exit; // в базе нет указаного пользователя из-за чего завершаем скрипт
      }
    }
    } else {
            //$vk->sendMessage($peer_id, "Доступно только для создателя Бота!");

        }
  }
//--------------------------------------------------------------------------------------------
  if(in_array($cmd, ['перевод', 'перевести', 'передать'])){
    $trUser = $reply_author; // Создали переменную кому перевести
    $sumTr = preg_replace('/\D/', '', $args[0]); // Указали сумму перевода без букв
    if($trUser == ''){ // Узнаем указал ли пользователь кому перевести при помощи пересланного сообщения
      if($args[1] != ''){ // Проверили ввели ли нам два аргумента (пользователь и сумма) если пересланного сообщения нет
        $trUser = $args[0]; // Записали кому перевести из аргумета
        $sumTr = preg_replace('/\D/', '', $args[1]); // заменили в сумме id на сумму перевода
      }else{
        $vk->sendMessage($peer_id, "@id{$id} ({$user['nick']}) не указали кому перевести валюту!");
        exit; // Завершили скрипт т.к. не указали пользователя для перевода
      }
    }
    $trUser = preg_replace('/\D/', '', $trUser); // Убрали все буквы из переменной id пользоавтеля для перевода
    if($trUser == ''){ // проверяем сумму на пустоту (Обязательно)
      $vk->sendMessage($peer_id, "@id{$id} ({$user['nick']}) не указали сумму перевода!");
      exit; // Т.к. сумма не указана мы завершили скрипт
    }else{ // Если сумма указана, то делаем перевод (почти)
      $findTrUser = R::findOne('users', 'user_id = ?', [$trUser]);
      if($findTrUser){
        $findTrUser['balance'] = $findTrUser['balance'] + $sumTr; // Отправили указаному пользователю сумму
        $user['balance'] = $user['balance'] - $sumTr; // Сняли эту сумму у пользователя который отправил
        R::store($findTrUser); // Записали в базу
        R::store($user); // Записали в базу
        $vk->sendMessage($peer_id, "@id{$id} ({$user['nick']}), вы успешно передали {$sumTr} кремпая пользователю @id{$findTrUser['user_id']} ({$findTrUser['nick']})"); // Пишем что перевод прошел успешно
        $vk->sendMessage($findTrUser['user_id'], "@id{$id} ({$user['nick']}) передал(а) вам {$sumTr} кремпая )"); // Пишем пользователю что ему кинули коинов
      }else{
        $vk->sendMessage($peer_id, "@id{$id} ({$user['nick']}), указанный пользователь не был найден в базе!");
        exit; // в базе нет указаного пользователя из-за чего завершаем скрипт
      }
    }
  }
  if(in_array($cmd, ['онлайн', 'online'])){
    if($chat_id > 0){ // Если это беседа
      $members = $vk->request('messages.getConversationMembers', ['peer_id' => $peer_id]); // Запрос на получение данных о пользователях беседы
      foreach ($members['profiles'] as $useronline) { // При помощи foreach производим работу над данными из пришедшего нам массива
        if ($useronline['online'] == 1) { // Если проверяемый пользователь в сети
          $userOnline++; // Добавляем 1 к общему числу онлайна

          $userInfoOnline = $vk->request("users.get", ["user_ids" => $useronline['id'], "fields" => "last_seen"]); // Запрос данных пользователя
          $first_nameOnline = $userInfoOnline[0]['first_name']; // Имя
          $last_nameOnline = $userInfoOnline[0]['last_name']; // Фамилия
          $platformOnline = $userInfoOnline[0]['last_seen']['platform']; // Платформа
          if ($platformOnline >= 1 && $platformOnline <= 5) { // 1 - 5 отнесем к телефонам
            $platformOnline = '📱';
          }else{ // остальные ПК
            $platformOnline = '💻';
          }
          $Onlinelist .= "🗣 @id{$useronline['id']} ({$first_nameOnline} {$last_nameOnline})"."   - ".$platformOnline."\n"; // Составили текст с онлайн людьми
        }
      }
      $vk->sendMessage($peer_id, "
      📍 Сейчас в сети: {$userOnline} 📍:
      {$Onlinelist}
      ");
    }else{ // Если это лс с ботом
      $vk->sendMessage($peer_id, "Команда 'Онлайн' доступна только в беседах");
    }
  }
  if(in_array($cmd, ['погода', 'погодка', 'weather'])){
    $city = implode(" ", $args); // Объединили текст после команды в единый
    if($city == ''){ // Проверка на указание города
      $vk->sendMessage($peer_id, "Вы не указали город. Пример: Погода Москва");
      exit; // Завершаем скрипт т.к. не указан город
    }
    $OWApi_key = 'c6c48db8e2970d6002267e6bcba21e1d'; // Ваш ключ от OpenWeatherMap - 18486676487135921cb7c6c8bbb44b3e
    $weather=json_decode(file_get_contents("https://api.openweathermap.org/data/2.5/weather?q={$city}&units=metric&appid={$OWApi_key}&lang=ru")); // Составили запрос к OpenWeatherMap
    if(empty($weather)){ // Если ответ не пришел
      $vk->sendMessage($peer_id, "✖ Ой.. Прости я не поняла, что это за город такой 😿");
    }else{ // Если ответ есть, то составляем текст для вывода
      $list = "В городе " . $weather->name; // Название города
      $list .= "\n🔮 Погода: " . $weather->weather[0]->description; // Название погода (пример: облачно/солнечно)
      $list .= "\n💨 Ветер: " . $weather->wind->speed. " m/s "; // Скорость ветра
      $list .= "\n🌡 Температура: " . $weather->main->temp . "°C"; // Температура
      $list .= "\n☁ Облачность: " . $weather->clouds->all . "%"; // Облачность в процентах
      $list .= "\n📊 Давление: " . $weather->main->pressure . " мм.рт.ст"; // Давление
      $vk->sendMessage($peer_id, $list); // Вывели погоду
    }
  }
//--------------------------------------------------------------------------
  if(implode(' ', $messages) == 'я админ беседы?'){//если человек является администратором
    if($vk->isAdmin($peer_id, $id)){
        $vk->sendMessage($peer_id, "@id{$id} ({$user['nick']}), вы админ!");
    }else{
      $vk->sendMessage($peer_id, "У вас нет доступа к этой команде!");
    } 
  }
//--------------------------------------------------------------------------
 if($chat_act->type == 'chat_kick_user'){//авто кик из беседы
    $userInfo = $vk->request("users.get", ["user_ids" => $id]);
    $first_name = $userInfo[0]['first_name'];
	if($chat_act->member_id <= '-') exit; // Т.к. ид не правильный завершили скрипт
    $strtkick = "@id{$chat_act->member_id} ({$first_name}) был(а) исключен(а) за то что покинул(а) беседу!";
    $vk->request('messages.send', ['peer_id' => $peer_id, 'message' => $strtkick, 'attachment' => "photo566868656_457239109"]);
    $vk->request('messages.removeChatUser', ['chat_id' => $chat_id, 'member_id' => $chat_act->member_id]);
  }
  
if($chat_act->type == 'chat_title_update'){
    $chatSettings = R::findOne('settings', 'peer_id = ?', [$peer_id]);
    $chatSettings->title = $chat_act->text;
    R::store($chatSettings);
    $vk->sendMessage($peer_id, "💬 Новое название чата: {$chat_act->text}");
	//$vk->request('messages.editChat', ['chat_id' => $chat_id, 'title' => 'title']);//изменяет название чата
  }
//--------------------------------------------------------------------------
}
