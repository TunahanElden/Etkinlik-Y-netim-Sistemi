<?php
include("baglanti.php");
session_start();

// Admin kontrolü 
$is_admin = isset($_SESSION["admin_email"]);
if (!$is_admin) {
    echo "Bu sayfaya erişim izniniz yok.";
    header("Location: login.php");
    exit();
}

// Veritabanındaki onaylanmış etkinlik ID'lerini çekme fonksiyonları
function getOnaylananIds($baglanti, $table) {
    $ids = [];
    $result = $baglanti->query("SELECT id FROM $table");
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $ids[] = $row['id'];
        }
    }
    return $ids;
}

// Kullanıcı Onaylama İşlemi
if (isset($_GET["onayla"])) {
    $kullanici_id = intval($_GET["onayla"]);
    $guncelle = $baglanti->prepare("UPDATE kullanicilar SET onay = 1 WHERE id = ?");
    $guncelle->bind_param("i", $kullanici_id);
    if ($guncelle->execute()) {
        echo "<div class='alert alert-success'>Kullanıcı başarıyla onaylandı.</div>";
    } else {
        echo "<div class='alert alert-danger'>Kullanıcı onaylanırken bir hata oluştu.</div>";
    }
    $guncelle->close();
}

// Duyuru Silme İşlemi
if (isset($_GET['duyuru_sil'])) {
    $sil_id = intval($_GET['duyuru_sil']);
    $sil = $baglanti->prepare("DELETE FROM duyurular WHERE id = ?");
    $sil->bind_param("i", $sil_id);
    $sil->execute();
    $sil->close();
    header("Location: admin_onay.php#announcements");
    exit();
}

// Duyuru Ekleme İşlemi
if (isset($_POST['duyuru_ekle'])) {
    $baslik = $_POST['baslik'] ?? '';
    $icerik = $_POST['icerik'] ?? '';
    if (!empty($baslik) && !empty($icerik)) {
        $stmt = $baglanti->prepare("INSERT INTO duyurular (baslik, icerik) VALUES (?, ?)");
        $stmt->bind_param("ss", $baslik, $icerik);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_onay.php#announcements");
        exit();
    }
}

// Duyuru Düzenleme İşlemi
$duzenlenecek_duyuru = null;
if (isset($_GET['duyuru_duzenle'])) {
    $duzenle_id = intval($_GET['duyuru_duzenle']);
    $sonuc = $baglanti->prepare("SELECT * FROM duyurular WHERE id = ?");
    $sonuc->bind_param("i", $duzenle_id);
    $sonuc->execute();
    $result = $sonuc->get_result();
    if ($result && $result->num_rows > 0) {
        $duzenlenecek_duyuru = $result->fetch_assoc();
    }
    $sonuc->close();
}

// Duyuru Güncelleme İşlemi
if (isset($_POST['duyuru_guncelle'])) {
    $id = intval($_POST['duyuru_id']);
    $baslik = $_POST['baslik'] ?? '';
    $icerik = $_POST['icerik'] ?? '';
    if (!empty($baslik) && !empty($icerik)) {
        $stmt = $baglanti->prepare("UPDATE duyurular SET baslik = ?, icerik = ? WHERE id = ?");
        $stmt->bind_param("ssi", $baslik, $icerik, $id);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_onay.php#announcements");
        exit();
    }
}

// Etkinlik Ekleme Fonksiyonu
function etkinlikEkle($baglanti, $table, $etkinlik_id, $ad, $tur, $tarih, $sehir, $kontenjan, $redirect) {
    $stmt = $baglanti->prepare("INSERT INTO $table (id, ad, tur, tarih, sehir, kontenjan) 
                                VALUES (?, ?, ?, ?, ?, ?) 
                                ON DUPLICATE KEY UPDATE 
                                ad=VALUES(ad), tur=VALUES(tur), tarih=VALUES(tarih), 
                                sehir=VALUES(sehir), kontenjan=VALUES(kontenjan)");
    $stmt->bind_param("sssssi", $etkinlik_id, $ad, $tur, $tarih, $sehir, $kontenjan);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_onay.php#$redirect");
    exit();
}

// Etkinlik Silme Fonksiyonu
function etkinlikSil($baglanti, $table, $etkinlik_id, $redirect) {
    $stmt = $baglanti->prepare("DELETE FROM $table WHERE id = ?");
    $stmt->bind_param("s", $etkinlik_id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_onay.php#$redirect");
    exit();
}

// Konser Ekleme İşlemi
if (isset($_POST['konser_ekle'])) {
    $etkinlik_id = $_POST['etkinlik_id'] ?? '';
    $ad = $_POST['ad'] ?? '';
    $tur = $_POST['tur'] ?? '';
    $tarih = $_POST['tarih'] ?? '';
    $sehir = $_POST['sehir'] ?? '';
    $kontenjan = (int)($_POST['kontenjan'] ?? 0);
    etkinlikEkle($baglanti, 'konserler', $etkinlik_id, $ad, $tur, $tarih, $sehir, $kontenjan, 'konserler');
}

// Konser Silme İşlemi
if (isset($_GET['konser_sil'])) {
    $konser_id = $_GET['konser_sil'] ?? '';
    etkinlikSil($baglanti, 'konserler', $konser_id, 'konserler');
}

// Tiyatro Ekleme İşlemi
if (isset($_POST['tiyatro_ekle'])) {
    $etkinlik_id = $_POST['etkinlik_id'] ?? '';
    $ad = $_POST['ad'] ?? '';
    $tur = $_POST['tur'] ?? '';
    $tarih = $_POST['tarih'] ?? '';
    $sehir = $_POST['sehir'] ?? '';
    $kontenjan = (int)($_POST['kontenjan'] ?? 0);
    etkinlikEkle($baglanti, 'tiyatrolar', $etkinlik_id, $ad, $tur, $tarih, $sehir, $kontenjan, 'tiyatrolar');
}

// Tiyatro Silme İşlemi
if (isset($_GET['tiyatro_sil'])) {
    $tiyatro_id = $_GET['tiyatro_sil'] ?? '';
    etkinlikSil($baglanti, 'tiyatrolar', $tiyatro_id, 'tiyatrolar');
}

// Sinema Ekleme İşlemi
if (isset($_POST['sinema_ekle'])) {
    $etkinlik_id = $_POST['etkinlik_id'] ?? '';
    $ad = $_POST['ad'] ?? '';
    $tur = $_POST['tur'] ?? '';
    $tarih = $_POST['tarih'] ?? '';
    $sehir = $_POST['sehir'] ?? '';
    $kontenjan = (int)($_POST['kontenjan'] ?? 0);
    etkinlikEkle($baglanti, 'sinemalar', $etkinlik_id, $ad, $tur, $tarih, $sehir, $kontenjan, 'sinemalar');
}

// Sinema Silme İşlemi
if (isset($_GET['sinema_sil'])) {
    $sinema_id = $_GET['sinema_sil'] ?? '';
    etkinlikSil($baglanti, 'sinemalar', $sinema_id, 'sinemalar');
}

// Veritabanındaki etkinlik ID'lerini çek
$onaylanan_konser_ids = getOnaylananIds($baglanti, 'konserler');
$onaylanan_tiyatro_ids = getOnaylananIds($baglanti, 'tiyatrolar');
$onaylanan_sinema_ids = getOnaylananIds($baglanti, 'sinemalar');

// Onay bekleyen kullanıcıları çek
$kullanicilar = $baglanti->query("SELECT id, email FROM kullanicilar WHERE onay = 0");

// Duyuruları çek
$duyurular = $baglanti->query("SELECT * FROM duyurular ORDER BY olusturma_tarihi DESC");

// Ticketmaster API'dan konserleri çek
$ticketmasterApiKey = 'yT5oLyeQQWQcPBgq0C4GgZPJZ6hvBtFj';
$etkinlikUrl = "https://app.ticketmaster.com/discovery/v2/events.json?apikey=$ticketmasterApiKey&countryCode=TR&classificationName=music";
$etkinlik_json = file_get_contents($etkinlikUrl);
$api_konserler = [];
if ($etkinlik_json) {
    $data = json_decode($etkinlik_json, true);
    $api_konserler = $data['_embedded']['events'] ?? [];
    usort($api_konserler, function($a, $b) {
        return strtotime($a['dates']['start']['localDate']) - strtotime($b['dates']['start']['localDate']);
    });
}

// Ticketmaster API'dan tiyatroları çek
$etkinlikUrl = "https://app.ticketmaster.com/discovery/v2/events.json?apikey=$ticketmasterApiKey&countryCode=TR&classificationName=theatre";
$etkinlik_json = file_get_contents($etkinlikUrl);
$api_tiyatrolar = [];
if ($etkinlik_json) {
    $data = json_decode($etkinlik_json, true);
    $api_tiyatrolar = $data['_embedded']['events'] ?? [];
    usort($api_tiyatrolar, function($a, $b) {
        return strtotime($a['dates']['start']['localDate']) - strtotime($b['dates']['start']['localDate']);
    });
}

// TMDB API'dan sinemaları çek
$tmdbApiKey = 'fc58f1bdaf4153e418239a357ebf1118';
$filmUrl = "https://api.themoviedb.org/3/movie/now_playing?api_key=$tmdbApiKey&language=tr-TR&page=1";
$film_json = file_get_contents($filmUrl);
$api_sinemalar = [];
if ($film_json) {
    $data = json_decode($film_json, true);
    $api_sinemalar = $data['results'] ?? [];
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yönetici Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right,rgb(75, 71, 77),rgb(218, 223, 233)); /* arka plan rengi; */
            font-family: Arial, sans-serif;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 1400px;
        }
        .section {
            margin-bottom: 40px;
            padding-top: 20px;
        }
        .nav-tabs {
            margin-bottom: 20px;
        }
        .card {
            height: 100%;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .card-img-top {
            height: 180px;
            object-fit: cover;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }
        .card-body {
            display: flex;
            flex-direction: column;
        }
        .form-container {
            margin-top: 30px;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .edit-mode {
            background-color: #f9f9f9;
            border: 2px solid #4CAF50;
            padding: 15px;
            margin-bottom: 20px;
        }
        .btn-cancel {
            background-color: #f44336;
            margin-left: 10px;
        }
        .duyuru {
            border: 1px solid #ddd;
            margin: 10px 0;
            padding: 15px;
            border-radius: 5px;
            background-color: #e0f7fa;
        }
        input, textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            padding: 10px 20px;
            border-radius: 5px;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .alert-success {
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<div class="container">
    <h1 class="text-center mb-4">Yönetici Paneli</h1>
    <p class="text-center">Hoş geldiniz, <?php echo htmlspecialchars($_SESSION["admin_email"] ?? "Admin"); ?>!</p>

    <!-- Menü Sekmeler -->
    <ul class="nav nav-tabs" id="adminTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab">Kullanıcılar</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="announcements-tab" data-bs-toggle="tab" data-bs-target="#announcements" type="button" role="tab">Duyurular</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="concerts-tab" data-bs-toggle="tab" data-bs-target="#concerts" type="button" role="tab">Konserler</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="theatres-tab" data-bs-toggle="tab" data-bs-target="#theatres" type="button" role="tab">Tiyatrolar</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="cinemas-tab" data-bs-toggle="tab" data-bs-target="#cinemas" type="button" role="tab">Sinemalar</button>
        </li>
    </ul>

    <div class="tab-content" id="adminTabsContent">
        <!-- Kullanıcılar Sekmesi -->
        <div class="tab-pane fade show active" id="users" role="tabpanel" aria-labelledby="users-tab">
            <div class="section">
                <h2 class="text-center">Onay Bekleyen Kullanıcılar</h2>
                <?php if ($kullanicilar && $kullanicilar->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Email</th>
                                    <th>İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($kullanici = $kullanicilar->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($kullanici["email"]); ?></td>
                                        <td>
                                            <a href="admin_onay.php?onayla=<?php echo $kullanici["id"]; ?>" class="btn btn-sm btn-success">Onayla</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center">Onay bekleyen kullanıcı yok.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Duyurular Sekmesi -->
        <div class="tab-pane fade" id="announcements" role="tabpanel" aria-labelledby="announcements-tab">
            <div class="section">
                <?php if ($duzenlenecek_duyuru): ?>
                    <div class="form-container edit-mode">
                        <h2 class="text-center">Duyuru Düzenle</h2>
                        <form method="POST" action="">
                            <input type="hidden" name="duyuru_id" value="<?php echo $duzenlenecek_duyuru['id']; ?>">
                            <input type="text" name="baslik" placeholder="Duyuru Başlığı" value="<?php echo htmlspecialchars($duzenlenecek_duyuru['baslik']); ?>" required>
                            <textarea name="icerik" rows="4" placeholder="Duyuru İçeriği" required><?php echo htmlspecialchars($duzenlenecek_duyuru['icerik']); ?></textarea>
                            <div class="text-center">
                                <button type="submit" name="duyuru_guncelle" class="btn btn-success">Güncelle</button>
                                <a href="admin_onay.php#announcements" class="btn btn-cancel">İptal</a>
                            </div>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="form-container">
                        <h2 class="text-center">Yeni Duyuru Ekle</h2>
                        <form method="POST" action="">
                            <input type="text" name="baslik" placeholder="Duyuru Başlığı" required>
                            <textarea name="icerik" rows="4" placeholder="Duyuru İçeriği" required></textarea>
                            <div class="text-center">
                                <button type="submit" name="duyuru_ekle" class="btn btn-success">Ekle</button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>

                <div class="form-container">
                    <h2 class="text-center">Mevcut Duyurular</h2>
                    <?php if ($duyurular && $duyurular->num_rows > 0): ?>
                        <?php while ($duyuru = $duyurular->fetch_assoc()): ?>
                            <div class="duyuru">
                                <h3><?php echo htmlspecialchars($duyuru['baslik']); ?></h3>
                                <p><?php echo nl2br(htmlspecialchars($duyuru['icerik'])); ?></p>
                                <small>Oluşturulma: <?php echo $duyuru['olusturma_tarihi']; ?></small><br><br>
                                <a href="admin_onay.php?duyuru_duzenle=<?php echo $duyuru['id']; ?>" class="btn btn-sm btn-primary">Düzenle</a>
                                <a href="admin_onay.php?duyuru_sil=<?php echo $duyuru['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu duyuruyu silmek istediğinize emin misiniz?')">Sil</a>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-center">Henüz duyuru bulunmamaktadır.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Konserler Sekmesi -->
        <div class="tab-pane fade" id="concerts" role="tabpanel" aria-labelledby="concerts-tab">
            <div class="section" id="konserler">
                <h2 class="text-center">Konser Yönetimi</h2>
                <div class="alert alert-info">
                    Bu sayfadan, Ticketmaster API'dan gelen konserleri sisteme ekleyebilir veya kaldırabilirsiniz. Eklediğiniz konserler anasayfada gösterilecektir.
                </div>

                <!-- API'dan Gelen Konserler -->
                <h3 class="mt-4">API'dan Gelen Konserler</h3>
                <div class="row">
                    <?php if (empty($api_konserler)): ?>
                        <div class="col-12">
                            <div class="alert alert-warning">
                                Konser bulunamadı veya API bağlantısı sağlanamadı.
                                <?php if (!$etkinlik_json): ?>
                                    <br>Hata: API bağlantısı başarısız. Lütfen API anahtarını veya internet bağlantınızı kontrol edin.
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($api_konserler as $konser):
                            if (($konser['classifications'][0]['segment']['name'] ?? '') !== 'Music') {
                                continue;
                            }
                            $konser_id = $konser['id'] ?? '';
                            $konser_adi = $konser['name'] ?? 'Bilinmeyen Etkinlik';
                            $konser_tur = $konser['classifications'][0]['genre']['name'] ?? 'Diğer';
                            $konser_sehir = 'Antalya';
                            $konser_kapasite = $konser['_embedded']['venues'][0]['capacity'] ?? 100; // Varsayılan 100
                            $konser_onaylandi = in_array($konser_id, $onaylanan_konser_ids);

                            // Tarih ve saat formatlama
                            $raw_tarih = $konser['dates']['start']['localDate'] ?? null;
                            $raw_saat = $konser['dates']['start']['localTime'] ?? null;
                            if ($raw_tarih) {
                                $datetime_str = $raw_tarih . ($raw_saat ? "T$raw_saat" : "T00:00:00");
                                $date = new DateTime($datetime_str);
                                $tarih_formatted = $date->format('Y-m-d H:i:s');
                                $tarih_goruntu = $date->format('d F Y');
                                $saat_goruntu = $date->format('H:i');
                            } else {
                                $tarih_formatted = date('Y-m-d H:i:s');
                                $tarih_goruntu = 'Tarih mevcut değil';
                                $saat_goruntu = 'Saat mevcut değil';
                            }

                            $konser_foto = 'images/konser.png';
                        ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow-sm <?php echo $konser_onaylandi ? 'border-success' : ''; ?>">
                                <?php if ($konser_foto): ?>
                                    <img src="<?php echo $konser_foto; ?>" class="card-img-top mb-3" alt="<?php echo htmlspecialchars($konser_adi); ?>">
                                <?php endif; ?>
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?php echo htmlspecialchars($konser_adi); ?></h5>
                                    <p class="card-text">
                                        <strong>Tür:</strong> <?php echo htmlspecialchars($konser_tur); ?><br>
                                        <strong>Tarih:</strong> <?php echo $tarih_goruntu; ?><br>
                                        <strong>Saat:</strong> <?php echo $saat_goruntu; ?><br>
                                        <strong>Şehir:</strong> <?php echo htmlspecialchars($konser_sehir); ?><br>
                                        <strong>Kapasite:</strong> <?php echo $konser_kapasite ?: 'Bilinmiyor'; ?>
                                    </p>
                                    <?php if ($konser_onaylandi): ?>
                                        <div class="alert alert-success py-1 mb-2">Ana sayfada gösteriliyor</div>
                                        <a href="admin_onay.php?konser_sil=<?php echo htmlspecialchars($konser_id); ?>#konserler" class="btn btn-danger mt-auto" 
                                           onclick="return confirm('Bu konseri sistemden çıkarmak istediğinize emin misiniz?')">Sistemden Çıkar</a>
                                    <?php else: ?>
                                        <form method="POST" action="admin_onay.php#konserler" class="mt-auto">
                                            <input type="hidden" name="etkinlik_id" value="<?php echo htmlspecialchars($konser_id); ?>">
                                            <input type="hidden" name="ad" value="<?php echo htmlspecialchars($konser_adi); ?>">
                                            <input type="hidden" name="tur" value="<?php echo htmlspecialchars($konser_tur); ?>">
                                            <input type="hidden" name="tarih" value="<?php echo $tarih_formatted; ?>">
                                            <input type="hidden" name="sehir" value="<?php echo htmlspecialchars($konser_sehir); ?>">
                                            <input type="hidden" name="kontenjan" value="<?php echo (int)$konser_kapasite; ?>">
                                            <button type="submit" name="konser_ekle" class="btn btn-success">Sisteme Ekle</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Sistemdeki Konserler Tablosu -->
                <h3 class="mt-5">Sistemdeki Konserler</h3>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Konser Adı</th>
                                <th>Tür</th>
                                <th>Tarih</th>
                                <th>Şehir</th>
                                <th>Kontenjan</th>
                                <th>İşlem</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $konserler = $baglanti->query("SELECT * FROM konserler ORDER BY tarih");
                            if ($konserler && $konserler->num_rows > 0) {
                                while ($row = $konserler->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['ad']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['tur']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['tarih']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['sehir']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['kontenjan']) . "</td>";
                                    echo "<td>
                                          <a href='admin_onay.php?konser_sil=" . htmlspecialchars($row['id']) . "#konserler' class='btn btn-sm btn-danger'
                                             onclick='return confirm(\"Bu konseri sistemden çıkarmak istediğinize emin misiniz?\")'>Sil</a>
                                        </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7' class='text-center'>Henüz sisteme eklenmiş konser bulunmamaktadır.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tiyatro Sekmesi -->
        <div class="tab-pane fade" id="theatres" role="tabpanel" aria-labelledby="theatres-tab">
            <div class="section" id="tiyatrolar">
                <h2 class="text-center">Tiyatro Yönetimi</h2>
                <div class="alert alert-info">
                    Bu sayfadan, Ticketmaster API'dan gelen tiyatroları sisteme ekleyebilir veya kaldırabilirsiniz. Eklediğiniz tiyatrolar anasayfada gösterilecektir.
                </div>

                <!-- API'dan Gelen Tiyatrolar -->
                <h3 class="mt-4">API'dan Gelen Tiyatrolar</h3>
                <div class="row">
                    <?php if (empty($api_tiyatrolar)): ?>
                        <div class="col-12">
                            <div class="alert alert-warning">
                                Tiyatro bulunamadı veya API bağlantısı sağlanamadı.
                                <?php if (!$etkinlik_json): ?>
                                    <br>Hata: API bağlantısı başarısız. Lütfen API anahtarını veya internet bağlantınızı kontrol edin.
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($api_tiyatrolar as $tiyatro):
                            if (!in_array(strtolower($tiyatro['classifications'][0]['segment']['name'] ?? ''), ['theatre', 'performing arts', 'arts & theatre'])) {
                                continue;
                            }
                            $tiyatro_id = $tiyatro['id'] ?? '';
                            $tiyatro_adi = $tiyatro['name'] ?? 'Bilinmeyen Etkinlik';
                            $tiyatro_tur = $tiyatro['classifications'][0]['genre']['name'] ?? 'Diğer';
                            $tiyatro_sehir ='Antalya';
                            $tiyatro_kapasite = $tiyatro['_embedded']['venues'][0]['capacity'] ?? 100; // Varsayılan 100
                            $tiyatro_onaylandi = in_array($tiyatro_id, $onaylanan_tiyatro_ids);

                            // Tarih ve saat formatlama
                            $raw_tarih = $tiyatro['dates']['start']['localDate'] ?? null;
                            $raw_saat = $tiyatro['dates']['start']['localTime'] ?? null;
                            if ($raw_tarih) {
                                $datetime_str = $raw_tarih . ($raw_saat ? "T$raw_saat" : "T00:00:00");
                                $date = new DateTime($datetime_str);
                                $tarih_formatted = $date->format('Y-m-d H:i:s');
                                $tarih_goruntu = $date->format('d F Y');
                                $saat_goruntu = $date->format('H:i');
                            } else {
                                $tarih_formatted = date('Y-m-d H:i:s');
                                $tarih_goruntu = 'Tarih mevcut değil';
                                $saat_goruntu = 'Saat mevcut değil';
                            }

                            $tiyatro_foto = 'images/tiyatro.png';
                        ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow-sm <?php echo $tiyatro_onaylandi ? 'border-success' : ''; ?>">
                                <?php if ($tiyatro_foto): ?>
                                    <img src="<?php echo $tiyatro_foto; ?>" class="card-img-top mb-3" alt="<?php echo htmlspecialchars($tiyatro_adi); ?>">
                                <?php endif; ?>
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?php echo htmlspecialchars($tiyatro_adi); ?></h5>
                                    <p class="card-text">
                                        <strong>Tür:</strong> <?php echo htmlspecialchars($tiyatro_tur); ?><br>
                                        <strong>Tarih:</strong> <?php echo $tarih_goruntu; ?><br>
                                        <strong>Saat:</strong> <?php echo $saat_goruntu; ?><br>
                                        <strong>Şehir:</strong> <?php echo htmlspecialchars($tiyatro_sehir); ?><br>
                                        <strong>Kapasite:</strong> <?php echo $tiyatro_kapasite ?: 'Bilinmiyor'; ?>
                                    </p>
                                    <?php if ($tiyatro_onaylandi): ?>
                                        <div class="alert alert-success py-1 mb-2">Ana sayfada gösteriliyor</div>
                                        <a href="admin_onay.php?tiyatro_sil=<?php echo htmlspecialchars($tiyatro_id); ?>#tiyatrolar" class="btn btn-danger mt-auto" 
                                           onclick="return confirm('Bu tiyatroyu sistemden çıkarmak istediğinize emin misiniz?')">Sistemden Çıkar</a>
                                    <?php else: ?>
                                        <form method="POST" action="admin_onay.php#tiyatrolar" class="mt-auto">
                                            <input type="hidden" name="etkinlik_id" value="<?php echo htmlspecialchars($tiyatro_id); ?>">
                                            <input type="hidden" name="ad" value="<?php echo htmlspecialchars($tiyatro_adi); ?>">
                                            <input type="hidden" name="tur" value="<?php echo htmlspecialchars($tiyatro_tur); ?>">
                                            <input type="hidden" name="tarih" value="<?php echo $tarih_formatted; ?>">
                                            <input type="hidden" name="sehir" value="<?php echo htmlspecialchars($tiyatro_sehir); ?>">
                                            <input type="hidden" name="kontenjan" value="<?php echo (int)$tiyatro_kapasite; ?>">
                                            <button type="submit" name="tiyatro_ekle" class="btn btn-success">Sisteme Ekle</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Sistemdeki Tiyatrolar Tablosu -->
                <h3 class="mt-5">Sistemdeki Tiyatrolar</h3>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tiyatro Adı</th>
                                <th>Tür</th>
                                <th>Tarih</th>
                                <th>Şehir</th>
                                <th>Kontenjan</th>
                                <th>İşlem</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $tiyatrolar = $baglanti->query("SELECT * FROM tiyatrolar ORDER BY tarih");
                            if ($tiyatrolar && $tiyatrolar->num_rows > 0) {
                                while ($row = $tiyatrolar->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['ad']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['tur']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['tarih']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['sehir']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['kontenjan']) . "</td>";
                                    echo "<td>
                                          <a href='admin_onay.php?tiyatro_sil=" . htmlspecialchars($row['id']) . "#tiyatrolar' class='btn btn-sm btn-danger'
                                             onclick='return confirm(\"Bu tiyatroyu sistemden çıkarmak istediğinize emin misiniz?\")'>Sil</a>
                                        </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7' class='text-center'>Henüz sisteme eklenmiş tiyatro bulunmamaktadır.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sinema Sekmesi -->
        <div class="tab-pane fade" id="cinemas" role="tabpanel" aria-labelledby="cinemas-tab">
            <div class="section" id="sinemalar">
                <h2 class="text-center">Sinema Yönetimi</h2>
                <div class="alert alert-info">
                    Bu sayfadan, TMDB API'dan gelen sinemaları sisteme ekleyebilir veya kaldırabilirsiniz. Eklediğiniz sinemalar anasayfada gösterilecektir.
                </div>

                <!-- TMDB API'dan Gelen Sinemalar -->
                <h3 class="mt-4">API'dan Gelen Sinemalar (TMDB)</h3>
                <div class="row">
                    <?php if (empty($api_sinemalar)): ?>
                        <div class="col-12">
                            <div class="alert alert-warning">
                                Sinema bulunamadı veya TMDB API bağlantısı sağlanamadı.
                                <?php if (!$film_json): ?>
                                    <br>Hata: API bağlantısı başarısız. Lütfen API anahtarınızı veya internet bağlantınızı kontrol edin.
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($api_sinemalar as $sinema):
                            $sinema_id = $sinema['id'] ?? '';
                            $sinema_adi = $sinema['title'] ?? 'Bilinmeyen Film';
                            $sinema_tur = 'Sinema'; // TMDB'den gelen verilerde tür sabit
                            $sinema_sehir = 'Antalya'; // Varsayılan şehir (TMDB'de şehir bilgisi yok)
                            $sinema_kapasite = 100; // Varsayılan kapasite (anasayfa.php ile uyumlu)
                            $sinema_onaylandi = in_array($sinema_id, $onaylanan_sinema_ids);

                            // Tarih formatlama
                            $raw_tarih = $sinema['release_date'] ?? null;
                            if ($raw_tarih) {
                                $datetime_str = $raw_tarih . "T21:00:00"; // Varsayılan saat
                                $date = new DateTime($datetime_str);
                                $tarih_formatted = $date->format('Y-m-d H:i:s');
                                $tarih_goruntu = $date->format('d F Y');
                                $saat_goruntu = $date->format('H:i');
                            } else {
                                $tarih_formatted = date('Y-m-d H:i:s');
                                $tarih_goruntu = 'Tarih mevcut değil';
                                $saat_goruntu = 'Saat mevcut değil';
                            }

                            $sinema_foto = 'images/sinema.png'; 
                        ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow-sm <?php echo $sinema_onaylandi ? 'border-success' : ''; ?>">
                                <?php if ($sinema_foto): ?>
                                    <img src="<?php echo $sinema_foto; ?>" class="card-img-top mb-3" alt="<?php echo htmlspecialchars($sinema_adi); ?>">
                                <?php endif; ?>
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?php echo htmlspecialchars($sinema_adi); ?></h5>
                                    <p class="card-text">
                                        <strong>Tür:</strong> <?php echo htmlspecialchars($sinema_tur); ?><br>
                                        <strong>Tarih:</strong> <?php echo $tarih_goruntu; ?><br>
                                        <strong>Saat:</strong> <?php echo $saat_goruntu; ?><br>
                                        <strong>Şehir:</strong> <?php echo htmlspecialchars($sinema_sehir); ?><br>
                                        <strong>Kapasite:</strong> <?php echo $sinema_kapasite ?: 'Bilinmiyor'; ?>
                                    </p>
                                    <?php if ($sinema_onaylandi): ?>
                                        <div class="alert alert-success py-1 mb-2">Ana sayfada gösteriliyor</div>
                                        <a href="admin_onay.php?sinema_sil=<?php echo htmlspecialchars($sinema_id); ?>#sinemalar" class="btn btn-danger mt-auto" 
                                           onclick="return confirm('Bu sinemayı sistemden çıkarmak istediğinize emin misiniz?')">Sistemden Çıkar</a>
                                    <?php else: ?>
                                        <form method="POST" action="admin_onay.php#sinemalar" class="mt-auto">
                                            <input type="hidden" name="etkinlik_id" value="<?php echo htmlspecialchars($sinema_id); ?>">
                                            <input type="hidden" name="ad" value="<?php echo htmlspecialchars($sinema_adi); ?>">
                                            <input type="hidden" name="tur" value="<?php echo htmlspecialchars($sinema_tur); ?>">
                                            <input type="hidden" name="tarih" value="<?php echo $tarih_formatted; ?>">
                                            <input type="hidden" name="sehir" value="<?php echo htmlspecialchars($sinema_sehir); ?>">
                                            <input type="hidden" name="kontenjan" value="<?php echo (int)$sinema_kapasite; ?>">
                                            <button type="submit" name="sinema_ekle" class="btn btn-success">Sisteme Ekle</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Sistemdeki Sinemalar Tablosu -->
                <h3 class="mt-5">Sistemdeki Sinemalar</h3>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Sinema Adı</th>
                                <th>Tür</th>
                                <th>Tarih</th>
                                <th>Şehir</th>
                                <th>Kontenjan</th>
                                <th>İşlem</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sinemalar = $baglanti->query("SELECT * FROM sinemalar ORDER BY tarih");
                            if ($sinemalar && $sinemalar->num_rows > 0) {
                                while ($row = $sinemalar->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['ad']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['tur']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['tarih']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['sehir']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['kontenjan']) . "</td>";
                                    echo "<td>
                                          <a href='admin_onay.php?sinema_sil=" . htmlspecialchars($row['id']) . "#sinemalar' class='btn btn-sm btn-danger'
                                             onclick='return confirm(\"Bu sinemayı sistemden çıkarmak istediğinize emin misiniz?\")'>Sil</a>
                                        </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7' class='text-center'>Henüz sisteme eklenmiş sinema bulunmamaktadır.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mt-4">
        <a href="admin_cikis.php" class="btn btn-danger">Çıkış Yap</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// URL hash'e göre sekme aktivasyonu
document.addEventListener('DOMContentLoaded', function() {
    let hash = window.location.hash;
    if (hash === '#konserler' || hash === '#concerts') {
        document.getElementById('concerts-tab').click();
    } else if (hash === '#announcements') {
        document.getElementById('announcements-tab').click();
    } else if (hash === '#tiyatrolar' || hash === '#theatres') {
        document.getElementById('theatres-tab').click();
    } else if (hash === '#sinemalar' || hash === '#cinemas') {
        document.getElementById('cinemas-tab').click();
    }

    // Duyuru düzenleme durumunda
    <?php if ($duzenlenecek_duyuru): ?>
    document.getElementById('announcements-tab').click();
    <?php endif; ?>
});
</script>

<?php $baglanti->close(); ?>
</body>
</html>