<?php
// Yeni bir oturum başlatır veya mevcut bir oturumu devam ettirir, kullanıcı verilerini yönetmek için
session_start();
// Veritabanı bağlantı dosyasını dahil eder, veritabanına erişim sağlamak için
include("baglanti.php");

// Oturum kontrolü
if (!isset($_SESSION["email"])) {
    // Kullanıcının oturum açıp açmadığını kontrol eder, yoksa login sayfasına yönlendirir
    header("Location: login.php");
    // Kodun yürütülmesini durdurur ve betiği sonlandırır
    exit();
}

// Sepet oturum değişkenini kontrol eder
if (!isset($_SESSION["sepet"])) {
    // Sepet tanımlı değilse, boş bir dizi ile başlatır
    $_SESSION["sepet"] = [];
}
?>

<!DOCTYPE html>
<!-- HTML belgesinin dilini Türkçeye ayarlar -->
<html lang="tr">
<head>
    <!-- Karakter kodlamasını UTF-8 olarak tanımlar -->
    <meta charset="UTF-8">
    <!-- Sayfa başlığını belirler -->
    <title>Sepet</title>
    <!-- Bootstrap CSS dosyasını ekler, stil için -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Özel CSS stillerini tanımlar -->
    <style>
        body {
            background: linear-gradient(to right,rgb(145, 82, 212),rgb(80, 140, 243)); /* Mor-mavi geçiş */
            font-family: Arial, sans-serif; /* Yazı tipini Arial yapar */
            padding: 20px; /* Sayfanın çevresine 20px iç boşluk bırakır */
        }
        .sepet-tablo {
            width: 90%; /* Tablo genişliğini %90 yapar */
            margin: 30px auto; /* Üstten ve alttan 30px, sağdan ve soldan otomatik boşluk bırakır */
            border-collapse: collapse; /* Kenarlıkları birleştirir */
        }
        .sepet-tablo th, .sepet-tablo td {
            padding: 12px; /* Hücrelerde 12px iç boşluk bırakır */
            text-align: center; /* Metni ortalar */
            vertical-align: middle; /* Metni dikey olarak ortalar */
        }
        .sepet-tablo th {
            background-color: #f8f9fa; /* Başlık arka planını açık gri yapar */
            font-weight: bold; /* Yazı tipini kalın yapar */
        }
        .sepet-tablo tr {
            border-bottom: 1px solid #dee2e6; /* Her satırın altını gri çizgiyle ayırır */
        }
        .toplam-fiyat {
            font-size: 1.3rem; /* Yazı boyutunu 1.3rem yapar */
            font-weight: bold; /* Yazı tipini kalın yapar*/
            text-align: right; /* Metni sağa hizalar*/
            margin-right: 5%; /* Sağdan %5 boşluk bırakır */
            margin-top: 20px; /* Üstten 20px boşluk bırakır */
        }
        .text-center {
            margin-top: 20px; /* Üstten 20px boşluk bırakır */
        }
        .btn {
            margin: 5px; /* Butonların çevresine 5px boşluk bırakır */
        }
        .btn-sm {
            padding: 2px 6px; /* Küçük butonlar için iç doldurma ayarı */
            font-size: 0.8rem; /* Yazı boyutunu 0.8rem yapar */
        }
    </style>
</head>
<body class="bg-light">
    <!-- Sayfanın gövdesini tanımlar, arka planı açık yapar -->

    <div class="container mt-5">
        <!-- İçeriği bir konteyner içinde tutar, üstte 5 birim boşluk bırakır -->
        <h2 class="text-center mb-4">Sepetiniz</h2>
        <!-- Başlık, ortalanmış ve altında 4 birim boşluk -->
        <?php if (empty($_SESSION["sepet"])): ?>
            <!-- Sepet boşsa uyarı mesajı göster -->
            <div class="alert alert-info text-center" role="alert">
                Sepetinizde henüz ürün bulunmamaktadır.
                <!-- Bilgi uyarısı, metni ortalar -->
                <div class="mt-3">
                    <!-- Altında 3 birim boşluk -->
                    <a href="anasayfa.php" class="btn btn-primary">Alışverişe Devam Et</a>
                    <!-- Anasayfaya yönlendiren mavi buton -->
                </div>
            </div>
        <?php else: ?>
            <!-- Sepet doluysa tabloyu göster -->
            <table class="table table-bordered sepet-tablo">
                <!-- Tabloyu tanımlar, kenarlıklı ve özel stil -->
                <thead>
                    <!-- Tablo başlığı -->
                    <tr>
                        <th>Etkinlik Türü</th> <!-- Etkinlik türü sütunu -->
                        <th>Etkinlik Adı</th> <!-- Etkinlik adı sütunu -->
                        <th>Adet</th> <!-- Adet sütunu -->
                        <th>Birim Fiyat</th> <!-- Birim fiyat sütunu -->
                        <th>Toplam Fiyat</th> <!-- Toplam fiyat sütunu -->
                        <th>İşlem</th> <!-- İşlem sütunu -->
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Genel toplamı hesaplamak için bir değişken tanımlar
                    $genelToplam = 0;
                    // Sepet öğelerini döngüyle işler
                    foreach ($_SESSION["sepet"] as $index => $item) {
                        // Her öğenin toplam fiyatını genel topama ekler
                        $genelToplam += $item['toplam_fiyat'];
                        echo "<tr>
                                <td>" . htmlspecialchars($item['tur']) . "</td>
                                <!-- Etkinlik türünü XSS koruması ile yazdırır -->
                                <td>" . htmlspecialchars($item['ad']) . "</td> 
                                <!-- Etkinlik adını XSS koruması ile yazdırır -->
                                <td class='adet'>" . $item['adet'] . "</td>
                                <!-- Adeti yazdırır -->
                                <td>" . number_format($item['fiyat'], 2) . " TL</td>
                                <!-- Birim fiyatı 2 ondalık basamakla yazdırır -->
                                <td class='toplam-fiyat'>" . number_format($item['toplam_fiyat'], 2) . " TL</td>
                                <!-- Toplam fiyatı 2 ondalık basamakla yazdırır -->
                                <td>
                                    <button class='btn btn-danger btn-sm iptal-btn' data-index='$index' data-etkinlik-id='" . htmlspecialchars($item['etkinlik_id']) . "' data-tur='" . htmlspecialchars($item['tur']) . "' data-bs-toggle='tooltip'>Sil</button>
                                    <!-- Sil butonu, index ve etkinlik bilgilerini saklar, tooltip ekler -->
                                </td>
                            </tr>";
                    }
                    ?>
                </tbody>
            </table>
            <p class="toplam-fiyat">Genel Toplam: <?php echo number_format($genelToplam, 2); ?> TL</p>
            <!-- Genel toplamı 2 ondalık basamakla gösterir -->
            <div class="text-center">
                <!-- Butonları ortalar -->
                <a href="anasayfa.php" class="btn btn-primary">Alışverişe Devam Et</a>
                <!-- Anasayfaya yönlendiren mavi buton -->
                <!--<button class="btn btn-danger" onclick="temizleSepeti()">Sepeti Temizle</button> -->
                <!-- Sepeti temizle butonu, şu an yorum satırı içinde -->
                <button class="btn btn-success" onclick="odemeYap()">Ödemeye Geç</button>
                <!-- Ödemeye geç butonu, yeşil renkli -->
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Bootstrap JavaScript dosyasını ekler, interaktif bileşenler için -->

    <script>
        // Tooltip'leri etkinleştir
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        // Tüm tooltip tetikleyicilerini bir diziye dönüştürür
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            // Her bir tetikleyici için yeni bir tooltip oluşturur
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Sepet boş mesajını gösteren yardımcı fonksiyon
        function showEmptyCartMessage() {
            // Konteyner elementini seçer
            const container = document.querySelector('.container');
            // Konteynerin içeriğini yeni HTML ile değiştirir
            container.innerHTML = `
                <h2 class="text-center mb-4">Sepetiniz</h2>
                <div class="alert alert-info text-center" role="alert">
                    Sepetinizde henüz ürün bulunmamaktadır.
                    <div class="mt-3">
                        <a href="anasayfa.php" class="btn btn-primary">Alışverişe Devam Et</a>
                    </div>
                </div>
            `;
        }

        // Bir bileti silme işlemi
        document.querySelectorAll('.iptal-btn').forEach(btn => {
            // Tüm sil butonlarına olay dinleyicisi ekler
            btn.addEventListener('click', function() {
                // Butonun index, etkinlik ID ve tür bilgilerini alır
                const index = this.getAttribute('data-index');
                const etkinlikId = this.getAttribute('data-etkinlik-id');
                const tur = this.getAttribute('data-tur');

                // Yeni bir XMLHttpRequest nesnesi oluşturur
                const xhr = new XMLHttpRequest();
                // POST isteği açar, sepetten_sil.php'ye gönderir
                xhr.open('POST', 'sepetten_sil.php', true);
                // İstek başlığını ayarlar
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                // İstek durumu değiştiğinde çalışır
                xhr.onreadystatechange = function() {
                    // İstek tamamlandıysa (readyState 4)
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        // Yanıtı JSON olarak ayrıştırmaya çalışır
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.status === 'success') {
                                // Başarılıysa satırı günceller
                                const row = btn.closest('tr'); // En yakın tr elementini alır
                                let currentAdet = parseInt(row.querySelector('.adet').textContent); // Mevcut adeti alır
                                if (currentAdet > 1) {
                                    // Adet 1'den büyükse 1 azaltır
                                    currentAdet -= 1;
                                    row.querySelector('.adet').textContent = currentAdet; // Adeti günceller
                                    row.querySelector('.toplam-fiyat').textContent = (currentAdet * parseFloat(row.querySelector('td:nth-child(4)').textContent.replace(' TL', ''))).toFixed(2) + ' TL'; // Toplam fiyatı günceller
                                } else {
                                    // Adet 1 ise satırı kaldırır
                                    row.remove();
                                }

                                // Anasayfadaki kontenjanı günceller
                                const kart = document.querySelector(`[data-etkinlik-id="${response.etkinlik_id}"]`);
                                if (kart) {
                                    const kontenjanSpan = kart.querySelector('.kontenjan'); // Kontenjan elementini alır
                                    let kontenjan = parseInt(kontenjanSpan.textContent); // Mevcut kontenjanı alır
                                    kontenjan += 1; // 1 bilet kontenjana geri ekler
                                    kontenjanSpan.textContent = kontenjan; // Kontenjanı günceller
                                }

                                // Genel toplamı günceller
                                let genelToplamElement = document.querySelector('.toplam-fiyat');
                                let mevcutToplam = parseFloat(genelToplamElement.textContent.replace('Genel Toplam: ', '').replace(' TL', ''));
                                mevcutToplam -= parseFloat(row.querySelector('td:nth-child(4)').textContent.replace(' TL', ''));
                                genelToplamElement.textContent = `Genel Toplam: ${mevcutToplam.toFixed(2)} TL`;

                                // Eğer sepet boşsa, boş mesajı gösterir
                                if (document.querySelectorAll('tbody tr').length === 0) {
                                    showEmptyCartMessage();
                                }

                                // Kullanıcıya bilgi verir
                                //alert('Bir bilet sepetten kaldırıldı!');
                            } else {
                                // Hata mesajı varsa gösterir
                                alert('Hata: ' + response.message);
                            }
                        } catch (e) {
                            // JSON ayrıştırma hatası varsa uyarı verir
                            //alert('Sunucudan geçersiz bir yanıt alındı. Lütfen tekrar deneyin.');
                        }
                    } else if (xhr.readyState === 4) {
                        // Sunucu hatası varsa uyarı verir
                        //alert('Sunucu hatası: ' + xhr.status + ' - ' + xhr.responseText);
                    }
                };
                // Gönderilecek veriyi hazırlar (index, etkinlik_id ve tur eklenerek)
                const data = `index=${encodeURIComponent(index)}&etkinlik_id=${encodeURIComponent(etkinlikId)}&tur=${encodeURIComponent(tur)}`;
                // İsteği gönderir
                xhr.send(data);
            });
        });

        // Sepeti tamamen temizle
        function temizleSepeti() {
            // Kullanıcıdan onay ister
            if (confirm('Sepeti tamamen temizlemek istediğinizden emin misiniz?')) {
                // Yeni bir XMLHttpRequest nesnesi oluşturur
                const xhr = new XMLHttpRequest();
                // POST isteği açar, sepet_temizle.php'ye gönderir
                xhr.open('POST', 'sepet_temizle.php', true);
                // İstek başlığını ayarlar
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                // İstek durumu değiştiğinde çalışır
                xhr.onreadystatechange = function() {
                    // İstek tamamlandıysa (readyState 4)
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        // Yanıtı JSON olarak ayrıştırmaya çalışır
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.status === 'success') {
                                // Anasayfadaki kontenjanları günceller
                                response.kontenjan_guncellemeleri.forEach(guncelleme => {
                                    const kart = document.querySelector(`[data-etkinlik-id="${guncelleme.etkinlik_id}"]`);
                                    if (kart) {
                                        const kontenjanSpan = kart.querySelector('.kontenjan'); // Kontenjan elementini alır
                                        kontenjanSpan.textContent = guncelleme.yeni_kontenjan; // Yeni kontenjanı ayarlar
                                    }
                                });

                                // Tüm içeriği boş sepet mesajıyla değiştirir
                                showEmptyCartMessage();

                                // Kullanıcıya bilgi verir
                                alert('Sepet temizlendi!');
                            } else {
                                // Hata mesajı varsa gösterir
                                alert('Hata: ' + response.message);
                            }
                        } catch (e) {
                            // JSON ayrıştırma hatası varsa uyarı verir
                            //alert('Sunucudan geçersiz bir yanıt alındı. Lütfen tekrar deneyin.');
                        }
                    } else if (xhr.readyState === 4) {
                        // Sunucu hatası varsa uyarı verir
                        //alert('Sunucu hatası: ' + xhr.status + ' - Lütfen tekrar deneyin.');
                    }
                };
                // İsteği gönderir
                xhr.send();
            }
        }

        // Ödemeye geç
        function odemeYap() {
            // Kullanıcıyı ödeme sayfasına yönlendirir
            window.location.href = 'odeme.php';
        }
    </script>
</body>
</html>