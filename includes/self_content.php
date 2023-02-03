<?php
if(!empty($_POST['rec_subm'])){//добавление новой записи в бд
	$author_id = $_POST['author_id'];
	$text = $_POST['text'];
	$date_create = date('U', time());
	
	$query = "INSERT INTO wall_recordings SET user_id='$user_id', author_id='$author_id', text='$text', date_create='$date_create'";
	mysqli_query($link, $query) or die(mysqli_error($link));
	header("Location: /main/$wall_user");
	die();
}

if(!empty($_POST['comm_subm'])){//добавление комментария к записи
	$author_id = $_POST['author_id'];
	$record_id = $_POST['record_id'];
	$text = $_POST['text'];
	$date_create = date('U', time());
	
	$query = "INSERT INTO wall_recordings_comments SET record_id='$record_id', author_id='$author_id', text='$text', date_create='$date_create'";
	mysqli_query($link, $query) or die(mysqli_error($link));
	header("Location: /main/$wall_user");
	die();
}

if(!empty($_POST['add_friend'])){//добавление в друзья
	$friend_id = $_POST['friend_id'];
	$query = "INSERT INTO friends SET user_id='$user_id', friend_id='$friend_id'";
	mysqli_query($link, $query) or die(mysqli_error($link));
	header("Location: /main/$wall_user");
	die();	
}

if(!empty($_POST['disline_friend'])){//отклонение друга
	$friend_id = $_POST['friend_id'];
	$query = "DELETE FROM friends WHERE user_id='$friend_id' AND friend_id='$user_id'";
	mysqli_query($link, $query) or die(mysqli_error($link));
	header("Location: /main/$wall_user");
	die();	
}

if(!empty($_POST['rec_del'])){//удаление записи
	$query = "DELETE FROM wall_recordings WHERE id='$_POST[rec_id]'";
	mysqli_query($link, $query) or die(mysqli_error($link));
	$query = "DELETE FROM wall_recordings_comments WHERE record_id='$_POST[rec_id]'";
	mysqli_query($link, $query) or die(mysqli_error($link));
	header("Location: /main/$wall_user");
	die();	
}

if(!empty($_POST['comm_del'])){//удаление комментария
	$query = "DELETE FROM wall_recordings_comments WHERE id='$_POST[comm_id]'";
	mysqli_query($link, $query) or die(mysqli_error($link));
	header("Location: /main/$wall_user");
	die();	
}

if(!empty($_POST['ban_subm'])){//бан пользователя
	$query = "UPDATE users SET status_id='4' WHERE id='$_POST[ban_id]'";
	mysqli_query($link, $query) or die(mysqli_error($link));
	header("Location: /main/$wall_user");
	die();		
}

if(!empty($_POST['del_friend_subm'])){//удалить друга
	$query = "DELETE FROM friends WHERE user_id='$user[id]' AND friend_id='$_POST[friend_id]'";
	mysqli_query($link, $query) or die(mysqli_error($link));
	$query = "DELETE FROM friends WHERE user_id='$_POST[friend_id]' AND friend_id='$user[id]'";
	mysqli_query($link, $query) or die(mysqli_error($link));
	header("Location: /main/$wall_user");
	die();	
}

$content .= " 
    <div>Имя - $user_profile[name] $user_profile[second_name]</div>
	<div>Дата рождения - $user_profile[date_birth]</div>
	<div>Персональная информация: $user_profile[personal_info]</div>
";//информация из профиля

$query= "
    SELECT  friends.friend_id, profile.name as friend_name, users.login as friend_login
	FROM friends
	LEFT JOIN profile ON friends.friend_id=profile.user_id
	LEFT JOIN users ON friends.friend_id=users.id
	WHERE friends.user_id='$user_id'
";//получаем пользователей которых залогиненый пользователь добавил в друзья
$result = mysqli_query($link, $query) or die(mysqli_error($link));
for($friends_data = []; $row = mysqli_fetch_assoc($result); $friends_data[] = $row);
$friends = [];
$requests = [];
foreach($friends_data as $friend_data){
	$query = "SELECT id FROM friends WHERE user_id='$friend_data[friend_id]' and friend_id='$user_id'";
	$result = mysqli_query($link, $query) or die(mysqli_error($link));
	$friend_conf = mysqli_fetch_assoc($result);
	
	if(!empty($friend_conf)){
		$friends[] = $friend_data;//аррей с друзьями
	} else {
		$requests[] = $friend_data;//аррей с запросами от пользователя, ожидающие подтверждения
	}
}

$query = "
	    SELECT friends.user_id, profile.name, users.login
	    FROM friends
		LEFT JOIN profile ON profile.user_id=friends.user_id
		LEFT JOIN users ON users.id=friends.user_id
		WHERE friends.friend_id='$user_id'
";//получаем пользователей которые добавили залогиненого пользователя в друзья
$result = mysqli_query($link, $query) or die(mysqli_error($link));
for($tofriends_data = []; $row = mysqli_fetch_assoc($result); $tofriends_data[] = $row);
$tofriends_req = [];
foreach($tofriends_data as $tofriend_data){
	$query = "SELECT id FROM friends WHERE user_id='$user_id' and friend_id='$tofriend_data[user_id]'";
    $result = mysqli_query($link, $query) or die(mysqli_error($link));
    $tofriend_conf = mysqli_fetch_assoc($result);
	
	if(empty($tofriend_conf)){
		$tofriends_req[] = $tofriend_data;//аррей с запросами в друзья пользователю
	}
}// и это работает

//друзья
$content .= '<h3>Друзья</h3>';
foreach($friends as $elem){
	$content .= '<div><a href="/main/'.$elem['friend_login'].'">'.$elem['friend_name'].'</a>
	<form action="" method="POST">
	<input type="hidden" name="friend_id" value="'.$elem['friend_id'].'">
	<input type="submit" name="del_friend_subm" value="Удалить друга">
	</form></div>';
}
if(empty($friends)){
	$content .= '<div>Друзей нет</div>';
}
if(!empty($requests)){
$content .= '<h3>Ожидают подтверждения</h3>';
foreach($requests as $elem){
	$content .= '<div><a href="/main/'.$elem['friend_login'].'">'.$elem['friend_name'].'</a></div>';
}
}
if(!empty($tofriends_req)){
$content .= '<h3>Запросы в друзья</h3>';//запросы в друзья с подтверждением или отклонением!!!
foreach($tofriends_req as $elem){
	$content .= '<div><a href="/main/'.$elem['login'].'">'.$elem['name'].'</a></div>';
	$content .= '
        <form action="" method="POST">
	    <input type="hidden" name="friend_id" value="'.$elem['user_id'].'">
	    <input type="submit" name="add_friend" value="Добавить">
	    <input type="submit" name="disline_friend" value="Отклонить">
	    </form>
    ';
}
}

$content .= '<p><a href="/messages">Личные сообщения</a></p>';//личные сообщения

$query = "
    SELECT wall_recordings.id, wall_recordings.text, wall_recordings.date_create, profile.name as author, users.status_id as author_status_id, users.id as author_id
	FROM wall_recordings
	LEFT JOIN profile ON profile.user_id=wall_recordings.author_id
	LEFT JOIN users ON users.id=wall_recordings.author_id
	WHERE wall_recordings.user_id='$user_id' AND users.status_id!='4'
";//Записи на стене 
$result = mysqli_query($link, $query) or die(mysqli_error($link));
for($wall_recs = []; $row = mysqli_fetch_assoc($result); $wall_recs[] = $row);

$content .= '<h3>Записи на стене</h3>';
foreach($wall_recs as $rec){
	$rec_date = date('H:i d-n-y',$rec['date_create']);
	$content .= '<p><div>Запись от '.$rec['author'].' в '.$rec_date;
	$content .= '
	    <form action="" method="POST">
		<input type="hidden" name="rec_id" value="'.$rec['id'].'">
		<input type="submit" name="rec_del" value="Удалить запись">
		</form>
	';//удалить запись
    if(($_SESSION['status'] === 'admin' or $_SESSION['status'] === 'moderator') and $rec['author_id'] !== $_SESSION['id']){
		$content .= '
		    <form action="" method="POST">
			<input type="hidden" name="ban_id" value="'.$rec['author_id'].'">
			<input type="submit" name="ban_subm" value="Забанить">
			</form>
		';//бан автора
	}	
	$content .= '</div><div>'.$rec['text'].'</div></p>';
	
	$content .= '<div>Комментарии:</div>';
	
	$id = $rec['id'];
	$query = "
	    SELECT wall_recordings_comments.id, wall_recordings_comments.text, wall_recordings_comments.date_create, profile.name as author, users.status_id as author_status_id, users.id as author_id
        FROM wall_recordings_comments
        LEFT JOIN profile ON profile.user_id=wall_recordings_comments.author_id
		LEFT JOIN users ON users.id=wall_recordings_comments.author_id
		WHERE wall_recordings_comments.record_id='$id' AND users.status_id!='4'
		";//комментарии к записи
	
	$result = mysqli_query($link, $query) or die(mysqli_error($link));
 	for($rec_comms = []; $row = mysqli_fetch_assoc($result); $rec_comms[] = $row);
	
	$i = 1;
	foreach($rec_comms as $comm){
		$content .="<p><div>".$i++.": $comm[author] в ".date('H:i d-n-y', $comm['date_create']);
		$content .='
		    <form action="" method="POST">
			<input type="hidden" name="comm_id" value="'.$comm['id'].'">
			<input type="submit" name="comm_del" value="Удалить комментарий">
			</form>
		';//удаление комментария
	    if(($_SESSION['status'] === 'admin' or $_SESSION['status'] === 'moderator') and $comm['author_id'] !== $_SESSION['id']){
		$content .= '
		    <form action="" method="POST">
			<input type="hidden" name="ban_id" value="'.$comm['author_id'].'">
			<input type="submit" name="ban_subm" value="Забанить">
			</form>
		';//бан автора
	    }	
		$content .="</div><div>$comm[text]</div></p>";//комментарии к записи
	}
	$content .='
	    <form action="" method="POST">
		<input type="hidden" name="author_id" value="'.$_SESSION['id'].'">
		<input type="hidden" name="record_id" value="'.$id.'">
		<textarea name="text"></textarea>
		<input type="submit" name="comm_subm" value="Добавить комментарий">
		</form>
	';//Добавить комментарий
}
if(!empty($user_profile)){
$content .= '
    <form action="" method="POST">
	<input type="hidden" name="author_id" value="'.$_SESSION['id'].'">
	<textarea name="text"></textarea>
	<input type="submit" name="rec_subm" value="Добавить запись">
	</form>
';//Добавить запись
}

if($_SESSION['status'] === 'banned'){
	$content = "
	    <div>Имя - $user_profile[name] $user_profile[second_name]</div>
		<div>Ваш аккаунт заблокирован</div>
	";
}
?>