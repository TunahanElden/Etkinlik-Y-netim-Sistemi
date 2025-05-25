<?php
// Yeni bir oturum başlatır veya mevcut bir oturumu devam ettirir, kullanıcı verilerini yönetmek için
session_start();
// Veritabanı bağlantı dosyasını dahil eder, veritabanına erişim sağlamak için
include("baglanti.php");

// Kullanıcının oturum açıp açmadığını kontrol eder, yoksa login sayfasına yönlendirir
if (!isset($_SESSION["email"])) {
    // Oturum yoksa login sayfasına yönlendirir
    header("Location: login.php");
    // Kodun yürütülmesini durdurur ve betiği sonlandırır
    exit();
}

// Kullanıcının oturum email'ini alır
$email = $_SESSION["email"];

// Kullanıcının bilgilerini veritabanından çekmek için bir SQL sorgusu hazırlar
$stmt = $baglanti->prepare("SELECT email, kullanici_adi FROM kullanicilar WHERE email = ?");
// Email'i sorguya bağlar (string)
$stmt->bind_param("s", $email);
// Sorguyu çalıştırır
$stmt->execute();
// Sorgu sonucunu alır
$result = $stmt->get_result();
// Kullanıcı bilgilerini bir dizi olarak alır
$kullanici = $result->fetch_assoc();
// Hazırlanmış ifadeyi kapatır, kaynakları serbest bırakır
$stmt->close();

// Kullanıcı email'ini değişkene atar
$email = $kullanici['email'];
// Kullanıcı adını değişkene atar
$kullanici_adi = $kullanici['kullanici_adi'];
?>

<!DOCTYPE html>
<!-- HTML belgesinin dilini Türkçeye ayarlar -->
<html lang="tr">
<head>
    <!-- Karakter kodlamasını UTF-8 olarak tanımlar -->
    <meta charset="UTF-8">
    <!-- Sayfa başlığını belirler -->
    <title>Kullanıcı Bilgilerim</title>
    <!-- Bootstrap CSS dosyasını ekler, stil için -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Özel CSS stillerini tanımlar -->
    <style>
        body { 
            background: linear-gradient(to right,rgb(145, 82, 212),rgb(80, 140, 243)); /* Mor-mavi geçiş */
            font-family: Arial, sans-serif; // Yazı tipini Arial yapar
            padding: 20px; // Sayfanın çevresine 20px iç boşluk bırakır
        }
        .container { 
            max-width: 600px; // Konteynerin maksimum genişliğini 600px ile sınırlar
            margin: 0 auto; // Konteyneri sayfanın ortasında hizalar
        }
    </style>
</head>
<body>
    <!-- Sayfanın gövdesini tanımlar -->

    <div class="container">
        <!-- İçeriği bir konteyner içinde tutar -->
        <h1>Kullanıcı Bilgilerim</h1>
        <!-- Başlık, sayfanın ana başlığı -->
        <p><strong>Kullanıcı Adı:</strong> <?php echo htmlspecialchars($kullanici_adi); ?></p>
        <!-- Kullanıcı adını gösterir, XSS koruması için htmlspecialchars kullanır -->
        <p><strong>E-posta:</strong> <?php echo htmlspecialchars($email); ?></p>
        <!-- Kullanıcı email'ini gösterir, XSS koruması için htmlspecialchars kullanır -->

        <a href="anasayfa.php" class="btn btn-secondary mt-3">Anasayfaya Dön</a>
        <!-- Anasayfaya dön bağlantısı, gri renkli buton, üstte 3 birim boşluk -->
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Bootstrap JavaScript dosyasını ekler, interaktif bileşenler için -->
</body>
</html>