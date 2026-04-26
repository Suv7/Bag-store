<?php

define('UPLOAD_DIR', 'uploaded_img');
define('UPLOAD_MAX_FILE_SIZE', 500000);
$conn = mysqli_connect('localhost','root','','crud') or die('connection failed');

require_once 'util.php';
?>