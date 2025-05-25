<?php
session_start();
include("baglanti.php");

// Sepet oturum değişkenini kontrol et
if (!isset($_SESSION["sepet"])) {
    $_SESSION["sepet"] = [];
}

// Tüm ürünlerin kontenjanını geri artır
foreach ($_SESSION["sepet"] as $item) {
    $etkinlik_id = $item['etkinlik_id'];
    $tur = $item['tur'];
    $adet = $item['adet'];

    if ($tur === 'konser' || $tur === 'tiyatro' || $tur === 'sinema') {
        $tablo = ($tur === 'konser') ? 'konserler' : ($tur === 'tiyatro' ? 'tiyatrolar' : 'sinemalar');
        $kontenjan_sorgu = $baglanti->prepare("SELECT kontenjan FROM $tablo WHERE id = ?");
        $kontenjan_sorgu->bind_param("i", $etkinlik_id);
        $kontenjan_sorgu->execute();
        $result = $kontenjan_sorgu->get_result();
        $row = $result->fetch_assoc();
        $mevcut_kontenjan = $row['kontenjan'] ?? 0;

        $yeni_kontenjan = $mevcut_kontenjan + $adet;
        $update_sorgu = $baglanti->prepare("UPDATE $tablo SET kontenjan = ? WHERE id = ?");
        $update_sorgu->bind_param("ii", $yeni_kontenjan, $etkinlik_id);
        $update_sorgu->execute();
        $update_sorgu->close();
        $kontenjan_sorgu->close();
    }
}

// Sepeti sıfırla
$_SESSION["sepet"] = [];

// Sepet sayfasına yönlendir
header("Location: sepet.php");
exit();
?>