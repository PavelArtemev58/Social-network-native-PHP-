<?php
if(!empty($_POST['submit'])){
	$login = $_POST['login'];
	
	$query = "
	    SELECT users.id, users.login, users.password, statuses.name as status FROM users
		LEFT JOIN statuses ON statuses.id=users.status_id
		WHERE users.login='$login'
	";
	$result = mysqli_query($link, $query) or die(mysqli_error($link));
	$user = mysqli_fetch_assoc($result);
	
	if(!empty($user)){
		$hash = $user['password'];
		if(password_verify($_POST['password'], $hash)){
			$_SESSION['auth'] = true;
			$_SESSION['login'] = $login;
			$_SESSION['id'] = $user['id'];
			$_SESSION['status'] = $user['status'];
			header ("Location: /main/$_SESSION[login]"); //переход на стену
			die();
		} else {
			$_SESSION['flash'] = 'Неверный логин или пароль';
		}
	} else {
		$_SESSION['flash'] = 'Неверный логин или пароль';
	}
}
$content = '
	<form action="" method="POST">
	    <div>Логин<input name="login"></div>
		<div>Пароль<input type="password" name="password"></div>
		<div><input type="submit" name="submit"></div>
	</form>';
$content .= "<div>$_SESSION[flash]<div>";
unset($_SESSION['flash']);	


$page = [
    'title' => 'Авторизация',
	'header' => '<h2>Авторизация</h2>',
	'content' => $content,
	'footer' => '<a href="/registration">Регистрация</a>'
];
return $page;
?>