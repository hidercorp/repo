<?
require 'classes/Curl.php';
require 'classes/PDO.php';

$curl = new Curl();

// Получаем информацию из БД о настройках бота
$set_bot = DB::$the->query("SELECT token,block FROM `sel_set_bot` ");
$set_bot = $set_bot->fetch(PDO::FETCH_ASSOC);
$token		= $set_bot['token']; // токен бота

$chat = $argv[1];
$message = base64_decode($argv[2]);

$user = DB::$the->query("SELECT ban,id_key,balans FROM `sel_users` WHERE `chat` = {$chat} ");
$user = $user->fetch(PDO::FETCH_ASSOC);




// Узнаем, сколько подкатегориев в категории
$nulled = DB::$the->query("SELECT id FROM `sel_keys` where `sale` = '0' and `block` = '1' and `block_time` < '".(time()-(60*$set_bot['block']))."' ");
$nulled = $nulled->fetchAll();

if(count($nulled > 0)){


$query = DB::$the->query("SELECT block_user FROM `sel_keys` where `sale` = '0' and `block` = '1' and `block_time` < '".(time()-(60*$set_bot['block']))."' order by `id` ");
while($us = $query->fetch()) {
	
DB::$the->prepare("UPDATE sel_keys SET block=? WHERE block_user=? ")->execute(array("0", $us['block_user'])); 
DB::$the->prepare("UPDATE sel_keys SET block_time=? WHERE block_user=? ")->execute(array('0', $us['block_user'])); 
DB::$the->prepare("UPDATE sel_keys SET block_user=? WHERE block_user=? ")->execute(array('0', $us['block_user']));  

//DB::$the->prepare("UPDATE sel_users SET id_key=? WHERE chat=? ")->execute(array('0', $us['block_user'])); 

$curl->get('https://api.telegram.org/bot'.$token.'/sendMessage',array(
	'chat_id' => $us['block_user'],
	'text' => "🚫 Вы не произвели оплату в течение {$set_bot['block']} минут. Этот товар выставлен на продажу. 
Для того чтобы купить товар, выберите его снова",
	
	)); 
}
}	
	
if (strstr($message, "🔹")) 
{

// Берем цифру из запроса
$name_cat = preg_replace('#🔹#USi', '', $message);

 
// Берем информацию об категории из БД
$cat_id = DB::$the->query("SELECT id FROM `sel_category` WHERE `name` = '".$name_cat."' ");
$cat_id = $cat_id->fetch(PDO::FETCH_ASSOC);


	
// Узнаем, сколько подкатегориев в категории
$total = DB::$the->query("SELECT id FROM `sel_subcategory` where `id_cat` = '".$cat_id['id']."' ");
$total = $total->fetchAll();

// Узнаем, сколько не проданных ключей
$total2 = DB::$the->query("SELECT id FROM `sel_keys` where `id_cat` = '".$cat_id['id']."' and `sale` = '0' and `block` = '0'");
$total2 = $total2->fetchAll();

// Если нет подкатегорий
if(count($total) == 0 or count($total2) == 0) {
$curl->get('https://api.telegram.org/bot'.$token.'/sendMessage',array(
	'chat_id' => $chat,
	'text' => "⛔ Данная категория пуста!\n",
	)); 
exit;
}
else // Иначе
{	



$arr = array();	

$query = DB::$the->query("SELECT * FROM `sel_subcategory` where `id_cat` = '".$cat_id['id']."' order by `mesto` ");
while($cat = $query->fetch()) {
// Считаем количество ключей в подкатегории	
$total = DB::$the->query("SELECT id_subcat FROM `sel_keys` WHERE `id_subcat` = {$cat['id']} and `sale` = '0' and `block` = '0'");
$total = $total->fetchAll();

// Считаем количество ключей в подкатегории	
$total2 = DB::$the->query("SELECT id_subcat FROM `sel_keys` WHERE `id_subcat` = {$cat['id']} and `sale` = '0' and `block` = '1'");
$total2 = $total2->fetchAll();

if (count($total2) > 0) $free = ' | '.count($total2).'🕑'; else $free = '';

if(count($total) != 0){
$arr[] = array("🔸[".$cat['name']."] - ".$cat['amount']." руб (".count($total)." шт".$free.")");
	
}	
}
$arr[] = array("🔙 Назад");	


	$replyMarkup = array(
	'resize_keyboard' => true,
    'keyboard' => 
	$arr 
	
);
$menu = json_encode($replyMarkup);

// Отправлеям	
$curl->get('https://api.telegram.org/bot'.$token.'/sendMessage',array(
	'chat_id' => $chat,
	'text' => 'Выберите район и товар',
	'reply_markup' => $menu,	
	)); 
}
exit;
}	
if (strstr($message, "🔸")) 
{

preg_match_all("#\[(.*)\]#Uis", $message, $res);
$res= preg_replace('#\[#USi', '', $res[0][0]); 
$name_cat= preg_replace('#\]#USi', '', $res); 

// Берем информацию о подкатегории
$row = DB::$the->query("SELECT * FROM `sel_subcategory` WHERE `name` = '".$name_cat."' ");
$subcat = $row->fetch(PDO::FETCH_ASSOC);


// Проверяем наличие ключей
$total = DB::$the->query("SELECT id FROM `sel_keys` where `id_subcat` = '".$subcat['id']."' and `sale` = '0' and `block` = '0' ");
$total = $total->fetchAll();

if(count($total) == 0) { // Если пусто, вызываем ошибку

// Отправляем текст
$curl->get('https://api.telegram.org/bot'.$token.'/sendMessage',array(
	'chat_id' => $chat,
	'text' => '⛔ В данном районе нет доступных товаров!',
	));
}
else // Иначе выводим результат
{

$clear = DB::$the->query("SELECT block_user FROM `sel_keys` where `block_user` = '".$chat."' ");
$clear = $clear->fetchAll();

if(count($clear) != 0){
DB::$the->prepare("UPDATE sel_keys SET block=? WHERE block_user=? ")->execute(array("0", $chat)); 
DB::$the->prepare("UPDATE sel_keys SET block_time=? WHERE block_user=? ")->execute(array('0', $chat));
DB::$the->prepare("UPDATE sel_keys SET block_user=? WHERE block_user=? ")->execute(array('0', $chat));  
}

// Получаем информацию о ключе 
$key = DB::$the->query("SELECT id,code,id_subcat FROM `sel_keys` where `id_subcat` = '".$subcat['id']."' and `sale` = '0' and `block` = '0' order by rand() limit 1");
$key = $key->fetch(PDO::FETCH_ASSOC);


if($subcat['amount'] == $user['balans']){

// Записываем информацию о покупке в БД
$params = array('id_key' => $key['id'], 'code' => $key['code'], 'chat' => $chat, 'id_subcat' => $key['id_subcat'], 'time' => time() );   
$q = DB::$the->prepare("INSERT INTO `sel_orders` (id_key, code, chat, id_subcat, time) 
VALUES (:id_key, :code, :chat, :id_subcat, :time)");  
$q->execute($params);

// Пополняем баланс пользователя
$new_balans = $user['balans']-$subcat['amount'];
DB::$the->prepare("UPDATE sel_users SET balans=? WHERE chat=? ")->execute(array($new_balans, $chat)); 

DB::$the->prepare("UPDATE sel_keys SET sale=? WHERE id=? ")->execute(array("1", $key['id']));

// Отправляем текст пользователю
$curl->get('https://api.telegram.org/bot'.$token.'/sendMessage',array(
	'chat_id' => $chat,
	'text' => "✔ Вы успешно приобрели товар!",
	)); 

// Выводим в меню список категорий
$query = DB::$the->query("SELECT * FROM `sel_category` order by `mesto` ");
while($cat = $query->fetch()) {
$arr[] = array("🔹".$cat['name']."");	
}

$arr[] = array("📦 Заказы", "💰 Баланс");	

	$replyMarkup = array(
	'resize_keyboard' => true,
    'keyboard' => 
	$arr 
	
);
$menu = json_encode($replyMarkup);

// Отправляем текст пользователю
$curl->get('https://api.telegram.org/bot'.$token.'/sendMessage',array(
	'chat_id' => $chat,
	'text' => $key['code'],
	'reply_markup' => $menu,		
	)); 

}
else
{	

DB::$the->prepare("UPDATE sel_keys SET block=? WHERE id=? ")->execute(array("1", $key['id'])); 
DB::$the->prepare("UPDATE sel_keys SET block_user=? WHERE id=? ")->execute(array($chat, $key['id'])); 
DB::$the->prepare("UPDATE sel_keys SET block_time=? WHERE id=? ")->execute(array(time(), $key['id'])); 

DB::$the->prepare("UPDATE sel_users SET id_key=? WHERE chat=? ")->execute(array($key['id'], $chat)); 
	
$set_qiwi = DB::$the->query("SELECT number FROM `sel_set_qiwi` WHERE `active` = '1' ");
$set_qiwi = $set_qiwi->fetch(PDO::FETCH_ASSOC);	
	
DB::$the->prepare("UPDATE sel_users SET pay_number=? WHERE chat=? ")->execute(array($set_qiwi['number'], $chat)); 
	
	
$text = "

Вы приобретаете «{$subcat['name']}»
➖➖➖➖➖➖➖➖➖➖
Совершите платеж на QIWI в течение {$set_bot['block']} минут
➖➖➖➖➖➖➖➖➖➖
Кошелек: {$set_qiwi['number']}
Сумма: {$subcat['amount']} руб
Комментарий: {$key['id']}
➖➖➖➖➖➖➖➖➖➖
БЕЗ КОММЕНТАРИЯ ДЕНЬГИ НЕ ЗАЧИСЛЯЮТСЯ
";



$arr = array();	

$arr[] = array("✅ Я оплатил товар");	
$arr[] = array("🔙 Назад");	

	$replyMarkup = array(
	'resize_keyboard' => true,
    'keyboard' => 
	$arr 
	
);
$menu = json_encode($replyMarkup);

// Отправляем текст
$curl->get('https://api.telegram.org/bot'.$token.'/sendMessage',array(
	'chat_id' => $chat,
	'text' => $text,
	'reply_markup' => $menu,		
	)); 
}

}	
}	
exit;
?>