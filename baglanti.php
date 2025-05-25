<?php

$host="localhost"; // MySQL sunucusu
$kullanici="root"; // MySQL kullanıcı adı
$parola=""; // MySQL şifresi
$vt="uyelik"; // MySQL veritabanı adı

$baglanti=mysqli_connect($host,$kullanici,$parola,$vt); // MySQL'e bağlan
mysqli_set_charset($baglanti,"utf8"); // Karakter setini UTF-8 olarak ayarla
?>