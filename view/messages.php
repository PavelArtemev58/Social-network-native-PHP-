<?php
if(!empty($_POST['send_msg_subm'])){//отправка сообщения
	$date_create = date('U', time());
	$query = "INSERT INTO pms SET from_id='$_POST[from_id]', to_id='$_POST[to_id]', text='$_POST[text]', date_create='$date_create'";
	mysqli_query($link, $query) or die(mysqli_error($link));
	header("Location: /messages?fid=$_POST[to_id]&flog=$_POST[flog]");
	die();
}

$title = 'Диалоги';
$header = '<h3>Диалоги с друзьями</h3>';
$contetn = '';
$footer = '<p><a href="/main/'.$_SESSION['login'].'">Мой профиль</a></p>';
if($_SESSION['status'] === 'admin'){
	$footer .= '<p><a href="/admin">Админка</a></p>';
}

if(empty($_GET)){
$query= "
    SELECT  friends.friend_id, profile.name as friend_name, profile.second_name as friend_second_name, users.login as friend_login
	FROM friends
	LEFT JOIN profile ON friends.friend_id=profile.user_id
	LEFT JOIN users ON friends.friend_id=users.id
	WHERE friends.user_id='$_SESSION[id]'
";//получаем пользователей которых залогиненый пользователь добавил в друзья
$result = mysqli_query($link, $query) or die(mysqli_error($link));
for($friends_data = []; $row = mysqli_fetch_assoc($result); $friends_data[] = $row);

$friends = [];
foreach($friends_data as $friend_data){
	$query = "SELECT id FROM friends WHERE user_id='$friend_data[friend_id]' and friend_id='$_SESSION[id]'";
	$result = mysqli_query($link, $query) or die(mysqli_error($link));
	$friend_conf = mysqli_fetch_assoc($result);
	
	if(!empty($friend_conf)){
		$friends[] = $friend_data;//аррей с друзьями
	}
}

foreach($friends as $elem){
	$content .= '<a href="/messages?fid='.$elem['friend_id'].'&flog='.$elem['friend_login'].'">'.$elem['friend_name'].' '.$elem['friend_second_name'].'</a>';
}
} else {
$query = "SELECT name, second_name FROM profile WHERE user_id='$_GET[fid]'";
$result = mysqli_query($link, $query) or die(mysqli_error($link));
$friend_info = mysqli_fetch_assoc($result);//получаем информацию о друге

$header = '<h3>Диалог с '.$friend_info['name'].' '.$friend_info['second_name'].'</h3>';

$query = "
    SELECT profile.name as from_name, pms.text, pms.date_create FROM pms
	LEFT JOIN profile ON profile.user_id=pms.from_id
	WHERE (from_id='$_SESSION[id]' AND to_id='$_GET[fid]') OR (from_id='$_GET[fid]' AND to_id='$_SESSION[id]')
";//получаем сообщения
$result = mysqli_query($link, $query) or die(mysqli_error($link));
for($messages = []; $row = mysqli_fetch_assoc($result); $messages[] = $row);
if(!empty($messages)){
	foreach($messages as $elem){
		$content .= "
		    <p><div>От $elem[from_name] в ".date('H:i d-n-y',$elem['date_create'])."</div>
			<div>$elem[text]</div></p>
		";
	}
} else {
	$content = '<div>Сообщений нет<br>Вы можете начать беседу</div>';
}
$content .= '
    <form action="" method="POST">
	<input type="hidden" name="from_id" value="'.$_SESSION['id'].'">
	<input type="hidden" name="to_id" value="'.$_GET['fid'].'">
	<input type="hidden" name="flog" value="'.$_GET['flog'].'">
	<div><textarea name="text">Введите сообщение</textarea>
	<input type="submit" name="send_msg_subm" value="Отправить сообщение"></div>
	</form>
';
}

$page = [
    'title' => $title,
	'header' => $header,
	'content' => $content,
	'footer' => $footer
];
return $page;
?>