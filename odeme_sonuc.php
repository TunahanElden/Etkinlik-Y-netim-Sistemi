<?php
$adsoyad = $_POST['adsoyad'] ?? 'Bilinmeyen Kullanıcı';
?>

<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Ödeme Başarılı</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f2f2f2;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .message-box {
      background-color: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      text-align: center;
    }

    h2 {
      color: #28a745;
    }

    p {
      font-size: 18px;
      color: #444;
    }

    a {
      display: inline-block;
      margin-top: 20px;
      text-decoration: none;
      color: white;
      background-color: #007bff;
      padding: 10px 20px;
      border-radius: 6px;
    }

    a:hover {
      background-color: #0056b3;
    }
  </style>
</head>
<body>

  <div class="message-box">
    <h2>✅ Ödeme Başarılı!</h2>
    <p>Teşekkürler, <strong><?= htmlspecialchars($adsoyad) ?></strong>. Siparişiniz alınmıştır.</p>
    <a href="anasayfa.php">Ana Sayfaya Dön</a>
  </div>

</body>
</html>