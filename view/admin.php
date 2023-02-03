<?php
if(!empty($_POST['change_status_sub'])){//изменение статуса
	$query = "UPDATE users SET status_id='$_POST[status_id]' WHERE id='$_POST[id]'";
	mysqli_query($link, $query) or die(mysqli_error($link));
	header ('Location: /admin');
    die();	
}

if(!empty($_POST['delete_user_sub'])){//удаление пользователя
	$query = "DELETE FROM users WHERE id='$_POST[id]'";
	mysqli_query($link, $query) or die(mysqli_error($link));
	$query = "DELETE FROM profile WHERE user_id='$_POST[id]'";
	mysqli_query($link, $query) or die(mysqli_error($link));//возможно придется удалить записи и комментарии
	header ('Location: /admin');
    die();	
}

if($_SESSION['status'] !== 'admin'){
	header('Location: /');
	die();
}
$query = "
    SELECT users.id, users.login, users.date_reg, profile.name, profile.second_name, profile.email, profile.date_birth, statuses.name as status
	FROM users
	LEFT JOIN statuses ON statuses.id=users.status_id
	LEFT JOIN profile ON profile.user_id=users.id
";
$result = mysqli_query($link, $query) or die(mysqli_error($link));
for($data = []; $row = mysqli_fetch_assoc($result); $data[] = $row);
	
$query = "SELECT * FROM statuses";//Получаем статусы
$result = mysqli_query($link, $query) or die(mysqli_error($link));
for($statuses = []; $row = mysqli_fetch_assoc($result); $statuses[] = $row);	

$content = '
    <table>
	<caption>Пользователи</caption>
    <tr>
	    <th>Логин</th>
		<th>ID</th>
		<th>Имя</th>
		<th>Фамилия</th>
		<th>Емаил</th>
		<th>Дата рождения</th>
		<th>Дата регистрации</th>
		<th>Статус</th>
		<th></th>
        <th></th>
    </tr>	
';
foreach($data as $elem){
	$content .= '
	    <tr>
		    <td><a href="/main/'.$elem['login'].'">'.$elem['login'].'</a></td>
			<td>'.$elem['id'].'</td>
			<td>'.$elem['name'].'</td>
			<td>'.$elem['second_name'].'</td>
			<td>'.$elem['email'].'</td>
			<td>'.$elem['date_birth'].'</td>
			<td>'.$elem['date_reg'].'</td>
			<td>'.$elem['status'].'</td>
			<td><form action="" method="POST">
			    <input type="hidden" name="id" value="'.$elem['id'].'">
				<select name="status_id">';
				    foreach($statuses as $status){
						if ($status['name'] !== $elem['status']){
							$content .= '<option value="'.$status['id'].'">'.$status['name'].'</option>';
						}
					}
$content .=	    '</select>
				<input type="submit" name="change_status_sub" value="Изменить статус">
			</form></td>
			<td><form>
			    <input type="hidden" name="id" value="'.$elem['id'].'">
				<input type="submit" name="delete_user_sub" value="Удалить пользователя">
			</form></td>
		</tr>
	';
}
$content .= '</table>';
$footer = '<p><a href="/main/'.$_SESSION['login'].'">Мой профиль</a></p>';

$page = [
    'title' => 'Админка',
	'header' => '<h2>Админка</h2>',
	'content' => $content,
	'footer' => $footer
];
return $page;
?>