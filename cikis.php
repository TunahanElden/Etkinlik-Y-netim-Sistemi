<?php
session_start(); // Oturumu başlat
$_SESSİON=array(); 
session_destroy(); // Oturumu yok et
header("Location: login.php"); // Kullanıcıyı giriş sayfasına yönlendir
?>