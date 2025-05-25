<?php
// Yeni bir oturum başlatır veya mevcut bir oturumu devam ettirir, kullanıcı verilerini yönetmek için
session_start();

// Veritabanı bağlantı dosyasını dahil eder, veritabanına bağlantı kurmak için
include("baglanti.php");

// HTTP yanıtının JSON formatında olduğunu ve UTF-8 karakter kodlamasını kullandığını belirtir
header('Content-Type: application/json; charset=UTF-8');

// Kullanıcının oturum açıp açmadığını kontrol eder, 'email' oturum değişkeni var mı diye bakar
if (!isset($_SESSION["email"])) {
    // Oturum yoksa, 403 (Yasak) HTTP durum kodu ayarlar
    http_response_code(403);
    // Hata mesajını JSON formatında döndürür, kullanıcıya giriş yapması gerektiğini bildirir
    echo json_encode(['status' => 'error', 'message' => 'Giriş yapmanız gerekiyor']);
    // Kodun yürütülmesini durdurur ve betiği sonlandırır
    exit();
}

// Sepet oturum değişkeninin tanımlı olup olmadığını kontrol eder
if (!isset($_SESSION["sepet"])) {
    // Sepet yoksa, boş bir dizi olarak başlatır
    $_SESSION["sepet"] = [];
}

// POST isteğiyle gönderilen 'index' değerini alır, yoksa null atar
$index = $_POST['index'] ?? null;

// Index değerinin null olup olmadığını veya sepet dizisinde bu indekse sahip bir öğe olup olmadığını kontrol eder
if ($index === null || !isset($_SESSION["sepet"][$index])) {
    // Geçersiz bir index ise, 400 (Hatalı İstek) HTTP durum kodu ayarlar
    http_response_code(400);
    // Hata mesajını JSON formatında döndürür, geçersiz sepet öğesi olduğunu bildirir
    echo json_encode(['status' => 'error', 'message' => 'Geçersiz sepet öğesi']);
    // Kodun yürütülmesini durdurur ve betiği sonlandırır
    exit();
}

// Sepet öğesini referans olarak alır, böylece değişiklikler doğrudan oturum dizisine yansır
$item = &$_SESSION["sepet"][$index];
// Sepet öğesinden etkinlik ID'sini alır
$etkinlik_id = $item['etkinlik_id'];
// Sepet öğesinden etkinlik türünü (konser, tiyatro, sinema) alır
$tur = $item['tur'];
// Silinecek bilet adedini tanımlar, her zaman 1 adet silinir
$silinen_adet = 1; // Sadece 1 adet azalt

// Etkinlik türünün geçerli olup olmadığını kontrol eder (konser, tiyatro veya sinema)
if (in_array($tur, ['konser', 'tiyatro', 'sinema'])) {
    // Etkinlik türüne göre veritabanındaki tablo adını belirler (konserler, tiyatrolar, sinemalar)
    $tablo = ($tur === 'konser') ? 'konserler' : ($tur === 'tiyatro' ? 'tiyatrolar' : 'sinemalar');
    
    // Veritabanından ilgili etkinliğin mevcut kontenjanını almak için bir SQL sorgusu hazırlar
    $kontenjan_sorgu = $baglanti->prepare("SELECT kontenjan FROM $tablo WHERE id = ?");
    // Sorgu hazırlanamazsa hata kontrolü yapar
    if (!$kontenjan_sorgu) {
        // 500 (Sunucu Hatası) HTTP durum kodu ayarlar
        http_response_code(500);
        // Veritabanı hatasını JSON formatında döndürür
        echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası: ' . $baglanti->error]);
        // Kodun yürütülmesini durdurur ve betiği sonlandırır
        exit();
    }
    // Etkinlik ID'sini sorguya bağlar (tamsayı olarak)
    $kontenjan_sorgu->bind_param("i", $etkinlik_id);
    // Hazırlanan sorguyu çalıştırır
    $kontenjan_sorgu->execute();
    // Sorgu sonucunu alır
    $result = $kontenjan_sorgu->get_result();
    
    // Sorgudan dönen satırı alır
    if ($row = $result->fetch_assoc()) {
        // Mevcut kontenjanı alır, yoksa 0 varsayar
        $mevcut_kontenjan = $row['kontenjan'] ?? 0;
        // Yeni kontenjanı hesaplar, mevcut kontenjana silinen adedi ekler
        $yeni_kontenjan = $mevcut_kontenjan + $silinen_adet;

        // Kontenjanı güncellemek için bir SQL sorgusu hazırlar
        $update_sorgu = $baglanti->prepare("UPDATE $tablo SET kontenjan = ? WHERE id = ?");
        // Sorgu hazırlanamazsa hata kontrolü yapar
        if (!$update_sorgu) {
            // 500 (Sunucu Hatası) HTTP durum kodu ayarlar
            http_response_code(500);
            // Veritabanı hatasını JSON formatında döndürür
            echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası: ' . $baglanti->error]);
            // Kodun yürütülmesini durdurur ve betiği sonlandırır
            exit();
        }
        // Yeni kontenjan ve etkinlik ID'sini sorguya bağlar (her ikisi de tamsayı)
        $update_sorgu->bind_param("ii", $yeni_kontenjan, $etkinlik_id);
        // Hazırlanan sorguyu çalıştırır
        $update_sorgu->execute();
        // Güncelleme sorgusunu kapatır, kaynakları serbest bırakır
        $update_sorgu->close();
    } else {
        // Etkinlik bulunamazsa, 400 (Hatalı İstek) HTTP durum kodu ayarlar
        http_response_code(400);
        // Hata mesajını JSON formatında döndürür, etkinlik bulunamadığını bildirir
        echo json_encode(['status' => 'error', 'message' => 'Etkinlik bulunamadı']);
        // Kontenjan sorgusunu kapatır
        $kontenjan_sorgu->close();
        // Veritabanı bağlantısını kapatır
        $baglanti->close();
        // Kodun yürütülmesini durdurur ve betiği sonlandırır
        exit();
    }
    // Kontenjan sorgusunu kapatır, kaynakları serbest bırakır
    $kontenjan_sorgu->close();
}

// Sepet öğesinin adedini kontrol eder, 1'den büyükse azaltır
if ($item['adet'] > 1) {
    // Öğenin adedini 1 azaltır
    $item['adet'] -= $silinen_adet;
    // Toplam fiyatı günceller, yeni adedi birim fiyatla çarpar
    $item['toplam_fiyat'] = $item['adet'] * $item['fiyat'];
    // Yeni adedi kaydeder
    $yeni_adet = $item['adet'];
} else {
    // Adet 1 ise, öğeyi sepetten tamamen kaldırır
    unset($_SESSION["sepet"][$index]);
    // Sepet dizisini yeniden indeksler, boşlukları kaldırır
    $_SESSION["sepet"] = array_values($_SESSION["sepet"]);
    // Yeni adedi 0 olarak ayarlar
    $yeni_adet = 0;
}

// İşlem başarılı olduğunda JSON yanıtı döndürür ve 200 OK durum kodunu ayarlar
http_response_code(200); // Başarılı işlem için 200 OK
echo json_encode([
    // İşlemin başarılı olduğunu belirtir
    'status' => 'success',
    // Kullanıcıya bilgi mesajı
    'message' => 'Bilet sepetten azaltıldı',
    // Silinen etkinliğin ID'sini döndürür
    'etkinlik_id' => $etkinlik_id,
    // Etkinlik türünü döndürür
    'tur' => $tur,
    // Silinen bilet adedini döndürür
    'silinen_adet' => $silinen_adet,
    // Kalan adedi döndürür
    'yeni_adet' => $yeni_adet
]);

// Veritabanı bağlantısını kapatır, kaynakları serbest bırakır
$baglanti->close();
?>