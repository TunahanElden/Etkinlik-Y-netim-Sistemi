<?php
// Yeni bir oturum başlatır veya mevcut bir oturumu devam ettirir, kullanıcı verilerini istekler arasında yönetmek için
session_start();

// Veritabanı bağlantı dosyasını dahil eder, veritabanına bağlantı kurmak için
include("baglanti.php");

// 'email' oturum değişkeninin tanımlı olup olmadığını kontrol eder, yani kullanıcının oturum açıp açmadığını
if (!isset($_SESSION["email"])) {
    // Kullanıcı oturum açmamışsa, login.php sayfasına yönlendirir
    header("Location: login.php");
    // Yönlendirme sonrası kodun yürütülmesini durdurur
    exit();
}

// Oturumdaki kullanıcı e-posta adresini bir değişkene kaydeder, kolay erişim için
$email = $_SESSION["email"];

// E-posta güncelleme formunun gönderilip gönderilmediğini kontrol eder (POST isteği ile)
if (isset($_POST['email_guncelle'])) {
    // Kullanıcının formda girdiği yeni e-posta adresini alır
    $yeni_email = $_POST['email'];

    // 'kullanic analizler' tablosunda e-posta adresini güncellemek için bir SQL sorgusu hazırlar
    $stmt = $baglanti->prepare("UPDATE kullanicilar SET email = ? WHERE email = ?");
    // Yeni e-posta ve mevcut e-posta adreslerini hazırlanan sorguya parametre olarak bağlar (her ikisi de string)
    $stmt->bind_param("ss", $yeni_email, $email);
    // Hazırlanan sorguyu çalıştırarak veritabanında e-posta adresini günceller
    $stmt->execute();
    // Hazırlanan sorguyu kapatır, kaynakları serbest bırakır
    $stmt->close();

    // 'ilgi_alanlari' tablosunda kullanıcı e-posta adresini güncellemek için bir SQL sorgusu hazırlar
    $stmt = $baglanti->prepare("UPDATE ilgi_alanlari SET kullanici_email = ? WHERE kullanici_email = ?");
    // Yeni e-posta ve mevcut e-posta adreslerini hazırlanan sorguya parametre olarak bağlar (her ikisi de string)
    $stmt->bind_param("ss", $yeni_email, $email);
    // Hazırlanan sorguyu çalıştırarak ilgi alanlarındaki e-posta adresini günceller
    $stmt->execute();
    // Hazırlanan sorguyu kapatır, kaynakları serbest bırakır
    $stmt->close();

    // Oturumdaki e-posta bilgisini yeni e-posta adresiyle günceller
    $_SESSION['email'] = $yeni_email;
    // Kullanıcıyı, e-posta güncellendi mesajıyla birlikte profil_bilgiler.php sayfasına yönlendirir
    header("Location: profil_bilgiler.php?mesaj=E-posta güncellendi");
    // Yönlendirme sonrası kodun yürütülmesini durdurur
    exit();
}

// İlgi alanları güncelleme formunun gönderilip gönderilmediğini kontrol eder (POST isteği ile)
if (isset($_POST['ilgi_guncelle'])) {
    // Formdan gönderilen ilgi alanlarını alır, yoksa boş bir dizi oluşturur
    $ilgi_alanlari = isset($_POST['ilgi_alanlari']) ? $_POST['ilgi_alanlari'] : [];

    // Kullanıcının mevcut ilgi alanlarını silmek için bir SQL sorgusu hazırlar
    $stmt = $baglanti->prepare("DELETE FROM ilgi_alanlari WHERE kullanici_email = ?");
    // Mevcut e-posta adresini hazırlanan sorguya parametre olarak bağlar (string)
    $stmt->bind_param("s", $email);
    // Hazırlanan sorguyu çalıştırarak kullanıcının mevcut ilgi alanlarını siler
    $stmt->execute();
    // Hazırlanan sorguyu kapatır, kaynakları serbest bırakır
    $stmt->close();

    // Yeni ilgi alanlarını eklemek için bir döngü başlatır
    foreach ($ilgi_alanlari as $tur) {
        // Yeni ilgi alanını 'ilgi_alanlari' tablosuna eklemek için bir SQL sorgusu hazırlar
        $stmt = $baglanti->prepare("INSERT INTO ilgi_alanlari (kullanici_email, tur) VALUES (?, ?)");
        // Kullanıcı e-posta adresini ve ilgi alanı türünü hazırlanan sorguya parametre olarak bağlar (her ikisi de string)
        $stmt->bind_param("ss", $email, $tur);
        // Hazırlanan sorguyu çalıştırarak yeni ilgi alanını ekler
        $stmt->execute();
        // Hazırlanan sorguyu kapatır, kaynakları serbest bırakır
        $stmt->close();
    }

    // Kullanıcıyı, ilgi alanları güncellendi mesajıyla birlikte ilgi_alanlari.php sayfasına yönlendirir
    header("Location: ilgi_alanlari.php?mesaj=İlgi alanları güncellendi");
    // Yönlendirme sonrası kodun yürütülmesini durdurur
    exit();
}
?>