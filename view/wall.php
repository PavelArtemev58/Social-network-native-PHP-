<?php
$wall_user = $params['wallSlug'];

$query = "SELECT id, status_id FROM users WHERE login='$wall_user'";
$result = mysqli_query($link, $query) or die(mysqli_error($link));
$user = mysqli_fetch_assoc($result);
$user_id = $user['id'];//ID страницы 
$user_status = $user['status_id'];//статус страницы

include 'includes/prof_filling.php';//Заполнение профиля

if(!empty($user) and $user_status !== '4' or $_SESSION['login'] === $wall_user){
    if ($wall_user === $_SESSION['login']){
    	$title = 'Ваша стена';
		$header = '<p><a href="/search">Поиск пользователей</a></p>';
    	$header .= '<h2>Ваша стена</h2>';
    	$self = true;//Флаг если своя страгица
    } else {
    	$title = "Стена пользователя";
		$header = '<p><a href="/search">Поиск пользователей</a></p>';
    	$header .= "<h2>Стена пользователя $wall_user</h2>";
		$other = true;//Флаг если чужая страница
		
    }
} else {
	$title = 'Пользователь не существует';
	$header = '<h2>Пользователь не существует или заблокирован</h2>';
	$content = '<p><a href="/main/'.$_SESSION['login'].'">Мой профиль</a></p>';
	$footer = '<p><a href="/logout">Разлогиниться</a></p>';
	if($_SESSION['status'] === 'admin'){
	    $footer .= '<p><a href="/admin">Админка</a></p>';
    }

    $page = [
        'title' => $title,
	    'header' => $header,
	    'content' => $content,
	    'footer' => $footer
    ];
    return $page;
	die();
}

if(!empty($self)){//Свой профиль
	$query = "SELECT * FROM profile WHERE user_id='$user_id'";
	$result = mysqli_query($link, $query) or die(mysqli_error($link));
    $user_profile = mysqli_fetch_assoc($result);
	
	if(empty($user_profile)){
		$content = '</p>Заполните профиль прежде чем добавлять друзей и отправлять сообщения<p>
		            <form action="" method="POST">
					    <div>Имя<input name="name"></div>
						<div>Фамилия<input name="second_name"></div>
						<div>Емаил<input name="email"></div>
						<div>Дата рождения<input  type="date" name="date_birth"></div>
						<div>Персональная информация<textarea name="personal_info"></textarea></div>
						<div><input type="submit" name="prof_subm"></div>
					</form>';
	} else {
		$prof = true;//флаг при заполненном профиле
		$content = "<div>$_SESSION[prof_fill_flash]</div>";
		unset($_SESSION['prof_fill_flash']);
	}
	if(!empty($prof)){
		include 'includes/self_content.php';
	}
} 

if(!empty($other)){//Чужой профиль
	include 'includes/other_content.php';
	$content .= '<p><a href="/main/'.$_SESSION['login'].'">Мой профиль</a></p>';	
}

$footer = '<p><a href="/logout">Разлогиниться</a></p>';
if($_SESSION['status'] === 'admin'){
	$footer .= '<p><a href="/admin">Админка</a></p>';
}

$page = [
    'title' => $title,
	'header' => $header,
	'content' => $content,
	'footer' => $footer
];
return $page;
?>