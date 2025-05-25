<?php 
include("baglanti.php");

// Başlangıç değerleri
$email_err = "";
$password_err = "";
$kullanici_adi_err = "";
$email = "";
$password = "";
$kullanici_adi = "";
$form_valid = true;
$success_message = "";
$error_message = "";

// Form gönderildiyse işlem yapılır
if (isset($_POST["kaydet"])) {

    // Kullanıcı adı kontrolü
    if (empty($_POST["kullanici_adi"])) {
        $kullanici_adi_err = "Kullanıcı adı boş bırakılamaz.";
        $form_valid = false;
    } else {
        $kullanici_adi = trim($_POST["kullanici_adi"]);
        $sorgu = "SELECT id FROM kullanicilar WHERE kullanici_adi = ?";
        $stmt = $baglanti->prepare($sorgu);
        $stmt->bind_param("s", $kullanici_adi);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $kullanici_adi_err = "Bu kullanıcı adı zaten alınmış.";
            $form_valid = false;
        }
        $stmt->close();
    }

    // Email kontrolü
    if (empty($_POST["email"])) {
        $email_err = "Email adresi boş bırakılamaz.";
        $form_valid = false;
    } else if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
        $email_err = "Geçersiz email adresi.";
        $form_valid = false;
    } else {
        $email = trim($_POST["email"]);
        $sorgu = "SELECT id FROM kullanicilar WHERE email = ?";
        $stmt = $baglanti->prepare($sorgu);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $email_err = "Bu email adresi zaten kayıtlı.";
            $form_valid = false;
        }
        $stmt->close();
    }

    // Parola kontrolü
    if (empty($_POST["parola"])) {
        $password_err = "Parola boş bırakılamaz.";
        $form_valid = false;
    } else {
        $password = password_hash($_POST["parola"], PASSWORD_DEFAULT);
    }

    // Tüm alanlar geçerliyse veritabanına kayıt
    if ($form_valid) {
        $ekle = "INSERT INTO kullanicilar (kullanici_adi, email, parola, onay) VALUES (?, ?, ?, 0)";
        $stmt = $baglanti->prepare($ekle);
        $stmt->bind_param("sss", $kullanici_adi, $email, $password);
        if ($stmt->execute()) {
            $success_message = "Kayıt başarılı. Onaylandıktan sonra giriş yapabilirsiniz.";
        } else {
            $error_message = "Kayıt eklenirken hata oluştu: " . $stmt->error;
        }
        $stmt->close();
        $baglanti->close();
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Üyelik Kaydı</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(to right, #6a11cb, #2575fc);
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .square-form {
            max-width: 400px;
            width: 100%;
            aspect-ratio: 4 / 6; /* En-boy oranı */
            box-shadow: 0 4px 16px rgba(0,0,0,0.2);
            background-color: #f8f9fa;
            border-radius: 12px;
            padding: 30px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .form-label {
            font-weight: 500;
        }

        .form-control {
            height: 40px;
        }

        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        .link-container {
            text-align: center;
            margin-top: 20px;
        }

        .alert {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="square-form">
        <div>
            <h4 class="text-center mb-4">Üyelik Kaydı</h4>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form action="kayit.php" method="POST">
                <div class="mb-3">
                    <label for="kullanici_adi" class="form-label">Kullanıcı Adı</label>
                    <input type="text" class="form-control <?php if(!empty($kullanici_adi_err)) echo 'is-invalid'; ?>" id="kullanici_adi" name="kullanici_adi" value="<?php echo htmlspecialchars($kullanici_adi); ?>">
                    <?php if(!empty($kullanici_adi_err)): ?>
                        <div class="invalid-feedback"><?php echo $kullanici_adi_err; ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email Adresi</label>
                    <input type="email" class="form-control <?php if(!empty($email_err)) echo 'is-invalid'; ?>" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
                    <?php if(!empty($email_err)): ?>
                        <div class="invalid-feedback"><?php echo $email_err; ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Parola</label>
                    <input type="password" class="form-control <?php if(!empty($password_err)) echo 'is-invalid'; ?>" id="password" name="parola">
                    <?php if(!empty($password_err)): ?>
                        <div class="invalid-feedback"><?php echo $password_err; ?></div>
                    <?php endif; ?>
                </div>

                <div class="d-grid gap-2 mt-3">
                    <button type="submit" name="kaydet" class="btn btn-primary">KAYIT OL</button>
                </div>
            </form>
        </div>

        <div class="link-container">
            <p class="mb-0">Hesabın var mı? <a href="login.php">Giriş Yap</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>