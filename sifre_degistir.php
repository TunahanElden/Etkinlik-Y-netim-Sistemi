<?php
// Yeni bir oturum başlatır veya mevcut bir oturumu devam ettirir, kullanıcı verilerini yönetmek için
session_start();
// Veritabanı bağlantı dosyasını dahil eder, veritabanına erişim sağlamak için
include("baglanti.php");

// Kullanıcının oturum açıp açmadığını kontrol eder, yoksa login sayfasına yönlendirir
if (!isset($_SESSION["email"])) {
    // Oturum yoksa login sayfasına yönlendirir
    header("Location: login.php");
    // Kodun yürütülmesini durdurur.
    exit();
}

// Şifre değiştirme için hata ve başarı mesajı değişkenlerini tanımlar
$yeni_parola_err = "";
$yeni_parola_tekrar_err = "";
$basarili = "";

// Form POST yöntemiyle gönderildiyse işlemleri gerçekleştirir
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Yeni parola ve tekrarını alır, yoksa boş string varsayar
    $yeni_parola = $_POST["yeni_parola"] ?? '';
    $yeni_parola_tekrar = $_POST["yeni_parola_tekrar"] ?? '';

    // Yeni parolanın boş olup olmadığını kontrol eder
    if (empty($yeni_parola)) {
        // Boşsa hata mesajı atar
        $yeni_parola_err = "Yeni parola boş bırakılamaz.";
    } elseif (strlen($yeni_parola) < 6) {
        // Parola 6 karakterden azsa hata mesajı atar
        $yeni_parola_err = "Parola en az 6 karakter olmalıdır.";
    }

    // Yeni parola ile tekrarının eşleşip eşleşmediğini kontrol eder
    if ($yeni_parola !== $yeni_parola_tekrar) {
        // Eşleşmiyorsa hata mesajı atar
        $yeni_parola_tekrar_err = "Parolalar uyuşmuyor.";
    }

    // Herhangi bir hata yoksa veritabanında güncelleme yapar
    if (empty($yeni_parola_err) && empty($yeni_parola_tekrar_err)) {
        // Yeni parolayı hash'ler ve güvenli bir şekilde saklar
        $hashli = password_hash($yeni_parola, PASSWORD_DEFAULT);
        // Kullanıcının oturum email'ini alır
        $email = $_SESSION["email"];

        // Veritabanında parolayı ve şifre değiştirme durumunu günceller
        $stmt = $baglanti->prepare("UPDATE kullanicilar SET parola = ?, sifre_degisti = 1 WHERE email = ?");
        $stmt->bind_param("ss", $hashli, $email); // Değerleri bağlar (her ikisi de string)

        // Güncelleme başarılıysa
        if ($stmt->execute()) {
            // Başarı mesajı ayarlar ve 3 saniye sonra anasayfaya yönlendirir
            $basarili = "Parolanız başarıyla değiştirildi. Yönlendiriliyorsunuz...";
            header("refresh:3;url=anasayfa.php");
        } else {
            // Güncelleme başarısızsa hata mesajı atar
            $yeni_parola_err = "Hata oluştu.";
        }

        $stmt->close(); // Hazırlanmış ifadeyi kapatır, kaynakları serbest bırakır
        $baglanti->close(); // Veritabanı bağlantısını kapatır, kaynakları serbest bırakır
    }
}
?>

<!DOCTYPE html>
<!-- HTML belgesinin dilini Türkçeye ayarlar -->
<html lang="tr">
<head>
    <!-- Karakter kodlamasını UTF-8 olarak tanımlar -->
    <meta charset="UTF-8">
    <!-- Sayfa başlığını belirler -->
    <title>Şifre Değiştir</title>
    <!-- Bootstrap CSS dosyasını ekler, stil için -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Sayfanın gövdesini tanımlar, arka planı açık yapar -->

    <div class="container mt-5">
        <!-- İçeriği bir konteyner içinde tutar, üstte 5 birim boşluk bırakır -->
        <div class="card p-4">
            <!-- Kart yapısını tanımlar, 4 birim iç boşluk bırakır -->
            <h3 class="mb-4">İlk Giriş - Şifre Değiştir</h3>
            <!-- Başlık, altında 4 birim boşluk bırakır -->

            <?php if ($basarili): ?>
                <!-- Başarı mesajı varsa göster -->
                <div class="alert alert-success"><?php echo $basarili; ?></div>
                <!-- Bootstrap başarı uyarısı, mesajı yazdırır -->
            <?php else: ?>
                <!-- Başarı yoksa formu göster -->
                <form method="POST">
                    <!-- Formu tanımlar, POST ile gönderir -->
                    <div class="mb-3">
                        <!-- Yeni parola giriş alanı grubu -->
                        <label for="yeni_parola" class="form-label">Yeni Parola</label>
                        <!-- Etiket, yeni parola için -->
                        <input type="password" name="yeni_parola" class="form-control <?php if($yeni_parola_err) echo 'is-invalid'; ?>">
                        <!-- Giriş alanı, hata varsa kırmızı çerçeve ekler -->
                        <div class="invalid-feedback"><?php echo $yeni_parola_err; ?></div>
                        <!-- Hata mesajını gösterir -->
                    </div>

                    <div class="mb-3">
                        <!-- Yeni parola tekrar giriş alanı grubu -->
                        <label for="yeni_parola_tekrar" class="form-label">Yeni Parola (Tekrar)</label>
                        <!-- Etiket, tekrar parola için -->
                        <input type="password" name="yeni_parola_tekrar" class="form-control <?php if($yeni_parola_tekrar_err) echo 'is-invalid'; ?>">
                        <!-- Giriş alanı, hata varsa kırmızı çerçeve ekler -->
                        <div class="invalid-feedback"><?php echo $yeni_parola_tekrar_err; ?></div>
                        <!-- Hata mesajını gösterir -->
                    </div>

                    <button type="submit" class="btn btn-success">Parolayı Değiştir</button>
                    <!-- Gönder butonu, yeşil renkli -->
                </form>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>