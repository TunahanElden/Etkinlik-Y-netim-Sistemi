<?php
session_start();
// Veritabanı bağlantı dosyasını dahil et
include("baglanti.php");

// Sepet oturum değişkenini başlat, eğer yoksa boş bir dizi oluştur
if (!isset($_SESSION["sepet"])) {
    $_SESSION["sepet"] = [];
}

// Kullanıcının oturum açmış olup olmadığını kontrol et, değilse hata döndür
if (!isset($_SESSION["email"])) {
    echo json_encode(['status' => 'error', 'message' => 'Giriş yapmanız gerekiyor']);
    exit();
}

// POST isteğinden etkinlik bilgilerini al, varsayılan değerlerle hata önle
$etkinlik_id = $_POST['etkinlik_id'] ?? '';
$tur = $_POST['tur'] ?? '';
$adet = (int)($_POST['adet'] ?? 0);
$fiyat = (float)($_POST['fiyat'] ?? 0);
$toplam_fiyat = (float)($_POST['toplam_fiyat'] ?? 0);

// Gelen verilerin doğruluğunu kontrol et, temel gereksinimler sağlanmalı
if ($etkinlik_id && $tur && $adet > 0) {
    // Etkinlik türüne göre tabloyu belirle
    $tablo = ($tur === 'konser') ? 'konserler' : (($tur === 'tiyatro') ? 'tiyatrolar' : 'sinemalar');
    
    // Mevcut kontenjanı ve etkinlik adını veritabanından sorgula
    $stmt = $baglanti->prepare("SELECT kontenjan, ad FROM $tablo WHERE id = ?");
    $stmt->bind_param("i", $etkinlik_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Kontenjan bilgisini al ve kontrol et
    if ($row = $result->fetch_assoc()) {
        $mevcut_kontenjan = $row['kontenjan'];
        $ad = $row['ad']; // Etkinlik adını veritabanından al
        // İstenen adet mevcut kontenjandan fazlaysa hata döndür
        if ($mevcut_kontenjan < $adet) {
            echo json_encode(['status' => 'error', 'message' => 'Yeterli kontenjan yok']);
            exit();
        }
    } else {
        // Etkinlik bulunamazsa hata döndür
        echo json_encode(['status' => 'error', 'message' => 'Etkinlik bulunamadı']);
        exit();
    }
    $stmt->close();

    // Kontenjanı güncelle, talep edilen adet kadar azalt
    $stmt = $baglanti->prepare("UPDATE $tablo SET kontenjan = kontenjan - ? WHERE id = ?");
    $stmt->bind_param("ii", $adet, $etkinlik_id);
    $stmt->execute();
    $stmt->close();

    // Sepetteki aynı etkinlik için adeti güncelle veya yeni etkinlik ekle
    $index = array_search($etkinlik_id, array_column($_SESSION["sepet"], 'etkinlik_id'));
    if ($index !== false && $_SESSION["sepet"][$index]['tur'] === $tur) {
        $_SESSION["sepet"][$index]['adet'] += $adet;
        $_SESSION["sepet"][$index]['toplam_fiyat'] = $_SESSION["sepet"][$index]['adet'] * $_SESSION["sepet"][$index]['fiyat'];
    } else {
        // Yeni bir etkinlik olarak sepete ekle
        $_SESSION["sepet"][] = [
            'etkinlik_id' => $etkinlik_id,
            'tur' => $tur,
            'ad' => $ad,
            'adet' => $adet,
            'fiyat' => $fiyat,
            'toplam_fiyat' => $toplam_fiyat
        ];
    }

    // İşlem başarılıysa yanıt döndür
    echo json_encode(['status' => 'success', 'message' => 'Ürün sepete eklendi']);
} else {
    // Geçersiz veri durumunda hata döndür
    echo json_encode(['status' => 'error', 'message' => 'Geçersiz veri']);
}

// Veritabanı bağlantısını kapat
$baglanti->close();
?>