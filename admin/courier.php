<?

ob_start();
require '../style/head.php';
require '../classes/My_Class.php';
require '../classes/PDO.php';

if (isset($_COOKIE['secretkey']) or $_COOKIE['secretkey'] == $secretkey) {unset($_SESSION['courier']);};

if ($_SESSION['courier'] == 'ok') {

    header("Location: category.php");
} 

    $courier = DB::$the->query("SELECT * FROM `sel_couriers` WHERE `login` = '".$_POST['login']."' && password='".$_POST['password']."'");
    $courier = $courier->fetchAll(PDO::FETCH_ASSOC);


    if(count($courier) == 1) {

        unset($_SESSION['login']);
        $_SESSION['courier'] = 'ok';
        $_SESSION['login'] = $_POST['login'];

        header("Location: category.php");

    } else {

if(isset($_POST['auth'])) {
        $error = "Неверный логин или пароль";
}

    }

?>
<? if (!isset($_COOKIE['secretkey']) or $_COOKIE['secretkey'] != $secretkey): ?>

<head>
    <title>
        Вход
    </title>
</head>
<br>
    <div class="row">
        <div class="col-md-4 col-md-offset-4">
                    <form method="POST">
                        <fieldset>
                            <div class="form-group">
                                <input class="form-control" placeholder="Логин" name="login" type="text" required="required" autofocus>
                            </div>
                            <div class="form-group">
                                <input class="form-control" placeholder="Пароль" name="password" type="password" value="" required="required">
                            </div>
                            <input class="btn btn-success btn-block" type="submit" name="auth" value="Войти" />
                        </fieldset>
                    </form>
                </div>
            </div>

<center><h3><span class="label label-danger"><?=$error;?></span></h3></center>

<?endif?>

<? if (isset($_COOKIE['secretkey']) or $_COOKIE['secretkey'] == $secretkey): ?>


    <?

    $My_Class->title("Пользователи");

    if (!isset($_COOKIE['secretkey']) or $_COOKIE['secretkey'] != $secretkey) {
        header("Location: /admin");
        exit;
    }

    if(isset($_GET['cmd'])){$cmd = htmlspecialchars($_GET['cmd']);}else{$cmd = '0';}
    if(isset($_GET['user'])){$user = abs(intval($_GET['user']));}else{$user = '0';}

    ?>

    <?

    switch ($cmd){
        case 'edit':

            ?>
            <ol class="breadcrumb">
                <li><a href="/admin">Админ-панель</a></li>
                <li><a href="users.php">Пользователи</a></li>
                <li class="active">Редактирование</li>
            </ol>

            <?
            if(isset($_POST['submit'])) {

                if(isset($_GET['ok'])) {

                    if($_POST['ban']=='on') { $ban = '1'; }
                    else { $ban = '0'; }


                    DB::$the->prepare("UPDATE sel_users SET ban=? WHERE chat=? ")->execute(array($ban, intval($_GET['chat'])));

                }
                else
                {
                    ?>
                    <div class="alert alert-danger"> Пустые данные!</div>
                    <?
                }
            }
            ?>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th  style="text-align:center;">№</th>
                        <th  style="text-align:center;">Никнейм</th>
                        <th  style="text-align:center;">Имя и Фамилия</th>
                        <th  style="text-align:center;">Бан</th>
                        <th  style="text-align:center;">chat</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?

                    $row = DB::$the->query("SELECT * FROM `sel_users` WHERE `chat` = '".intval($_GET['chat'])."'");
                    $user = $row->fetch(PDO::FETCH_ASSOC);

                    if($user['ban']==0) { $ban = 'Нет';	}
                    else { $ban = 'Да'; }
                    ?>
                    <tr>
                        <td  align="center"><?=$user['id'];?></td>
                        <td  align="center"><?=$user['username'];?></td>
                        <td  align="center"><?=$user['first_name'];?> <?=$user['last_name'];?></td>
                        <td  align="center"><?=$ban;?></td>
                        <td  align="center"><?=$user['chat'];?></td>
                    </tr>

                    </tbody>
                </table>
            </div>

            <form method="POST" action="?cmd=edit&chat=<?=intval($_GET['chat']);?>&ok">
                <div class="form-group col-sm-8">

                    <hr>
                    <label><span class="glyphicon glyphicon-lock"></span>
                        <input name="ban" type="checkbox" <?if($user['ban']=='1')echo'checked';?>>
                        Забанен
                    </label>
                    <hr>

                    <button type="submit" name="submit" class="btn btn-danger btn-lg btn-block" data-loading-text="Изменяю">Изменить</button></form>
            </div>

            <?
            break;

        default:

            ?>
            <ol class="breadcrumb">
                <li><a href="/admin">Админ-панель</a></li>
                <li class="active">Курьеры</li>
            </ol>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th  style="text-align:center;">№</th>
                        <th  style="text-align:center;">Логин</th>
                        <th  style="text-align:center;">Пароль</th>
                        <th  style="text-align:center;">Адреса</th>
                        <th  style="text-align:center;">Удаление</th>
                    </tr>
                    </thead>
                    <tbody>

                    <?

                    if(isset($_POST['new_user'])) {

                        DB::$the->query("INSERT INTO sel_couriers SET login='". $_POST['login']."', password='".$_POST['password']."'");
                    }

                    ?>

                    <?

                    $total = DB::$the->query("SELECT * FROM `sel_couriers` ");
                    $total = $total->fetchAll();
                    $max = 50;
                    $pages = $My_Class->k_page(count($total),$max);
                    $page = $My_Class->page($pages);
                    $start=($max*$page)-$max;




                    if(isset($_GET['delete'])){

                        $login = htmlspecialchars($_GET['delete']);

                        DB::$the->exec("DELETE FROM sel_couriers WHERE login='$login'");
                        header('Location: ./courier.php');

                    }

                    $all = 0;

                    $query = DB::$the->query("SELECT * FROM `sel_couriers`");
                    while($user = $query->fetch()) {


                       # if($user['ban']==0) { $ban = 'Нет';	}
                       # else { $ban = 'Да'; }


                        $key = DB::$the->query("SELECT * FROM sel_keys WHERE role='{$user['login']}'");
                        $allCount = $key->rowCount();
                        $key2 = DB::$the->query("SELECT * FROM sel_keys WHERE role='{$user['login']}' AND sale='0'");
                        $noCount = $key2->rowCount();
                        $key3 = DB::$the->query("SELECT * FROM sel_keys WHERE role='{$user['login']}' AND sale='1'");
                        $okCount = $key3->rowCount();
                        ?>

                        <tr>
                            <td  align="center"><?=$user['id'];?></td>
                            <td  align="center"><?=$user['login'];?></td>
                            <td  align="center"><?=$user['password'];?></td>
                            <td  align="center">Всего: <b><?=$allCount;?></b> в продаже: <b><?=$noCount;?></b> продано: <b><?=$okCount;?></b></td>
                            <td  align="center"><a href="?delete=<?=$user['login'];?>">Удалить</a></td>
                        </tr>

                        <?


                    }

                    ?>
                    </tbody>
                </table>
            </div>

            <a href="javascript:void(0);" class="btn btn-primary" role="button" onclick="$('#delete').toggle();">Создать аккаунт</a>
            <div class="col-md-4 col-md-offset-4" >
                <form method="POST" id='delete' style='display:none;'>
                    <fieldset>
                        <div class="form-group">
                            <input class="form-control" placeholder="Логин" name="login" type="text" required="required" autofocus>
                        </div>
                        <div class="form-group">
                            <input class="form-control" placeholder="Пароль" name="password" type="password" value="" required="required">
                        </div>
                        <input class="btn btn-success btn-block" type="submit" name="new_user" value="Добавить" />
                    </fieldset>
                </form>
            </div>

            <br>
            <?

    }
    if ($pages>1) $My_Class->str('?',$pages,$page);

    $My_Class->foot();
    ?>


<?endif?>

