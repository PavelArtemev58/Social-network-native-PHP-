<?php
if(isset($_POST['submit'])){
	if(!empty($_POST['login']) and !empty($_POST['password']) and !empty($_POST['confirm'])){
		if(strlen($_POST['login'])>3 and strlen($_POST['ligin'])<11){
			if(strlen($_POST['password'])>5 and strlen($_POST['password'])<21){
				if(!preg_match('#[^a-zA-Z0-9]+#', $_POST['login'])){
					if($_POST['password'] === $_POST['confirm']){
						$login = $_POST['login'];
						$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
						$email = $_POST['email'];
						$date_reg = date('Y-m-d', time());
							
						$query = "SELECT id FROM users WHERE login='$login'";
						$user = mysqli_fetch_assoc(mysqli_query($link, $query));
							
						if(empty($user)){
							$query = "
							    INSERT INTO users
								SET login='$login', password='$password', date_reg='$date_reg', status_id='1'  
							";
							mysqli_query($link, $query) or die(mysqli_error($link));
								
							$_SESSION['id'] = mysqli_insert_id($link);
							$_SESSION['login'] = $login;
							$_SESSION['status'] = 'user';
							$_SESSION['auth'] = true;
								
							header('Location: /main/$_SESSION[login]');//на стену
							die();
						} else {
							$_SESSION['regmsg'] = 'Логин занят';
						}
					} else {
						$_SESSION['regmsg'] = 'Пароль введен неверно';
					}
				} else {
					$_SESSION['regmsg'] = 'Логин должен содержать только латинскае буквы и цифры';
				}
			} else {
				$_SESSION['regmsg'] = 'Пароль должен быть длиной от 6 до 12 символов';
			}
		} else {
			$_SESSION['regmsg'] = 'Логин должен быть длиной от 4 до 10 символов';
		}
	} else {
		$_SESSION['regmsg'] = 'Введите ваши логин и пароль';
	}
}

$content = '
 	<form action="" method="POST">
	    <div>Логин<input name="login"></div>
		<div>Пароль<input type="password" name="password"></div>
		<div>Подтверждение пароля<input type="password" name="confirm"></div>
		<div><input type="submit" name="submit"></div>
	</form>
	';
$content .= $_SESSION['regmsg'];
unset($_SESSION['regmsg']);

$page = [
    'title' => 'Регистрация',
	'header' => '<h2>Регистрация</h2>',
	'content' => $content,
	'footer' => '<p><a href="/auth">Авторизация</a></p>'
];
return $page;
?>