<?php
session_start();
include("baglanti.php");

$email_err = $password_err = "";
$email = $password = "";

if (isset($_POST["giris"])) {
    if (empty($_POST["email"])) {
        $email_err = "Email gerekli";
    } else {
        $email = trim($_POST["email"]);
    }

    if (empty($_POST["parola"])) {
        $password_err = "Parola gerekli";
    } else {
        $password = $_POST["parola"];
    }

    if (empty($email_err) && empty($password_err)) {
        $stmt = $baglanti->prepare("SELECT * FROM kullanicilar WHERE email = ? AND rol = 'admin'");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $sonuc = $stmt->get_result();

        if ($sonuc->num_rows == 1) {
            $admin = $sonuc->fetch_assoc();
            if (password_verify($password, $admin["parola"])) {
                $_SESSION["admin_giris"] = true;
                $_SESSION["admin_email"] = $admin["email"];
                header("Location: admin_onay.php");
                exit();
            } else {
                $password_err = "Parola hatalı";
            }
        } else {
            $email_err = "Admin hesabı bulunamadı";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Admin Giriş</title>
</head>
<body>
    <h2>Yönetici Girişi</h2>
    <form action="admin_giris.php" method="post">
        <label>Email:</label><br>
        <input type="text" name="email" value="<?php echo htmlspecialchars($email); ?>"><br>
        <span style="color:red;"><?php echo $email_err; ?></span><br>

        <label>Parola:</label><br>
        <input type="password" name="parola"><br>
        <span style="color:red;"><?php echo $password_err; ?></span><br><br>

        <input type="submit" name="giris" value="Giriş Yap">
    </form>
</body>
</html>