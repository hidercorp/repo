<?
require 'classes/Curl.php';
require 'classes/PDO.php';

$curl = new Curl();

// Получаем информацию из БД о настройках бота
$set_bot = DB::$the->query("SELECT * FROM `sel_set_bot` ");
$set_bot = $set_bot->fetch(PDO::FETCH_ASSOC);
$token		= $set_bot['token']; // токен бота


// Получаем всю информацию о настройках киви
$us_qiwi = DB::$the->query("SELECT password FROM `sel_set_qiwi` WHERE `number` = '".$user['pay_number']."' ");
$us_qiwi = $us_qiwi->fetch(PDO::FETCH_ASSOC);

// Получаем информацию о ключе 
$key = DB::$the->query("SELECT * FROM `sel_keys` WHERE `id` = '".$user['id_key']."' ");
$key = $key->fetch(PDO::FETCH_ASSOC);

// Получаем всю информацию о пользователе
$user = DB::$the->query("SELECT * FROM `sel_users` WHERE `chat` = {$argv[1]} ");
$user = $user->fetch(PDO::FETCH_ASSOC);

// Получаем всю информацию о балансе пользователя
$user = DB::$the->query("SELECT * FROM `sel_users` WHERE `balans` = '".$user['balans']."' ");
$user = $user->fetch(PDO::FETCH_ASSOC);



$active_number = $set_qiwi['qiwi_number'];
$active_password = $set_qiwi['qiwi_password'];
	
// Если номер доступен для оплаты

if($set_bot['text_page'] != '') {
    $text = $set_bot['text_page'];
} else {
    $text = 'Текст отсутствует 😔';
}

$arr = array();	

$arr[] = array("🔙 Назад");	

	$replyMarkup = array(
	'resize_keyboard' => true,
    'keyboard' => 
	$arr 
	
);
$menu = json_encode($replyMarkup);
	
// Отправляем текст сверху пользователю
$curl->get('https://api.telegram.org/bot'.$token.'/sendMessage',array(
	'chat_id' => $argv[1],
	'text' => $text,
	'reply_markup' => $menu,
	));
exit;	
?>
