<?
require 'classes/Curl.php';
require 'classes/PDO.php';

$curl = new Curl();

$json = file_get_contents('php://input'); // Получаем запрос от пользователя
$action = json_decode($json, true); // Расшифровываем JSON

// Получаем информацию из БД о настройках бота
$set_bot = DB::$the->query("SELECT * FROM `sel_set_bot` ");
$set_bot = $set_bot->fetch(PDO::FETCH_ASSOC);

$message	= $action['message']['text']; // текст сообщения от пользователя
$chat		= $action['message']['chat']['id']; // ID чата
$username	= $action['message']['from']['username']; // username пользователя
$first_name	= $action['message']['from']['first_name']; // имя пользователя
$last_name	= $action['message']['from']['last_name']; // фамилия пользователя
$token		= $set_bot['token']; // токен бота
$title_p = trim($set_bot['title_page']);
$text_p = trim($set_bot['text_page']);

// Если бот отключен, прерываем все!
if($set_bot['on_off'] == "off") exit;

if(!$chat) exit; 

// Проверяем наличие пользователя в БД
$vsego = DB::$the->query("SELECT chat FROM `sel_users` WHERE `chat` = {$chat} ");
$vsego = $vsego->fetchAll();

// Если отсутствует, записываем его
if(count($vsego) == 0){ 

// Записываем в БД
$params = array('username' => $username, 'first_name' => $first_name, 'last_name' => $last_name, 
'chat' => $chat, 'time' => time() );  
 
$q = DB::$the->prepare("INSERT INTO `sel_users` (username, first_name, last_name, chat, time) 
VALUES (:username, :first_name, :last_name, :chat, :time)");  
$q->execute($params);	
}

// Получаем всю информацию о пользователе
$user = DB::$the->query("SELECT ban FROM `sel_users` WHERE `chat` = {$chat} ");
$user = $user->fetch(PDO::FETCH_ASSOC);

// Если юзер забанен, отключаем для него все!
if($user['ban'] == "1") exit;

// Получаем всю информацию о настройках киви
$set_qiwi = DB::$the->query("SELECT * FROM `sel_set_qiwi` ");
$set_qiwi = $set_qiwi->fetch(PDO::FETCH_ASSOC);


// Если сделан запрос /verification
if($message == '✅ Я оплатил товар'){
    #$chat = escapeshellarg($chat);
    #exec('bash -c "exec nohup setsid wget -q -O - '.$set_bot['url'].'/verification.php?chat='.$chat.' > /dev/null 2>&1 &"');
    $curl->get("{$set_bot['url']}/verification.php?chat=$chat");
    exit;
}

// Если сделан запрос /select
$pos = strpos($message, "🔹");	
$pos2 = strpos($message, "🔸");	
if ($pos !== false or $pos2 !== false) 
{	
$chat = escapeshellarg($chat);	
$message = escapeshellarg(base64_encode($message));	
exec('bash -c "exec nohup setsid php ./select.php '.$chat.' '.$message.' > /dev/null 2>&1 &"');
exit;
}

// Если проверяют список покупок
if($message == $title_p){
$chat = escapeshellarg($chat);	
exec('bash -c "exec nohup setsid php ./page.php '.$chat.' > /dev/null 2>&1 &"');
exit;
}

// Если проверяют список покупок
if($message == '📦 Заказы'){
$chat = escapeshellarg($chat);	
exec('bash -c "exec nohup setsid php ./orders.php '.$chat.' > /dev/null 2>&1 &"');
exit;
}

// Если проверяют список покупок
if($message == '✔️ Обновления'){
$chat = escapeshellarg($chat);	
exec('bash -c "exec nohup setsid php ./update.php '.$chat.' > /dev/null 2>&1 &"');
exit;
}
	
$arr = array();
	
	if($title_p != '') {

// Выводим в меню список категорий
$query = DB::$the->query("SELECT name FROM `sel_category` order by `mesto` ");
while($cat = $query->fetch()) {
$arr[] = array("🔹".$cat['name']."");	
}
$arr[] = array("📦 Заказы", "$title_p");	
#$arr[] = array("❗️ Информация");
	$replyMarkup = array(
	'resize_keyboard' => true,
    'keyboard' => 
	$arr 
	
);
} else {

// Выводим в меню список категорий
$query = DB::$the->query("SELECT name FROM `sel_category` order by `mesto` ");
while($cat = $query->fetch()) {
$arr[] = array("🔹".$cat['name']."");	
}
$arr[] = array("📦 Заказы");	
	$replyMarkup = array(
	'resize_keyboard' => true,
    'keyboard' => 
	$arr 
);
}

$menu = json_encode($replyMarkup);


// Отправляем все это пользователю
$curl->get('https://api.telegram.org/bot'.$token.'/sendMessage',array(
	'chat_id' => $chat,
	'text' => 'Выберите город',
	'reply_markup' => $menu,	
	)); 

?>
