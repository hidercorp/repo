<?
require 'classes/Curl.php';
require 'classes/PDO.php';

$curl = new Curl();

// Получаем информацию из БД о настройках бота
$set_bot = DB::$the->query("SELECT token FROM `sel_set_bot` ");
$set_bot = $set_bot->fetch(PDO::FETCH_ASSOC);
$token		= $set_bot['token']; // токен бота

$chat = $argv[1];
$message = base64_decode($argv[2]);

$user = DB::$the->query("SELECT ban,balans FROM `sel_users` WHERE `chat` = {$chat} ");
$user = $user->fetch(PDO::FETCH_ASSOC);
	
$res= preg_replace('#/key#USi', '', $message);	
preg_match("/^([^_]*)_(.*)$/", $res, $res);
// Проверяем сколько у нас ключей
$total = DB::$the->query("SELECT id FROM `sel_keys` where `id_cat` = {$res[1]} and `id_subcat` = {$res[2]} and `sale` = '0' ");
$total = $total->fetchAll();

if(count($total) == 0) { // Если пусто, вызываем ошибку
$text .= "⛔ Ошибка! Нет доступных ключей!\n\n";	
}
else
{
// Получаем информацию о субкатегории	
$subcat = DB::$the->query("SELECT name,amount FROM `sel_subcategory` where `id` = {$res[2]} ");
$subcat = $subcat->fetch(PDO::FETCH_ASSOC);
// Если баланс пользователя меньше стоимости
if($user['balans'] < $subcat['amount'])
{ // Ошибка
$text .= "⛔ Ошибка! Недостаточно средств!\n\n";		
$text .= "/payment - Пополнить счет\n\n";		
}	
else // Иначе
{
// Получаем информацию о ключе 
$key = DB::$the->query("SELECT id,code FROM `sel_keys` where `id_cat` = {$res[1]} and `id_subcat` = {$res[2]} and `sale` = '0' order by rand() limit 1");
$key = $key->fetch(PDO::FETCH_ASSOC);
// Отмечаем ключ как проданный	
DB::$the->prepare("UPDATE sel_keys SET sale=? WHERE id=? ")->execute(array("1", $key['id'])); 
// Отнимаем сумму от баланса пользователя
$balans_minus = $user['balans']-$subcat['amount'];
DB::$the->prepare("UPDATE sel_users SET balans=? WHERE chat=? ")->execute(array($balans_minus, $chat)); 
// Записываем информацию о платеже в БД
$params = array('id_key' => $key['id'], 'code' => $key['code'], 'chat' => $chat, 'id_subcat' => $res[2], 'time' => time() );   
$q = DB::$the->prepare("INSERT INTO `sel_orders` (id_key, code, chat, id_subcat, time) 
VALUES (:id_key, :code, :chat, :id_subcat, :time)");  
$q->execute($params);	

$text .= "✔ Вы успешно приобрели ключ 📬 {$subcat['name']}\n\n";		
$text .= "Пожалуйста, сохраните свой ключ: \n\n{$key['code']}\n\n";		
}
}
$text .= "/menu - Главное меню";
// Отправляем
$curl->get('https://api.telegram.org/bot'.$token.'/sendMessage',array(
	'chat_id' => $chat,
	'text' => $text,
	)); 
exit;
?>