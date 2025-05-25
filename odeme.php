<?php
session_start();
if (!isset($_SESSION["email"])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Ödeme Sayfası</title>
  <style>
    body { 
      font-family: Arial, sans-serif;
      background: #f4f4f4;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .form-container {
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      width: 350px;
    }

    h2 {
      margin-bottom: 20px;
      text-align: center;
      color: #333;
    }

    label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
      color: #444;
    }

    input[type="text"],
    input[type="number"] {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 6px;
      box-sizing: border-box;
    }

    button {
      width: 100%;
      background-color: #28a745;
      color: white;
      padding: 12px;
      border: none;
      border-radius: 6px;
      font-size: 16px;
      cursor: pointer;
    }

    button:hover {
      background-color: #218838;
    }
  </style>
</head>
<body>

  <div class="form-container">
    <h2>💳 Ödeme Bilgileri</h2>
    <form action="odeme_sonuc.php" method="POST">
      <label>Ad Soyad:</label>
      <input type="text" name="adsoyad" required>

      <label>Kredi Kartı Numarası:</label>
      <input type="text" name="kart_no" placeholder="**** **** **** ****" required>

      <label>Son Kullanma Tarihi:</label>
      <input type="text" name="skt" placeholder="MM/YY" required>

      <label>CVV:</label>
      <input type="text" name="cvv" required>

      <button type="submit">Ödemeyi Tamamla</button>
    </form>
  </div>

</body>
</html>