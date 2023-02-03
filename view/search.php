<?php
$title = 'Поиск';
$header = '<h2>Поиск пользователей</h2>';
$footer = '<p><a href="/main/'.$_SESSION['login'].'">Мой профиль</a></p>';

$query = "
    SELECT users.login, profile.name, profile.second_name
	FROM users
	LEFT JOIN profile ON users.id=profile.user_id
";
$result = mysqli_query($link, $query) or die(mysqli_error($link));
for($data = []; $row = mysqli_fetch_assoc($result); $data[] = $row);

$content = '';
foreach($data as $elem){
	$content .= '<div><a href="/main/'.$elem['login'].'">'.$elem['name'].' '.$elem['second_name'].'</a></div>';
}

$page = [
    'title' => $title,
	'header' => $header,
	'content' => $content,
	'footer' => $footer
];
return $page;
?>