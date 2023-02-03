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

if(!empty($_POST['add_to_friends'])){//добавление в друзья
	$query = "INSERT INTO friends SET user_id='$_SESSION[id]', friend_id='$user_id'";
	mysqli_query($link, $query) or die(mysqli_error($link));
	header("Location: /main/$wall_user");
	die();	
}

if(!empty($_POST['rec_del_subm'])){//удаление записи
	$query = "DELETE FROM wall_recordings WHERE id='$_POST[rec_id]'";
	mysqli_query($link, $query) or die(mysqli_error($link));
	$query = "DELETE FROM wall_recordings_comments WHERE record_id='$_POST[rec_id]'";
	mysqli_query($link, $query) or die(mysqli_error($link));
	header("Location: /main/$wall_user");
	die();	
}

if(!empty($_POST['comm_del_subm'])){//удаление коментария
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

$query = "SELECT * FROM profile WHERE user_id='$user_id'";
$result = mysqli_query($link, $query) or die(mysqli_error($link));
$profile_data = mysqli_fetch_assoc($result);

if(!empty($profile_data)){
$content .= " 
    <div>Имя - $profile_data[name] $profile_data[second_name]</div>
	<div>Дата рождения - $profile_data[date_birth]</div>
	<div>Персональная информация: $profile_data[personal_info]</div>
";//информация из профиля
} else {
	$content .= 'Пользователь не заполнил профиль';
}

if(($_SESSION['status'] === 'admin' or $_SESSION['status'] === 'moderator') and $user['status_id'] !== '4'){
	$content .= '
	    <div><form action="" method="POST">
		<input type="hidden" name="ban_id" value="'.$user['id'].'">
		<input type="submit" name="ban_subm" value="Забанить">
		</form></div>
	';//забанить
}

if(!empty($profile_data)){
	$query = "SELECT id FROM friends WHERE user_id='$_SESSION[id]' AND friend_id='$user_id'";
	$result = mysqli_query($link, $query) or die(mysqli_error($link));
    $user_friend = mysqli_fetch_assoc($result);//если вы добавили пользователя в друзья
	
	$query = "SELECT id FROM friends WHERE user_id='$user_id' AND friend_id='$_SESSION[id]'";
	$result = mysqli_query($link, $query) or die(mysqli_error($link));
    $friend_user = mysqli_fetch_assoc($result);//если пользователь добавил вас в друзья

    if(!empty($user_friend) and !empty($friend_user)){
		$content .= '<p>Вы с пользователем друзья</p>';
	} elseif(!empty($user_friend) and empty($friend_user)){
		$content .= '<p>Вы добавили пользователя в друзья</p>';
	} else {
		if($_SESSION['status'] !== 'banned'){
		$content .= '
		    <form action="" method="POST">
			<p><input type="submit" name="add_to_friends" value="Добавить в друзья"></p>
			</form>
		';
		}
	}	
}

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
	if($_SESSION['id'] === $rec['author_id'] or $_SESSION['status'] === 'moderator' or $_SESSION['status'] === 'admin'){
		$content .= '
		    <form action="" method="POST">
			<input type="hidden" name="rec_id" value="'.$rec['id'].'">
			<input type="submit" name="rec_del_subm" value="Удалить запись">
			</form>
		';//удалить запись
	}
    if(($_SESSION['status'] === 'admin' or $_SESSION['status'] === 'moderator') and $rec['author_id'] !== $_SESSION['id']){
		$content .= '
		    <form action="" method="POST">
			<input type="hidden" name="ban_id" value="'.$rec['author_id'].'">
			<input type="submit" name="ban_subm" value="Забанить">
			</form>
		';//бан автора
	}	
	$content .= '</div><div>'.$rec['text'].'</div></p>';
	$content .= '<div>Комментарии</div>';
	
	$id = $rec['id'];//id записи
	$query = "
	    SELECT wall_recordings_comments.id, wall_recordings_comments.text, wall_recordings_comments.date_create, profile.name as author, users.status_id as author_status_id, users.id as author_id
        FROM wall_recordings_comments
		LEFT JOIN users ON users.id=wall_recordings_comments.author_id
        LEFT JOIN profile ON profile.user_id=wall_recordings_comments.author_id
		WHERE wall_recordings_comments.record_id='$id' AND users.status_id!='4'
		";//комментарии к записи
	
	$result = mysqli_query($link, $query) or die(mysqli_error($link));
 	for($rec_comms = []; $row = mysqli_fetch_assoc($result); $rec_comms[] = $row);
	
	$i = 1;
	foreach($rec_comms as $comm){
		$content .= '<p><div>'.$i++.': '.$comm['author'].' в '.date('H:i d-n-y', $comm['date_create']);
		if($_SESSION['id'] === $comm['author_id'] or $_SESSION['status'] === 'moderator' or $_SESSION['status'] === 'admin'){
			$content .= '
			    <form action="" method="POST">
				<input type="hidden" name="comm_id" value="'.$comm['id'].'">
				<input type="submit" name="comm_del_subm" value="Удалить комментарий">
				</form>
			';//удалить комментарий
		}
	    if(($_SESSION['status'] === 'admin' or $_SESSION['status'] === 'moderator') and $comm['author_id'] !== $_SESSION['id']){
		$content .= '
		    <form action="" method="POST">
			<input type="hidden" name="ban_id" value="'.$comm['author_id'].'">
			<input type="submit" name="ban_subm" value="Забанить">
			</form>
		';//бан автора
	}	
		$content .= '</div><div>'.$comm['text'].'</div></p>';//комментарии к записи
	}
	if($_SESSION['status'] !== 'banned'){
	$content .='
	    <form action="" method="POST">
		<input type="hidden" name="author_id" value="'.$_SESSION['id'].'">
		<input type="hidden" name="record_id" value="'.$id.'">
		<textarea name="text"></textarea>
		<input type="submit" name="comm_subm" value="Добавить комментарий">
		</form>
	';//Добавить комментарий
	}
}

if(!empty($profile_data)){
	if($_SESSION['status'] !== 'banned'){
        $content .= '
            <form action="" method="POST">
	        <input type="hidden" name="author_id" value="'.$_SESSION['id'].'">
	        <textarea name="text"></textarea>
	        <input type="submit" name="rec_subm" value="Добавить запись">
	        </form>
        ';//Добавить запись
	}
}
?>