<?php
SESSION_START();
require('connections/connect.php');

$url = $_SERVER['REQUEST_URI'];

if(!empty($_SESSION['auth'])){
	$route = '/main/(?<wallSlug>[a-z0-9_-]+)';//путь на стену
	if(preg_match("#$route#", $url, $params)){
		$flag = true;
		$page = include 'view/wall.php';
	}
	
	$route = '/logout';
	if(preg_match("#$route#", $url)){
		include 'view/logout.php';
		die();
	}
	
	$route = '/admin';
	if(preg_match("#$route#", $url)){
		$flag = true;
		$page = include 'view/admin.php';
	}
	
	$route = '/search';
	if(preg_match("#$route#", $url)){
		$flag = true;
		$page = include 'view/search.php';
	}
	
	$route = '/messages';
	if(preg_match("#$route#", $url)){
		$flag = true;
		$page = include 'view/messages.php';
	}
	
} else {
    $route = '/auth';
    if(preg_match("#$route#", $url)){
	    $flag = true;
	    $page = include 'view/auth.php';
    }

    $route = '/registration';
    if(preg_match("#$route#", $url)){
	    $flag = true;
	    $page = include 'view/registration.php';
    }	
}

if(empty($flag)){
	if(empty($_SESSION['auth'])){
		header('Location: /auth');
		die();
	} else {
		header("Location: /main/$_SESSION[login]");//главная страница или стена
		die();
	}
}
	
$layout = file_get_contents('layout.php');
$layout = str_replace('{{ title }}', $page['title'], $layout);
$layout = str_replace('{{ header }}', $page['header'], $layout);
$layout = str_replace('{{ content }}', $page['content'], $layout);
$layout = str_replace('{{ footer }}', $page['footer'], $layout);

echo $layout;
?>