<?php
if(!empty($_POST['prof_subm'])){
	$name = $_POST['name'];
	$second_name = $_POST['second_name'];
	$email = $_POST['email'];
	$date_birth = $_POST['date_birth'];
	$personal_info = $_POST['personal_info'];
	//ДОБАВИТЬ ВРИФИКАЦИЮ!!!
	$query = "INSERT INTO profile SET user_id='$user_id', name='$name', second_name='$second_name', email='$email', date_birth='$date_birth', personal_info='$personal_info'";
    mysqli_query($link, $query) or die(mysqli_error($link));
	$_SESSION['prof_fill_flash'] = 'Профиль успешно заполнен';
	header('Location: '.$_SESSION['login'].'');
	die();
}
?>