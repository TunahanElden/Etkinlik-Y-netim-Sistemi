<?php
session_start();
include("baglanti.php");

// Oturum kontrolü
if (!isset($_SESSION["email"])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION["email"];
$kullanici_adi = $_SESSION["kullanici_adi"] ?? null;
// Kullanıcı adını veritabanından al
if (!$kullanici_adi) {
    $stmt = $baglanti->prepare("SELECT kullanici_adi FROM kullanicilar WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $kullanici_adi = $row['kullanici_adi'];
        $_SESSION["kullanici_adi"] = $kullanici_adi;
    } else {
        $kullanici_adi = 'Kullanıcı';
    }
    $stmt->close();
}

// Sepet başlatma (eğer yoksa)
if (!isset($_SESSION["sepet"])) {
    $_SESSION["sepet"] = [];
}

// Sıralama parametrelerini al ve doğrula
$siralama_konser = isset($_GET['siralama_konser']) && in_array($_GET['siralama_konser'], ['en-yakin', 'en-uzak']) ? $_GET['siralama_konser'] : 'en-yakin';
$siralama_tiyatro = isset($_GET['siralama_tiyatro']) && in_array($_GET['siralama_tiyatro'], ['en-yakin', 'en-uzak']) ? $_GET['siralama_tiyatro'] : 'en-yakin';
$siralama_sinema = isset($_GET['siralama_sinema']) && in_array($_GET['siralama_sinema'], ['en-yakin', 'en-uzak']) ? $_GET['siralama_sinema'] : 'en-yakin';

// Her etkinlik türü için ORDER BY belirle
$orderBy_konser = $siralama_konser === 'en-yakin' ? 'ORDER BY tarih ASC' : 'ORDER BY tarih DESC';
$orderBy_tiyatro = $siralama_tiyatro === 'en-yakin' ? 'ORDER BY tarih ASC' : 'ORDER BY tarih DESC';
$orderBy_sinema = $siralama_sinema === 'en-yakin' ? 'ORDER BY tarih ASC' : 'ORDER BY tarih DESC';

// Duyuruları veritabanından çek
$duyurular = $baglanti->query("SELECT baslik, icerik FROM duyurular ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Ana Sayfa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        html {
            scroll-behavior: smooth; /* Yumuşak kaydırma */
        }
        body {
            background: linear-gradient(to top,rgb(159, 112, 209),rgb(112, 162, 248)); /* Mor-mavi geçiş */
            margin: 0;
            padding: 0;
        }
        .header-container {
            position: relative;
            padding: 20px 0;
        }
        .profil-kutusu {
            position: absolute;
            top: 20px;
            right: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px; /* Profil ve sepet butonları arasında boşluk */
        }
        .profil-kutusu .btn {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 8px 12px;
            font-size: 1.2rem;
            color: #333;
        }
        .profil-kutusu .btn:hover {
            background-color: #e9ecef;
        }
        .dropdown-menu {
            min-width: 200px;
        }
        .event-options {
            display: flex;
            justify-content: center;
            padding: 40px;
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .card-option {
            flex: 1;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            color: #333;
            background-color: white;
            transition: transform 0.2s;
        }
        .card-option:hover {
            transform: translateY(-5px);
        }
        .card-option h2 {
            margin-top: 0;
            font-size: 24px;
        }
        .card-option p {
            font-size: 16px;
            margin: 20px 0;
        }
        .card-option a {
            color: #0056d2;
            text-decoration: none;
            font-weight: bold;
        }
        .section {
            padding: 20px 0;
            min-height: 200px;
        }
        .duyuru-kart {
            background-color: #e0f7fa;
            border: 1px solid #00acc1;
            border-radius: 10px;
            padding: 20px;
            margin: 20px auto;
            width: 80%;
            max-width: 600px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .duyuru-baslik {
            font-size: 1.5rem;
            font-weight: bold;
            color: #006064;
            margin-bottom: 10px;
        }
        .duyuru-icerik {
            font-size: 1.1rem;
            color: #004d40;
        }
        .oneri-kart {
            background-color: #ffffff;
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 15px;
            margin-bottom: 20px;
            transition: transform 0.2s;
            height: 420px; /* Sabit yükseklik */
            display: flex;
            flex-direction: column;
        }
        .oneri-kart:hover {
            transform: translateY(-5px);
        }
        .oneri-kart .card-img-top {
            height: 180px;
            object-fit: cover;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }
        .oneri-kart .card-body {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .oneri-kart .card-title {
            font-size: 1.2rem;
            color: #333;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap; /* Uzun başlıklar taşarsa keser */
            margin-bottom: 10px;
        }
        .oneri-kart .card-text {
            flex-grow: 1;
            margin: 5px 0;
            color: #666;
        }
        .oneri-kart .btn {
            background-color: #007bff;
            border: none;
            padding: 8px 16px;
            font-size: 0.9rem;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .oneri-kart .btn:hover {
            background-color: #0056b3;
        }
        .bilet-secim {
            display: none;
            margin-top: 10px;
            align-items: center;
            gap: 10px;
        }
        .bilet-secim button {
            padding: 5px 10px;
            font-size: 1rem;
        }
        .bilet-secim span {
            font-size: 1rem;
            font-weight: bold;
        }
        .hava-durumu-baslik {
            font-size: 2rem; /* Başlık boyutu */
        }
        .hava-durumu-bilgi {
            font-size: 1.5rem; /* Hava durumu bilgisi ve öneri boyutu */
        }
        .siralama-container {
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="header-container">
            <h2>Hoş geldiniz, <?php echo htmlspecialchars($kullanici_adi); ?>!</h2>

            <!-- Profil ve Sepet Kutusu -->
            <div class="profil-kutusu">
                <div class="dropdown">
                    <button class="btn dropdown-toggle" type="button" id="profilMenu" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i> <!-- Kullanıcı simgesi -->
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profilMenu">
                        <?php
                        if (isset($_SESSION["email"])) {
                            echo '<li><a class="dropdown-item" href="profil_bilgiler.php">Kullanıcı Bilgilerim</a></li>';
                            echo '<li><a class="dropdown-item" href="ilgi_alanlari.php">İlgi Alanlarım</a></li>';
                            echo '<li><hr class="dropdown-divider"></li>';
                            echo '<li><a class="dropdown-item text-danger" href="cikis.php">Çıkış Yap</a></li>';
                        } else {
                            echo '<li><a class="dropdown-item" href="login.php">Giriş Yap</a></li>';
                        }
                        ?>
                    </ul>
                </div>
                <a href="sepet.php" class="btn" title="Sepet">
                    <i class="bi bi-cart"></i> <!-- Sepet simgesi -->
                </a>
            </div>
        </div>

        <div class="text-center mt-5">
            <!-- Duyurular -->
            <h2 style="text-align: center;">Duyurular</h2>
            <?php
            if ($duyurular === false) {
                echo "<p>Duyurular alınırken bir hata oluştu: " . htmlspecialchars($baglanti->error) . "</p>";
            } elseif ($duyurular->num_rows > 0) {
                while ($duyuru = $duyurular->fetch_assoc()) {
                    echo "<div class='duyuru-kart'>
                            <div class='duyuru-baslik'>" . htmlspecialchars($duyuru["baslik"]) . "</div>
                            <div class='duyuru-icerik'>" . nl2br(htmlspecialchars($duyuru["icerik"])) . "</div>
                          </div>";
                }
            } else {
                echo "<p style='text-align: center;'>Henüz duyuru yok.</p>";
            }
            ?>

            <!-- İlgi Alanlarına Göre Öneri -->
            <h3 class="mt-4">Sana Özel Etkinlik Önerileri</h3>
            <div class="row">
            <?php
            $ilgi = $baglanti->prepare("SELECT tur FROM ilgi_alanlari WHERE kullanici_email = ?");
            $ilgi->bind_param("s", $email);
            $ilgi->execute();
            $result = $ilgi->get_result();
            $turler = [];
            while ($row = $result->fetch_assoc()) {
                $turler[] = strtolower($row['tur']);
            }
            $ilgi->close();

            if (!empty($turler)) {
                // Konser önerileri
                if (in_array('konser', $turler)) {
                    echo "<h4 class='mt-3'>İlginizi Çekebilecek Konserler</h4>";
                    echo "<div class='siralama-container'>
                            <label for='siralama_konser_oneri' class='form-label'>Konserleri Sırala:</label>
                            <select id='siralama_konser_oneri' class='form-select' onchange=\"siralamaDegistir('konser', this)\">
                                <option value='en-yakin' " . ($siralama_konser == 'en-yakin' ? 'selected' : '') . ">En Yakın Etkinlik Tarihi</option>
                                <option value='en-uzak' " . ($siralama_konser == 'en-uzak' ? 'selected' : '') . ">En Uzak Etkinlik Tarihi</option>
                            </select>
                          </div>";
                    $konserler = $baglanti->query("SELECT * FROM konserler $orderBy_konser LIMIT 3");
                    if ($konserler === false) {
                        echo "<p>Konserler alınırken bir hata oluştu: " . htmlspecialchars($baglanti->error) . "</p>";
                    } elseif ($konserler->num_rows > 0) {
                        while ($konser = $konserler->fetch_assoc()) {
                            $etkinlikId = $konser['id'] ?? '';
                            $tarihFormatted = 'Belirtilmemiş';
                            $saatFormatted = 'Belirtilmemiş';
                            if (!empty($konser['tarih'])) {
                                try {
                                    $date = new DateTime($konser['tarih']);
                                    $tarihFormatted = $date->format('d F Y');
                                    $saatFormatted = $date->format('H:i');
                                } catch (Exception $e) {
                                    $tarihFormatted = 'Geçersiz Tarih';
                                    $saatFormatted = 'Geçersiz Saat';
                                }
                            }
                            $kontenjan = $konser['kontenjan'] ?? 100;
                            $fiyat = $konser['fiyat'] ?? 100; // Varsayılan fiyat

                            // images klasöründen alıyoruz.
                            $etkinlikFoto = 'images/konser.png'; // Varsayılan fotoğraf

                            echo "<div class='col-md-4'>
                                    <div class='oneri-kart' data-etkinlik-id='$etkinlikId' data-fiyat='$fiyat' data-tur='konser'>
                                        <img src='$etkinlikFoto' class='card-img-top' alt='" . htmlspecialchars($konser['ad']) . "'>
                                        <div class='card-body'>
                                            <h5 class='card-title'>" . htmlspecialchars($konser['ad']) . "</h5>
                                            <p class='card-text'>
                                                " . htmlspecialchars($konser['sehir']) . " - $tarihFormatted $saatFormatted<br>
                                                Kontenjan: <span class='kontenjan'>$kontenjan</span><br>
                                                ücret: <strong>" . htmlspecialchars($konser['fiyat']) . " TL</strong>
                                            </p>
                                            <button class='btn btn-primary bilet-al-btn'>Bilet Al</button>
                                            <div class='bilet-secim'>
                                                <button class='btn btn-outline-secondary decrease'>-</button>
                                                <span class='bilet-sayisi'>0</span>
                                                <button class='btn btn-outline-secondary increase'>+</button>
                                                <button class='btn btn-success sepete-ekle'>Sepete Ekle</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>";
                        }
                    } else {
                        echo "<p>Şu anda konser bulunamadı.</p>";
                    }
                }

                // Tiyatro önerileri
                if (in_array('tiyatro', $turler)) {
                    echo "<h4 class='mt-3'>İlginizi Çekebilecek Tiyatro Oyunları</h4>";
                    echo "<div class='siralama-container'>
                            <label for='siralama_tiyatro_oneri' class='form-label'>Tiyatroları Sırala:</label>
                            <select id='siralama_tiyatro_oneri' class='form-select' onchange=\"siralamaDegistir('tiyatro', this)\">
                                <option value='en-yakin' " . ($siralama_tiyatro == 'en-yakin' ? 'selected' : '') . ">En Yakın Etkinlik Tarihi</option>
                                <option value='en-uzak' " . ($siralama_tiyatro == 'en-uzak' ? 'selected' : '') . ">En Uzak Etkinlik Tarihi</option>
                            </select>
                          </div>";
                    $tiyatrolar = $baglanti->query("SELECT * FROM tiyatrolar $orderBy_tiyatro LIMIT 3");
                    if ($tiyatrolar === false) {
                        echo "<p>Tiyatrolar alınırken bir hata oluştu: " . htmlspecialchars($baglanti->error) . "</p>";
                    } elseif ($tiyatrolar->num_rows > 0) {
                        while ($tiyatro = $tiyatrolar->fetch_assoc()) {
                            $etkinlikId = $tiyatro['id'] ?? '';
                            $tarihFormatted = 'Belirtilmemiş';
                            $saatFormatted = 'Belirtilmemiş';
                            if (!empty($tiyatro['tarih'])) {
                                try {
                                    $date = new DateTime($tiyatro['tarih']);
                                    $tarihFormatted = $date->format('d F Y');
                                    $saatFormatted = $date->format('H:i');
                                } catch (Exception $e) {
                                    $tarihFormatted = 'Geçersiz Tarih';
                                    $saatFormatted = 'Geçersiz Saat';
                                }
                            }
                            $kontenjan = $tiyatro['kontenjan'] ?? 100;
                            $fiyat = $tiyatro['fiyat'] ?? 80; // Varsayılan fiyat

                            $etkinlikFoto = 'images/tiyatro.png'; // Varsayılan fotoğraf

                            echo "<div class='col-md-4'>
                                    <div class='oneri-kart' data-etkinlik-id='$etkinlikId' data-fiyat='$fiyat' data-tur='tiyatro'>
                                        <img src='$etkinlikFoto' class='card-img-top' alt='" . htmlspecialchars($tiyatro['ad']) . "'>
                                        <div class='card-body'>
                                            <h5 class='card-title'>" . htmlspecialchars($tiyatro['ad']) . "</h5>
                                            <p class='card-text'>
                                                " . htmlspecialchars($tiyatro['sehir']) . " - $tarihFormatted $saatFormatted<br>
                                                Kontenjan: <span class='kontenjan'>$kontenjan</span><br>
                                                ücret: <strong>" . htmlspecialchars($tiyatro['fiyat']) . " TL</strong>
                                            </p>
                                            <button class='btn btn-primary bilet-al-btn'>Bilet Al</button>
                                            <div class='bilet-secim'>
                                                <button class='btn btn-outline-secondary decrease'>-</button>
                                                <span class='bilet-sayisi'>0</span>
                                                <button class='btn btn-outline-secondary increase'>+</button>
                                                <button class='btn btn-success sepete-ekle'>Sepete Ekle</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>";
                        }
                    } else {
                        echo "<p>Şu anda tiyatro oyunu bulunamadı.</p>";
                    }
                }

                // Sinema önerileri
                if (in_array('sinema', $turler)) {
                    echo "<h4 class='mt-3'>İlginizi Çekebilecek Sinema Filmleri</h4>";
                    echo "<div class='siralama-container'>
                            <label for='siralama_sinema_oneri' class='form-label'>Sinemaları Sırala:</label>
                            <select id='siralama_sinema_oneri' class='form-select' onchange=\"siralamaDegistir('sinema', this)\">
                                <option value='en-yakin' " . ($siralama_sinema == 'en-yakin' ? 'selected' : '') . ">En Yakın Etkinlik Tarihi</option>
                                <option value='en-uzak' " . ($siralama_sinema == 'en-uzak' ? 'selected' : '') . ">En Uzak Etkinlik Tarihi</option>
                            </select>
                          </div>";
                    $sinemalar = $baglanti->query("SELECT * FROM sinemalar $orderBy_sinema LIMIT 3");
                    if ($sinemalar === false) {
                        echo "<p>Sinemalar alınırken bir hata oluştu: " . htmlspecialchars($baglanti->error) . "</p>";
                    } elseif ($sinemalar->num_rows > 0) {
                        while ($sinema = $sinemalar->fetch_assoc()) {
                            $etkinlikId = $sinema['id'] ?? '';
                            $tarihFormatted = 'Belirtilmemiş';
                            $saatFormatted = 'Belirtilmemiş';
                            if (!empty($sinema['tarih'])) {
                                try {
                                    $date = new DateTime($sinema['tarih']);
                                    $tarihFormatted = $date->format('d F Y');
                                    $saatFormatted = $date->format('H:i');
                                } catch (Exception $e) {
                                    $tarihFormatted = 'Geçersiz Tarih';
                                    $saatFormatted = 'Geçersiz Saat';
                                }
                            }
                            $kontenjan = $sinema['kontenjan'] ?? 100;
                            $fiyat = $sinema['fiyat'] ?? 50; // NULL ise varsayılan 50 TL

                            $etkinlikFoto = 'images/sinema.png'; // Varsayılan fotoğraf

                            echo "<div class='col-md-4'>
                                    <div class='oneri-kart' data-etkinlik-id='$etkinlikId' data-fiyat='$fiyat' data-tur='sinema'>
                                        <img src='$etkinlikFoto' class='card-img-top' alt='" . htmlspecialchars($sinema['ad']) . "'>
                                        <div class='card-body'>
                                            <h5 class='card-title'>" . htmlspecialchars($sinema['ad']) . "</h5>
                                            <p class='card-text'>
                                                " . htmlspecialchars($sinema['sehir']) . " - $tarihFormatted $saatFormatted<br>
                                                Kontenjan: <span class='kontenjan'>$kontenjan</span><br>
                                                ücret: <strong>" . htmlspecialchars($sinema['fiyat']) . " TL</strong>
                                            </p>
                                            <button class='btn btn-primary bilet-al-btn'>Bilet Al</button>
                                            <div class='bilet-secim'>
                                                <button class='btn btn-outline-secondary decrease'>-</button>
                                                <span class='bilet-sayisi'>0</span>
                                                <button class='btn btn-outline-secondary increase'>+</button>
                                                <button class='btn btn-success sepete-ekle'>Sepete Ekle</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>";
                        }
                    } else {
                        echo "<p>Şu anda sinema filmi bulunamadı.</p>";
                    }
                }
            } else {
                echo "<p>Henüz ilgi alanı bilgisi vermediniz. <a href='ilgi_alanlari.php'>Buradan ekleyebilirsiniz.</a></p>";
            }
            ?>
            </div>

            <!-- Hava Durumu -->
            <h3 class="mt-4 hava-durumu-baslik">Hava Durumu</h3>
            <?php
            $apiKey = "6d74aa4bf75e44e7b28114728252804"; // WeatherAPI anahtarı
            $sehir = "Antalya";
            $apiUrl = "https://api.weatherapi.com/v1/current.json?key=$apiKey&q=$sehir&lang=tr";

            // Varsayılan değerler
            $sicaklik = null;
            $havaDurumu = "Bilinmiyor";
            $uyariMesaji = "";

            // cURL ile API isteği yap
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Geliştirme ortamı için SSL doğrulamasını kapat
            $hava_json = curl_exec($ch);
            $curl_error = curl_error($ch);
            curl_close($ch);

            if ($hava_json === false) {
                echo "<p class='hava-durumu-bilgi'>Hava durumu bilgisi alınamadı. cURL Hatası: " . htmlspecialchars($curl_error) . "</p>";
            } else {
                $hava = json_decode($hava_json, true);
                if (isset($hava['current'])) {
                    $havaDurumu = strtolower($hava['current']['condition']['text']);
                    $sicaklik = round($hava['current']['temp_c']);

                    echo "<p class='hava-durumu-bilgi'>{$sehir} için hava: " . htmlspecialchars($havaDurumu) . ", " . $sicaklik . "°C</p>";

                    $olumsuzKosullar = ['yağmur', 'fırtına', 'kar', 'sis', 'dolu'];
                    $etkinlikUygun = true;

                    foreach ($olumsuzKosullar as $kosul) {
                        if (strpos($havaDurumu, $kosul) !== false) {
                            $etkinlikUygun = false;
                            $uyariMesaji = "Hava durumu nedeniyle etkinlik planlamak riskli olabilir ($kosul bekleniyor).";
                            break;
                        }
                    }

                    if ($etkinlikUygun && $sicaklik !== null) {
                        if ($sicaklik < 0) {
                            $etkinlikUygun = false;
                            $uyariMesaji = "Sıcaklık çok düşük ($sicaklik °C). Etkinlik planlamak için uygun değil.";
                        } elseif ($sicaklik > 35) {
                            $etkinlikUygun = false;
                            $uyariMesaji = "Sıcaklık çok yüksek ($sicaklik °C). Etkinlik planlamak için uygun değil.";
                        } elseif ($sicaklik < 10 || $sicaklik > 35) {
                            $uyariMesaji = "Sıcaklık ($sicaklik °C) etkinlik için ideal değil, dikkatli olun.";
                        }
                    }

                    if ($etkinlikUygun && empty($uyariMesaji)) {
                        echo "<p class='hava-durumu-bilgi' style='color: green;'>Etkinlik planlamak için hava durumu uygun.</p>";
                    } else {
                        echo "<p class='hava-durumu-bilgi' style='color: red;'>$uyariMesaji</p>";
                    }
                } else {
                    $hataMesaji = isset($hava['error']['message']) ? $hava['error']['message'] : 'Bilinmeyen hata';
                    echo "<p class='hava-durumu-bilgi'>Hava durumu bilgisi alınamadı. API hatası: " . htmlspecialchars($hataMesaji) . "</p>";
                    echo "<!-- API Yanıtı: " . htmlspecialchars($hava_json) . " -->";
                }
            }
            ?>
        </div>

        <!-- Etkinlik Seçenekleri (Konser, Tiyatro, Sinema) -->
        <div class="event-options">
            <div class="card-option">
                <h2>Konser</h2>
                <p>En sevdiğiniz sanatçıların canlı performanslarını keşfedin ve unutulmaz bir müzik deneyimi yaşayın.</p>
                <a href="#konser" onclick="scrollToSection('konser')">Daha Fazla Bilgi →</a>
            </div>
            <div class="card-option">
                <h2>Tiyatro</h2>
                <p>Sahne sanatlarının büyüsüne kapılın, en iyi tiyatro oyunlarıyla kültürel bir yolculuğa çıkın.</p>
                <a href="#tiyatro" onclick="scrollToSection('tiyatro')">Daha Fazla Bilgi →</a>
            </div>
            <div class="card-option">
                <h2>Sinema</h2>
                <p>En yeni filmleri izleyin, sinema keyfini doyasıya yaşayın.</p>
                <a href="#sinema" onclick="scrollToSection('sinema')">Daha Fazla Bilgi →</a>
            </div>
        </div>

        <!-- Konser Bölümü -->
        <div class="section" id="konser">
            <h4 class="mt-4">Konserler</h4>
            <div class="siralama-container">
                <label for="siralama_konser_main" class="form-label">Konserleri Sırala:</label>
                <select id="siralama_konser_main" class="form-select" onchange="siralamaDegistir('konser', this)">
                    <option value="en-yakin" <?php echo $siralama_konser == 'en-yakin' ? 'selected' : ''; ?>>En Yakın Etkinlik Tarihi</option>
                    <option value="en-uzak" <?php echo $siralama_konser == 'en-uzak' ? 'selected' : ''; ?>>En Uzak Etkinlik Tarihi</option>
                </select>
            </div>
            <div class="row">
            <?php
            $konserler_sorgu = $baglanti->query("SELECT * FROM konserler $orderBy_konser");
            if ($konserler_sorgu === false) {
                echo "<p>Konserler alınırken bir hata oluştu: " . htmlspecialchars($baglanti->error) . "</p>";
            } elseif ($konserler_sorgu->num_rows > 0) {
                while ($konser = $konserler_sorgu->fetch_assoc()) {
                    $etkinlikId = $konser['id'] ?? '';
                    $etkinlikAdi = $konser['ad'];
                    $etkinlikTur = $konser['tur'];
                    $etkinlikTarih = $konser['tarih'];
                    $etkinlikSehir = $konser['sehir'];
                    $etkinlikKontenjan = $konser['kontenjan'] ?? 100;
                    $etkinlikFiyat = $konser['fiyat'] ?? 100;

                    $etkinlikFoto = 'images/konser.png'; // Varsayılan fotoğraf

                    $tarihFormatted = 'Belirtilmemiş'; //Eğer tarih bilgisi varsa (!empty()), DateTime ile formatlanır ve $tarihFormatted ile $saatFormatted güncellenir.
                                                    // Eğer tarih bilgisi yoksa veya formatlama başarısız olursa (örneğin hata yakalanırsa), varsayılan 'Belirtilmemiş' değeri korunur veya hata mesajıyla değiştirilir.
                    $saatFormatted = 'Belirtilmemiş';
                    if (!empty($etkinlikTarih)) {
                        try {
                            $date = new DateTime($etkinlikTarih);
                            $tarihFormatted = $date->format('d F Y');
                            $saatFormatted = $date->format('H:i');
                        } catch (Exception $e) {
                            $tarihFormatted = 'Geçersiz Tarih';
                            $saatFormatted = 'Geçersiz Saat';
                        }
                    }

                    echo "
                    <div class='col-md-4 mb-4'>
                        <div class='oneri-kart' data-etkinlik-id='$etkinlikId' data-fiyat='$etkinlikFiyat' data-tur='konser'>
                            <img src='$etkinlikFoto' class='card-img-top' alt='" . htmlspecialchars($etkinlikAdi) . "'>
                            <div class='card-body'>
                                <h5 class='card-title'>" . htmlspecialchars($etkinlikAdi) . "</h5>
                                <p class='card-text'>
                                    Gün: $tarihFormatted<br>
                                    Saat: $saatFormatted<br>
                                    Şehir: " . htmlspecialchars($etkinlikSehir) . "<br>
                                    Kapasite: <span class='kontenjan'>" . ($etkinlikKontenjan > 0 ? "$etkinlikKontenjan" : "Belirtilmemiş") . "</span> <br>
                                    ücret:<strong> $etkinlikFiyat TL </strong>
                                </p>
                                <button class='btn btn-primary bilet-al-btn'>Bilet Al</button>
                                <div class='bilet-secim'>
                                    <button class='btn btn-outline-secondary decrease'>-</button>
                                    <span class='bilet-sayisi'>0</span>
                                    <button class='btn btn-outline-secondary increase'>+</button>
                                    <button class='btn btn-success sepete-ekle'>Sepete Ekle</button>
                                </div>
                            </div>
                        </div>
                    </div>";
                }
            } else {
                echo "<p>Şu anda gösterilecek konser bulunmamaktadır.</p>";
            }
            ?>
            </div>
        </div>

        <!-- Tiyatro Bölümü -->
        <div class="section" id="tiyatro">
            <h4 class="mt-4">Tiyatro Oyunları</h4>
            <div class="siralama-container">
                <label for="siralama_tiyatro_main" class="form-label">Tiyatroları Sırala:</label>
                <select id="siralama_tiyatro_main" class="form-select" onchange="siralamaDegistir('tiyatro', this)">
                    <option value="en-yakin" <?php echo $siralama_tiyatro == 'en-yakin' ? 'selected' : ''; ?>>En Yakın Etkinlik Tarihi</option>
                    <option value="en-uzak" <?php echo $siralama_tiyatro == 'en-uzak' ? 'selected' : ''; ?>>En Uzak Etkinlik Tarihi</option>
                </select>
            </div>
            <div class="row">
            <?php
            $tiyatrolar_sorgu = $baglanti->query("SELECT * FROM tiyatrolar $orderBy_tiyatro");
            if ($tiyatrolar_sorgu === false) {
                echo "<p>Tiyatrolar alınırken bir hata oluştu: " . htmlspecialchars($baglanti->error) . "</p>";
            } elseif ($tiyatrolar_sorgu->num_rows > 0) {
                while ($tiyatro = $tiyatrolar_sorgu->fetch_assoc()) {
                    $etkinlikId = $tiyatro['id'] ?? '';
                    $etkinlikAdi = $tiyatro['ad'];
                    $etkinlikTur = $tiyatro['tur'];
                    $etkinlikTarih = $tiyatro['tarih'];
                    $etkinlikSehir = $tiyatro['sehir'];
                    $etkinlikKontenjan = $tiyatro['kontenjan'] ?? 100;
                    $etkinlikFiyat = $tiyatro['fiyat'] ?? 80;

                    $etkinlikFoto = 'images/tiyatro.png'; // Varsayılan fotoğraf

                    $tarihFormatted = 'Belirtilmemiş';
                    $saatFormatted = 'Belirtilmemiş';
                    if (!empty($etkinlikTarih)) {
                        try {
                            $date = new DateTime($etkinlikTarih);
                            $tarihFormatted = $date->format('d F Y');
                            $saatFormatted = $date->format('H:i');
                        } catch (Exception $e) {
                            $tarihFormatted = 'Geçersiz Tarih';
                            $saatFormatted = 'Geçersiz Saat';
                        }
                    }

                    echo "
                    <div class='col-md-4 mb-4'>
                        <div class='oneri-kart' data-etkinlik-id='$etkinlikId' data-fiyat='$etkinlikFiyat' data-tur='tiyatro'>
                            <img src='$etkinlikFoto' class='card-img-top' alt='" . htmlspecialchars($etkinlikAdi) . "'>
                            <div class='card-body'>
                                <h5 class='card-title'>" . htmlspecialchars($etkinlikAdi) . "</h5>
                                <p class='card-text'>
                                    Gün: $tarihFormatted<br>
                                    Saat: $saatFormatted<br>
                                    Şehir: " . htmlspecialchars($etkinlikSehir) . "<br>
                                    Kapasite: <span class='kontenjan'>" . ($etkinlikKontenjan > 0 ? "$etkinlikKontenjan" : "Belirtilmemiş") . "</span><br>
                                    ücret: <strong> $etkinlikFiyat TL </strong>
                                </p>
                                <button class='btn btn-primary bilet-al-btn'>Bilet Al</button>
                                <div class='bilet-secim'>
                                    <button class='btn btn-outline-secondary decrease'>-</button>
                                    <span class='bilet-sayisi'>0</span>
                                    <button class='btn btn-outline-secondary increase'>+</button>
                                    <button class='btn btn-success sepete-ekle'>Sepete Ekle</button>
                                </div>
                            </div>
                        </div>
                    </div>";
                }
            } else {
                echo "<p>Şu anda gösterilecek tiyatro oyunu bulunmamaktadır.</p>";
            }
            ?>
            </div>
        </div>

        <!-- Sinema Bölümü -->
        <div class="section" id="sinema">
            <h4 class="mt-4">Sinema Filmleri</h4>
            <div class="siralama-container">
                <label for="siralama_sinema_main" class="form-label">Sinemaları Sırala:</label>
                <select id="siralama_sinema_main" class="form-select" onchange="siralamaDegistir('sinema', this)">
                    <option value="en-yakin" <?php echo $siralama_sinema == 'en-yakin' ? 'selected' : ''; ?>>En Yakın Etkinlik Tarihi</option>
                    <option value="en-uzak" <?php echo $siralama_sinema == 'en-uzak' ? 'selected' : ''; ?>>En Uzak Etkinlik Tarihi</option>
                </select>
            </div>
            <div class="row">
            <?php
            $sinemalar_sorgu = $baglanti->query("SELECT * FROM sinemalar $orderBy_sinema");
            if ($sinemalar_sorgu === false) {
                echo "<p>Sinemalar alınırken bir hata oluştu: " . htmlspecialchars($baglanti->error) . "</p>";
            } elseif ($sinemalar_sorgu->num_rows > 0) {
                while ($sinema = $sinemalar_sorgu->fetch_assoc()) {
                    $etkinlikId = $sinema['id'] ?? '';
                    $etkinlikAdi = $sinema['ad'];
                    $etkinlikTur = $sinema['tur'];
                    $etkinlikTarih = $sinema['tarih'];
                    $etkinlikSehir = $sinema['sehir'];
                    $etkinlikKontenjan = $sinema['kontenjan'] ?? 100;
                    $etkinlikFiyat = $sinema['fiyat'] ?? 50; // NULL ise varsayılan 50 TL

                    $etkinlikFoto = 'images/sinema.png'; // Varsayılan fotoğraf

                    $tarihFormatted = 'Belirtilmemiş';
                    $saatFormatted = 'Belirtilmemiş';
                    if (!empty($etkinlikTarih)) {
                        try {
                            $date = new DateTime($etkinlikTarih);
                            $tarihFormatted = $date->format('d F Y');
                            $saatFormatted = $date->format('H:i');
                        } catch (Exception $e) {
                            $tarihFormatted = 'Geçersiz Tarih';
                            $saatFormatted = 'Geçersiz Saat';
                        }
                    }

                    echo "
                    <div class='col-md-4 mb-4'>
                        <div class='oneri-kart' data-etkinlik-id='$etkinlikId' data-fiyat='$etkinlikFiyat' data-tur='sinema'>
                            <img src='$etkinlikFoto' class='card-img-top' alt='" . htmlspecialchars($etkinlikAdi) . "'>
                            <div class='card-body'>
                                <h5 class='card-title'>" . htmlspecialchars($etkinlikAdi) . "</h5>
                                <p class='card-text'>
                                    Gün: $tarihFormatted<br>
                                    Saat: $saatFormatted<br>
                                    Şehir: " . htmlspecialchars($etkinlikSehir) . "<br>
                                    Kapasite: <span class='kontenjan'>" . ($etkinlikKontenjan > 0 ? "$etkinlikKontenjan" : "Belirtilmemiş") . "</span><br>
                                    ücret: <strong> $etkinlikFiyat TL </strong>
                                </p>
                                <button class='btn btn-primary bilet-al-btn'>Bilet Al</button>
                                <div class='bilet-secim'>
                                    <button class='btn btn-outline-secondary decrease'>-</button>
                                    <span class='bilet-sayisi'>0</span>
                                    <button class='btn btn-outline-secondary increase'>+</button>
                                    <button class='btn btn-success sepete-ekle'>Sepete Ekle</button>
                                </div>
                            </div>
                        </div>
                    </div>";
                }
            } else {
                echo "<p>Şu anda gösterilecek sinema filmi bulunmamaktadır.</p>";
            }
            ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function scrollToSection(sectionId) {
            const element = document.getElementById(sectionId);
            if (element) {
                element.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        // Sıralama değiştiğinde URL'yi güncelle
        function siralamaDegistir(tur, element) {

            // Seçilen sıralama değerini al
            const siralama = element.value;

            if (siralama) {
                const url = new URL(window.location);
                url.searchParams.set(`siralama_${tur}`, siralama);
                window.location.href = url.toString();
            } 
            
        }

        // Bilet Al butonuna tıklama
        document.querySelectorAll('.bilet-al-btn').forEach(button => {
            button.addEventListener('click', function() {
                const kart = this.closest('.oneri-kart');
                const biletSecim = kart.querySelector('.bilet-secim');
                this.style.display = 'none'; // Bilet Al butonunu gizle
                biletSecim.style.display = 'flex'; // Artı/eksi butonlarını göster
            });
        });

        // Artı ve eksi butonlarına tıklama
        document.querySelectorAll('.oneri-kart').forEach(kart => {
            const increaseBtn = kart.querySelector('.increase');
            const decreaseBtn = kart.querySelector('.decrease');
            const biletSayisiSpan = kart.querySelector('.bilet-sayisi');
            const kontenjanSpan = kart.querySelector('.kontenjan');
            let biletSayisi = 0;
            let kontenjan = parseInt(kontenjanSpan.textContent) || 100;

            increaseBtn.addEventListener('click', function() {
                if (biletSayisi < kontenjan) {
                    biletSayisi++;
                    kontenjan--;
                    biletSayisiSpan.textContent = biletSayisi;
                    kontenjanSpan.textContent = kontenjan;
                } else {
                    alert('Kontenjan sınırı aşıldı!');
                }
            });

            decreaseBtn.addEventListener('click', function() {
                if (biletSayisi > 0) {
                    biletSayisi--;
                    kontenjan++;
                    biletSayisiSpan.textContent = biletSayisi;
                    kontenjanSpan.textContent = kontenjan;
                }
            });

        // Her etkinlik kartı için "Sepete Ekle" butonunu seç ve işlemleri tanımla
        // Sepete Ekle butonuna tıklama
        const sepeteEkleBtn = kart.querySelector('.sepete-ekle'); // Etkinlik kartındaki "Sepete Ekle" butonunu bul ve değişkene ata
        sepeteEkleBtn.addEventListener('click', function() { // Butona tıklama olayı ekle, tıklandığında aşağıdaki fonksiyon çalışsın
            // Kullanıcının seçtiği bilet sayısını kontrol et
            if (biletSayisi > 0) { // Eğer bilet sayısı 0'dan büyükse devam et
                // Etkinlik bilgilerini kartın özniteliklerinden al
                const etkinlikId = kart.getAttribute('data-etkinlik-id'); // Etkinliğin benzersiz kimliğini al (örneğin, 1, 2, 3)
                const tur = kart.getAttribute('data-tur'); // Etkinlik türünü al (konser, tiyatro, sinema)
                const fiyat = parseFloat(kart.getAttribute('data-fiyat')); // Etkinlik fiyatını al ve sayıya çevir
                // Etkinlik adını kartın içindeki strong etiketinden veya başlık (card-title) etiketinden al
                const etkinlikAdi = kart.querySelector('strong')?.textContent || kart.querySelector('.card-title')?.textContent; // Etkinlik adını al 
                const toplamFiyat = fiyat * biletSayisi; // Toplam fiyatı hesapla (bilet sayısı x birim fiyat)

                // AJAX ile sunucuya veri gönderme işlemi başlat
                // AJAX ile sepete ekleme
                const xhr = new XMLHttpRequest(); // Yeni bir HTTP isteği nesnesi oluştur
                xhr.open('POST', 'sepete_ekle.php', true); // POST yöntemiyle sepete_ekle.php dosyasına asenkron bir istek tanımla
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded'); // Gönderilecek verinin formatını URL-encoded olarak belirt

                // Sunucudan gelen yanıtı işlemek için olay dinleyicisi tanımla
                xhr.onreadystatechange = function() { // İsteğin durumu değiştiğinde çalışacak fonksiyon
                    if (xhr.readyState === 4) { // İstek tamamlandıysa (4: tamamlandı)
                        if (xhr.status === 200) { // Sunucu başarılı bir yanıt döndürdüyse (200 OK)
                            try { // Yanıtı JSON formatında ayrıştırmayı dene
                                const response = JSON.parse(xhr.responseText); // Sunucudan gelen JSON yanıtını bir nesneye çevir
                                if (response.status === 'success') { // Eğer işlem başarılıysa
                                    // Kullanıcıya başarı mesajı göster ve bilet sayısını sıfırla
                                    alert(`${biletSayisi} adet bilet sepete eklendi! Toplam: ${toplamFiyat} TL`); // Başarı mesajı göster
                                    biletSayisi = 0; // Bilet sayısını sıfırla
                                    biletSayisiSpan.textContent = biletSayisi; // Ekrandaki bilet sayısını güncelle
                                    kontenjanSpan.textContent = kontenjan; // Ekrandaki kontenjanı güncelle
                                } else { // Eğer işlem başarısızsa
                                    alert('Hata: ' + response.message); // Hata mesajını göster
                                }
                            } catch (e) { // Eğer JSON ayrıştırma başarısız olursa
                                alert('Yanıt işlenemedi: ' + xhr.responseText); // Hata mesajı göster, ham yanıtı ekle
                            }
                        } else { // Eğer sunucu bir hata kodu döndürdüyse (örneğin 404, 500)
                            alert('Sunucu hatası: ' + xhr.status); // Hata kodunu göster
                        }
                    }
                };

                // Sunucuya gönderilecek veriyi hazırla
                const data = `etkinlik_id=${encodeURIComponent(etkinlikId)}&tur=${encodeURIComponent(tur)}&ad=${encodeURIComponent(etkinlikAdi)}&adet=${biletSayisi}&fiyat=${fiyat}&toplam_fiyat=${toplamFiyat}`; // Veriyi URL-encoded formatta bir string olarak oluştur
                xhr.send(data); // İsteği sunucuya gönder
            } else { // Eğer bilet sayısı 0 ise
                alert('Lütfen en az 1 bilet seçin!'); // Kullanıcıya uyarı mesajı göster
            }
        });
        });
    </script>
</body>
</html>
<?php
// Veritabanı bağlantısını kapat
$baglanti->close();
?>