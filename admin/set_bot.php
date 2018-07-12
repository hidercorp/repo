<?php
ob_start();
require '../style/head.php';
require '../classes/My_Class.php';
require '../classes/PDO.php';

if (!isset($_COOKIE['secretkey']) or $_COOKIE['secretkey'] != $secretkey) {
    header("Location: /admin");
    exit;
}

$My_Class->title("Настройки бота");

$row = DB::$the->query("SELECT * FROM `sel_set_bot` ");
$set = $row->fetch(PDO::FETCH_ASSOC);

if(isset($_GET['ok']) and isset($_POST['submit'])) {

    if($_POST['token'] != "" and $_POST['verification'] != "" and $_POST['block'] != "" and $_POST['on_off'] != "") {
        $token=$_POST['token'];
        $verification=$_POST['verification'];
        $block=$_POST['block'];
        $proxy=$_POST['proxy'];
        $proxy_login=$_POST['proxy_login'];
        $proxy_pass=$_POST['proxy_pass'];
        $url=$_POST['url'];
        $on_off=$_POST['on_off'];
        $title_p=$_POST['title_page'];
        $text_p=$_POST['text_page'];

        DB::$the->prepare("UPDATE sel_set_bot SET token=? ")->execute(array("$token"));
        DB::$the->prepare("UPDATE sel_set_bot SET verification=? ")->execute(array("$verification"));
        DB::$the->prepare("UPDATE sel_set_bot SET block=? ")->execute(array("$block"));
        DB::$the->prepare("UPDATE sel_set_bot SET proxy=? ")->execute(array("$proxy"));
        DB::$the->prepare("UPDATE sel_set_bot SET proxy_login=? ")->execute(array("$proxy_login"));
        DB::$the->prepare("UPDATE sel_set_bot SET proxy_pass=? ")->execute(array("$proxy_pass"));
        DB::$the->prepare("UPDATE sel_set_bot SET url=? ")->execute(array("$url"));
        DB::$the->prepare("UPDATE sel_set_bot SET on_off=? ")->execute(array("$on_off"));
        DB::$the->prepare("UPDATE sel_set_bot SET title_page=? ")->execute(array("$title_p"));
        DB::$the->prepare("UPDATE sel_set_bot SET text_page=? ")->execute(array("$text_p"));
        header("Location: ?");
    }
    else
    {
        ?>
        <div class="alert alert-danger"> Пустые данные!</div>
        <?
    }
}

?>
    <script type="text/javascript">
        $(function() {
            $(".btn").click(function(){
                $(this).button('loading').delay(3000).queue(function() {
                    $(this).button('reset');
                    $(this).dequeue();
                });
            });
        });
    </script>

    <ol class="breadcrumb">
        <li><a href="/admin">Админ-панель</a></li>
        <li class="active">Настройки бота</li>
    </ol>

    <form method="POST" action="?ok"><div class="form-group col-sm-10">
            <div class="input-group input-group-lg">
                <span class="input-group-addon">TOKEN</span>
                <input type="text" class="form-control" name="token" value="<?=$set['token'];?>">
            </div><br />
            <div class="input-group input-group-lg">
                <span class="input-group-addon">Антифлуд (проверка оплаты)</span>
                <input type="text" class="form-control" name="verification" value="<?=$set['verification'];?>">
                <span class="input-group-addon">сек.</span>
            </div><br />
            <div class="input-group input-group-lg">
                <span class="input-group-addon">Время для оплаты товара</span>
                <input type="text" class="form-control" name="block" value="<?=$set['block'];?>">
                <span class="input-group-addon">мин.</span>
            </div><br />
            <div class="input-group input-group-lg">
                <span class="input-group-addon">IP прокси</span>
                <input type="text" class="form-control" name="proxy" value="<?=$set['proxy'];?>">
            </div><br />
            <div class="input-group input-group-lg">
                <span class="input-group-addon">Логин прокси</span>
                <input type="text" class="form-control" name="proxy_login" value="<?=$set['proxy_login'];?>">
            </div><br />
            <div class="input-group input-group-lg">
                <span class="input-group-addon">Пароль прокси</span>
                <input type="text" class="form-control" name="proxy_pass" value="<?=$set['proxy_pass'];?>">
            </div><br />
            <div class="input-group input-group-lg">
                <span class="input-group-addon">Адрес сайта (с http://)</span>
                <input type="text" class="form-control" name="url" value="<?=$set['url'];?>">
            </div><br />
            <div class="input-group input-group-lg">
                <span class="input-group-addon">Заголовок доп. кнопки</span>
                <input type="text" class="form-control" name="title_page" value="<?=$set['title_page'];?>">
            </div><br />
            <textarea  name="text_page" class="form-control" cols="50" rows="5" placeholder="Содержание доп. кнопки"><?=htmlspecialchars($set['text_page']); ?></textarea>
            <br />
            Состояние бота
            <label class="radio">
                <input type="radio" name="on_off" class="form-control" value="on" <?if($set['on_off']=='on')echo'checked';?>>
                Включен
            </label>
            <hr>
            <label class="radio">
                <input type="radio" name="on_off" class="form-control" value="off" <?if($set['on_off']=='off')echo'checked';?>>
                Отключен
            </label>
            <hr>
            <button type="submit" name="submit" data-loading-text="Сохраняю" class="btn btn-danger btn-lg btn-block">Сохранить</button></form>
    </div>
<?

$My_Class->foot();
?>