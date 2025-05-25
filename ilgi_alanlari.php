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

// Kullanıcının ilgi alanlarını veritabanından çekmek için bir SQL sorgusu hazırlar
$stmt = $baglanti->prepare("SELECT tur FROM ilgi_alanlari WHERE kullanici_email = ?");
// Email'i sorguya bağlar (string)
$stmt->bind_param("s", $email);
// Sorguyu çalıştırır
$stmt->execute();
// Sorgu sonucunu alır
$result = $stmt->get_result();
// İlgi alanlarını saklamak için bir dizi tanımlar
$ilgi_alanlari = [];
// Sonuçlardan her bir satırı döngüyle alır
while ($row = $result->fetch_assoc()) {
    // Her bir ilgi alanını diziye ekler
    $ilgi_alanlari[] = $row['tur'];
}
// Hazırlanmış ifadeyi kapatır, kaynakları serbest bırakır
$stmt->close();
?>

<!DOCTYPE html>
<!-- HTML belgesinin dilini Türkçeye ayarlar -->
<html lang="tr">
<head>
    <!-- Karakter kodlamasını UTF-8 olarak tanımlar -->
    <meta charset="UTF-8">
    <!-- Sayfa başlığını belirler -->
    <title>İlgi Alanlarım</title>
    <!-- Bootstrap CSS dosyasını ekler, stil için -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Özel CSS stillerini tanımlar -->
    <style>
        body { 
            background: linear-gradient(to left,rgb(145, 82, 212),rgb(80, 140, 243)); /* Mor-mavi geçiş */
            font-family: Arial, sans-serif; /* Yazı tipini Arial yapar */
            padding: 20px; /* Sayfanın çevresine 20px iç boşluk bırakır. */
        }
        .container { 
            max-width: 600px; /* Konteynerin maksimum genişliğini 600px ile sınırlar */
            margin: 0 auto; /* Konteyneri sayfanın ortasında hizalar. */
        }
    </style>
</head>
<body>
    <!-- Sayfanın gövdesini tanımlar -->

    <div class="container">
        <!-- İçeriği bir konteyner içinde tutar -->
        <h1>İlgi Alanlarım</h1>
        <!-- Başlık, sayfanın ana başlığı -->
        <p><strong>Mevcut İlgi Alanları:</strong> 
            <?php echo !empty($ilgi_alanlari) ? htmlspecialchars(implode(', ', $ilgi_alanlari)) : 'Henüz ilgi alanı eklenmedi.'; ?>
            <!-- Kullanıcının mevcut ilgi alanlarını gösterir, yoksa bir mesaj yazdırır -->
        </p>

        <!-- İlgi Alanlarını Güncelleme Formu -->
        <h3>İlgi Alanlarınızı Güncelleyin</h3>
        <!-- Alt başlık, form için -->
        <form method="POST" action="profil_guncelle.php">
            <!-- Formu tanımlar, POST ile profil_guncelle.php sayfasına gönderir -->
            <div class="mb-3">
                <!-- İlgi alanları seçim grubu -->
                <label for="ilgi_alanlari" class="form-label">İlgi Alanlarınızı Seçin:</label><br>
                <!-- Etiket, ilgi alanları için -->
                <!-- Mevcut kategoriler -->
                <input type="checkbox" name="ilgi_alanlari[]" value="Tiyatro" <?php echo in_array('Tiyatro', $ilgi_alanlari) ? 'checked' : ''; ?>> 
                Tiyatro<br>
                <!-- Tiyatro seçeneği, daha önce seçilmişse işaretli gelir -->
                <input type="checkbox" name="ilgi_alanlari[]" value="Konser" <?php echo in_array('Konser', $ilgi_alanlari) ? 'checked' : ''; ?>> 
                Konser<br>
                <!-- Konser seçeneği, daha önce seçilmişse işaretli gelir -->
                <input type="checkbox" name="ilgi_alanlari[]" value="Sinema" <?php echo in_array('Sinema', $ilgi_alanlari) ? 'checked' : ''; ?>> 
                Sinema<br>
                <!-- Sinema seçeneği, daha önce seçilmişse işaretli gelir -->
            </div>
            <button type="submit" name="ilgi_guncelle" class="btn btn-primary">Kaydet</button>
            <!-- Gönder butonu, mavi renkli -->
        </form>

        <a href="anasayfa.php" class="btn btn-secondary mt-3">Anasayfaya Dön</a>
        <!-- Anasayfaya dön bağlantısı, gri renkli buton, üstte 3 birim boşluk -->
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Bootstrap JavaScript dosyasını ekler, interaktif bileşenler için -->
</body>
</html>