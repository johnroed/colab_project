<?php
session_start();
session_destroy();
header("Location: login_files/login_form.php");
exit();
?>