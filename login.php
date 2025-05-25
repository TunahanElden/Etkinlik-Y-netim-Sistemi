<?php
session_start();
include("baglanti.php");

// Kullanıcı formu hata/değişkenleri
$email = $password = $email_err = $password_err = "";

// Admin formu hata/değişkenleri
$admin_email = $admin_password = $admin_email_err = $admin_password_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $form_tipi = $_POST["form_tipi"] ?? "";

    // Kullanıcı Girişi
    if ($form_tipi === "user") {
        $email = trim($_POST["email"]);
        $password = $_POST["parola"];

        if (empty($email)) {
            $email_err = "Email adresi boş bırakılamaz.";
        }

        if (empty($password)) {
            $password_err = "Parola boş bırakılamaz.";
        }

        if (empty($email_err) && empty($password_err)) {
            $sorgu = "SELECT * FROM kullanicilar WHERE email = ? AND rol = 'user'";
            $stmt = $baglanti->prepare($sorgu);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $sonuc = $stmt->get_result();

            if ($sonuc->num_rows == 1) {
                $kullanici = $sonuc->fetch_assoc();

                if (!password_verify($password, $kullanici["parola"])) {
                    $password_err = "Parola hatalı.";
                } elseif ($kullanici["onay"] != 1) {
                    $email_err = "Hesabınız henüz onaylanmamış.";
                } elseif ($kullanici["sifre_degisti"] == 0) {
                    $_SESSION["email"] = $kullanici["email"];
                    header("Location: sifre_degistir.php");
                    exit();
                } else {
                    $_SESSION["email"] = $kullanici["email"];
                    header("Location: anasayfa.php");
                    exit();
                }
            } else {
                $email_err = "Bu email adresine ait kullanıcı bulunamadı.";
            }

            $stmt->close();
        }

    // Admin Girişi
    } elseif ($form_tipi === "admin") {
        $admin_email = trim($_POST["email"]);
        $admin_password = $_POST["parola"];

        if (empty($admin_email)) {
            $admin_email_err = "Email adresi boş bırakılamaz.";
        }

        if (empty($admin_password)) {
            $admin_password_err = "Parola boş bırakılamaz.";
        }

        if (empty($admin_email_err) && empty($admin_password_err)) {
            $sorgu = "SELECT * FROM kullanicilar WHERE email = ? AND rol = 'admin'";
            $stmt = $baglanti->prepare($sorgu);
            $stmt->bind_param("s", $admin_email);
            $stmt->execute();
            $sonuc = $stmt->get_result();

            if ($sonuc->num_rows == 1) {
                $admin = $sonuc->fetch_assoc();

                if (!password_verify($admin_password, $admin["parola"])) {
                    $admin_password_err = "Parola hatalı.";
                } else {
                    $_SESSION["admin_email"] = $admin["email"];
                    $_SESSION["admin_giris"] = true;
                    header("Location: admin_onay.php");
                    exit();
                }
            } else {
                $admin_email_err = "Yönetici hesabı bulunamadı.";
            }

            $stmt->close();
        }
    }

    $baglanti->close();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Giriş Sayfası</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(to top,rgb(145, 82, 212),rgb(80, 140, 243)); /* Mor-mavi geçiş */
      
      min-height: 100vh;
      margin: 0;
      padding: 0;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
    }

    .logo-container {
      text-align: center;
      margin-bottom: 20px;
    }

    .logo-container h1 {
      color: #fff;
      font-size: 2.5rem;
      font-weight: bold;
      margin-top: 10px;
    }

    .login-container {
      display: flex;
      justify-content: center;
      align-items: center;
      width: 100%;
    }

    .login-card {
      width: 90%;
      max-width: 800px;
      display: flex;
      background-color: transparent;
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    }

    .login-left, .login-right {
      flex: 1;
      padding: 30px;
      background-color: #fff;
      border-radius: 15px;
      margin: 10px;
    }

    .form-title {
      text-align: center;
      margin-bottom: 20px;
      font-weight: 600;
    }

    .form-control {
      border-radius: 8px;
      padding: 10px;
    }

    .btn-primary {
      background-color: #007bff;
      border: none;
      border-radius: 8px;
      padding: 10px;
      font-weight: 600;
    }

    .btn-dark {
      background-color: #343a40;
      border: none;
      border-radius: 8px;
      padding: 10px;
      font-weight: 600;
    }

    .text-center a {
      color: #007bff;
      text-decoration: none;
    }

    .text-center a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

<div class="logo-container">
  <h1>HOŞ GELDİNİZ</h1>
</div>

<div class="login-container">
  <div class="login-card">

    <!-- Kullanıcı Girişi -->
    <div class="login-left">
      <h3 class="form-title">KULLANICI GİRİŞİ</h3>
      <form action="login.php" method="POST">
        <input type="hidden" name="form_tipi" value="user">

        <div class="mb-3">
          <label for="email" class="form-label">Email Adresi</label>
          <input type="email" class="form-control <?php if($email_err) echo 'is-invalid'; ?>" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="Email Adresi">
          <div class="invalid-feedback"><?php echo $email_err; ?></div>
        </div>

        <div class="mb-3">
          <label for="password" class="form-label">Parola</label>
          <input type="password" class="form-control <?php if($password_err) echo 'is-invalid'; ?>" id="password" name="parola" placeholder="Parola">
          <div class="invalid-feedback"><?php echo $password_err; ?></div>
        </div>

        <div class="text-center">
          <button type="submit" name="giris" class="btn btn-primary w-100">GİRİŞ YAP</button>
        </div>

        <div class="text-center mt-3">
          <p>Hesabın yok mu? <a href="kayit.php">Kayıt ol</a></p>
        </div>
      </form>
    </div>

    <!-- Admin Girişi -->
    <div class="login-right">
      <h3 class="form-title">YÖNETİCİ GİRİŞİ</h3>
      <form action="login.php" method="POST">
        <input type="hidden" name="form_tipi" value="admin">

        <div class="mb-3">
          <label for="admin_email" class="form-label">Email</label>
          <input type="email" class="form-control <?php if($admin_email_err) echo 'is-invalid'; ?>" id="admin_email" name="email" value="<?php echo htmlspecialchars($admin_email); ?>" placeholder="Email">
          <div class="invalid-feedback"><?php echo $admin_email_err; ?></div>
        </div>

        <div class="mb-3">
          <label for="admin_password" class="form-label">Parola</label>
          <input type="password" class="form-control <?php if($admin_password_err) echo 'is-invalid'; ?>" id="admin_password" name="parola" placeholder="Parola">
          <div class="invalid-feedback"><?php echo $admin_password_err; ?></div>
        </div>

        <div class="text-center">
          <button type="submit" name="giris" class="btn btn-dark w-100">GİRİŞ YAP</button>
        </div>
      </form>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>