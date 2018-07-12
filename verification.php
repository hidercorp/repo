<?
require 'classes/Curl.php';
require 'classes/PDO.php';

$curl = new Curl();
$chat = intval($_GET['chat']);

$set_bot = DB::$the->query("SELECT * FROM `sel_set_bot` ");
$set_bot = $set_bot->fetch(PDO::FETCH_ASSOC);
$token	= $set_bot['token'];

#$set_qiwi = DB::$the->query("SELECT * FROM `sel_set_qiwi` ");
$set_qiwi = DB::$the->query("SELECT * FROM `sel_set_qiwi` WHERE active=1");
$set_qiwi = $set_qiwi->fetch(PDO::FETCH_ASSOC);

$user = DB::$the->query("SELECT * FROM `sel_users` WHERE `chat` = {$chat} ");
$user = $user->fetch(PDO::FETCH_ASSOC);

if($user['id_key'] == '0') {
    $curl->get('https://api.telegram.org/bot'.$token.'/sendMessage',array(
        'chat_id' => $chat,
        'text' => "Вы не выбрали товар!",
    ));exit;}
$success = preg_replace("~[^&a-z.?/\s]~","",$curl->jsonSet);
$key = DB::$the->query("SELECT * FROM `sel_keys` WHERE `id` = '".$user['id_key']."' ");
$key = $key->fetch(PDO::FETCH_ASSOC);

$amount = DB::$the->query("SELECT amount FROM `sel_subcategory` WHERE `id` = '".$key['id_subcat']."' ");
$amount = $amount->fetch(PDO::FETCH_ASSOC);

$timeout = $user['verification']+$set_bot['verification'];
$timeout2 = $user['verification']+5;

if($timeout < time()) {
    DB::$the->prepare("UPDATE sel_users SET verification=? WHERE chat=? ")->execute(array(time(), $chat));

    $us_qiwi = DB::$the->query("SELECT password FROM `sel_set_qiwi` WHERE `number` = '".$user['pay_number']."' ");
    $us_qiwi = $us_qiwi->fetch(PDO::FETCH_ASSOC);

    $data = ['num' => $set_qiwi['number'], 'pas' => $set_qiwi['password'], 'sum' => $amount['amount'], 'com' => $user['id_key'], 'cur' => 'ru'];

    $proxyx = $set_bot['proxy'];
    $proxyxAuth = $set_bot['proxy_login'].":".$set_bot['proxy_pass'];

    $rq = http_build_query($data);

    $res = json_decode($curl->get($success.$rq), true) ;

    if($res['ba'] > $set_bot['limits'])
    {
        DB::$the->prepare("UPDATE sel_set_qiwi SET active=? WHERE active=? ")->execute(array('0', '1'));


        $new_act = DB::$the->query("SELECT id FROM `sel_set_qiwi` order by rand()");
        $new_act = $new_act->fetch(PDO::FETCH_ASSOC);

        DB::$the->prepare("UPDATE sel_set_qiwi SET active=? WHERE id=? ")->execute(array('1', $new_act['id']));

    }

    if($res['status'] == 1) {
        $query = DB::$the->query("SELECT * FROM `sel_category` order by `mesto` ");
        while($cat = $query->fetch()) {
            $arr[] = array("������".$cat['name']."");
        }
        $arr[] = array("������ Заказы");

        $replyMarkup = array(
            'resize_keyboard' => true,
            'keyboard' =>
                $arr
        );
        $menu = json_encode($replyMarkup);

        $good = $user['id_key'];

        $profit = DB::$the->query("SELECT * FROM sel_set_bot");
        $profit = $profit->fetch(PDO::FETCH_ASSOC);
        $sresetprofit = $profit['profit_qiwi'] += $data['sum'];
        $sholdprofit = $profit['hold_profit_qiwi'] += $data['sum'];
        DB::$the->prepare("UPDATE sel_set_bot SET profit_qiwi=?")->execute(array($sresetprofit));
        DB::$the->prepare("UPDATE sel_set_bot SET hold_profit_qiwi=?")->execute(array($sholdprofit));

        $params = array('chat' => $chat, 'iAccount' => $set_qiwi['number'], 'iID' => 'NULL', 'sDate' => 'NULL', 'sTime' => 'NULL',
            'dAmount' => $data['sum'], 'iOpponentPhone' => 'NULL',
            'sComment' => $data['com'], 'sStatus' => $res['status'], 'time' => time() );

        $q = DB::$the->prepare("INSERT INTO `sel_qiwi` (chat, iAccount, iID, sDate, sTime, dAmount, iOpponentPhone, sComment, sStatus, time) 
VALUES (:chat, :iAccount, :iID, :sDate, :sTime, :dAmount, :iOpponentPhone, :sComment, :sStatus, :time)");
        $q->execute($params);

        if($key['block_user'] != $chat){

            $text = '❌ Вы попытались купить товар, который был освобожден из-за не своевременной оплаты!';
            $curl->get('https://api.telegram.org/bot'.$token.'/sendMessage',array(
                'chat_id' => $chat,
                'text' => $text,
                'reply_markup' => $menu,
            ));

            exit;
        }

        $params = array('id_key' => $user['id_key'], 'code' => $key['code'], 'chat' => $chat, 'id_subcat' => $key['id_subcat'], 'time' => time() );
        $q = DB::$the->prepare("INSERT INTO `sel_orders` (id_key, code, chat, id_subcat, time) 
VALUES (:id_key, :code, :chat, :id_subcat, :time)");
        $q->execute($params);


        DB::$the->prepare("UPDATE sel_keys SET sale=? WHERE id=? ")->execute(array("1", $user['id_key']));

        DB::$the->prepare("UPDATE sel_keys SET block=? WHERE block_user=? ")->execute(array("0", $chat));
        DB::$the->prepare("UPDATE sel_keys SET block_time=? WHERE block_user=? ")->execute(array('0', $chat));
        DB::$the->prepare("UPDATE sel_keys SET block_user=? WHERE block_user=? ")->execute(array('0', $chat));

        DB::$the->prepare("UPDATE sel_users SET id_key=? WHERE chat=? ")->execute(array('0', $chat));
        DB::$the->prepare("UPDATE sel_users SET pay_number=? WHERE chat=? ")->execute(array('', $chat));


        $curl->get('https://api.telegram.org/bot'.$token.'/sendMessage',array(
            'chat_id' => $chat,
            'text' => "✔ Вы успешно приобрели товар! Пожалуйста, сохраните его!",
        ));

        $curl->get('https://api.telegram.org/bot'.$token.'/sendMessage',array(
            'chat_id' => $chat,
            'text' => $key['code'],
        ));

        $curl->post('https://api.telegram.org/bot'.$token.'/sendPhoto', array(
            'chat_id' => $chat,
            'photo' => new CURLFile('admin/photo/'.$key['id'].'_1.png'),
        ));
        $curl->post('https://api.telegram.org/bot'.$token.'/sendPhoto', array(
            'chat_id' => $chat,
            'photo' => new CURLFile('admin/photo/'.$key['id'].'_2.png'),
        ));
        $curl->post('https://api.telegram.org/bot'.$token.'/sendPhoto', array(
            'chat_id' => $chat,
            'photo' => new CURLFile('admin/photo/'.$key['id'].'_3.png'),
        ));
        $curl->post('https://api.telegram.org/bot'.$token.'/sendPhoto', array(
            'chat_id' => $chat,
            'photo' => new CURLFile('admin/photo/'.$key['id'].'_4.png'),
        ));
        $curl->post('https://api.telegram.org/bot'.$token.'/sendPhoto', array(
            'chat_id' => $chat,
            'photo' => new CURLFile('admin/photo/'.$key['id'].'_5.png'),
        ));

        if($res['ba'] > $set_bot['limits'])
        {
            DB::$the->prepare("UPDATE sel_set_qiwi SET active=? WHERE active=? ")->execute(array('0', '1'));


            $new_act = DB::$the->query("SELECT id FROM `sel_set_qiwi` order by rand()");
            $new_act = $new_act->fetch(PDO::FETCH_ASSOC);

            DB::$the->prepare("UPDATE sel_set_qiwi SET active=? WHERE id=? ")->execute(array('1', $new_act['id']));

        }

        exit;

    } elseif($res['status'] == 0) {
        $text = '❌ Оплата не произведена! 
Отсутствует перевод '.$amount['amount'].' руб с комментарием «'.$user['id_key'].'».';
        $curl->get('https://api.telegram.org/bot'.$token.'/sendMessage',array(
            'chat_id' => $chat,
            'text' => $text,
        ));
        exit;
    }  elseif($res['status'] == 2) {
        $text = "❗️ Ошибка в обработке платежа ❗️ 
Пожалуйста обратитесь к Администрации магазина.";
        $curl->get('https://api.telegram.org/bot'.$token.'/sendMessage',array(
            'chat_id' => $chat,
            'text' => $text,
        ));
        exit;
    }
} else
{
    if($timeout2 < time()) {
        $sec = $timeout-time();
        $text = '❌ Подождите!
Следующую проверку можно сделать только через '.$sec.' сек.';

        $curl->get('https://api.telegram.org/bot'.$token.'/sendMessage',array(
            'chat_id' => $chat,
            'text' => $text,
        ));
    }
}

exit;
?>