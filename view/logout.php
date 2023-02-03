<?php
unset($_SESSION['auth']);
unset($_SESSION['id']);
unset($_SESSION['login']);
unset($_SESSION['status']);

header('Location: /auth');
die();
?>