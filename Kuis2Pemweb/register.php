<?php
require_once "koneksi/db.php";

$nama_lengkap = $username = $password = "";
$nama_lengkap_err = $username_err = $password_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){

    if(empty(trim($_POST["nama_lengkap"]))){
        $nama_lengkap_err = "Silakan masukkan nama lengkap Anda.";
    } else {
        $nama_lengkap = trim($_POST["nama_lengkap"]);
    }

    if(empty(trim($_POST["username"]))){
        $username_err = "Silakan masukkan username.";
    } else {
        $sql = "SELECT id FROM users WHERE username = ?";
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = trim($_POST["username"]);
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                if(mysqli_stmt_num_rows($stmt) == 1){
                    $username_err = "Username ini sudah digunakan.";
                } else {
                    $username = trim($_POST["username"]);
                }
            } else {
                echo "Oops! Terjadi kesalahan. Silakan coba lagi nanti.";
            }
            mysqli_stmt_close($stmt);
        }
    }
    
    // Validasi password
    if(empty(trim($_POST["password"]))){
        $password_err = "Silakan masukkan password.";     
    } elseif(strlen(trim($_POST["password"])) < 6){
        $password_err = "Password minimal harus 6 karakter.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    $foto_profil_name = 'default.png';

    if(empty($nama_lengkap_err) && empty($username_err) && empty($password_err)){
        
        $sql = "INSERT INTO users (nama_lengkap, username, password, foto_profil) VALUES (?, ?, ?, ?)";
         
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "ssss", $param_nama, $param_username, $param_password, $param_foto);
            
            $param_nama = $nama_lengkap;
            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_DEFAULT);
            $param_foto = $foto_profil_name;
            
            if(mysqli_stmt_execute($stmt)){
                header("location: login.php");
            } else {
                echo "Oops! Terjadi kesalahan. Silakan coba lagi nanti.";
            }
            mysqli_stmt_close($stmt);
        }
    }
    
    mysqli_close($link);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register User</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-container">
        <div class="card">
            <h2 class="card-title">Daftar User Baru</h2>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label for="nama_lengkap">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" id="nama_lengkap" class="form-control <?php echo (!empty($nama_lengkap_err)) ? 'input-error' : ''; ?>" value="<?php echo $nama_lengkap; ?>">
                    <span class="error-text"><?php echo $nama_lengkap_err; ?></span>
                </div>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" class="form-control <?php echo (!empty($username_err)) ? 'input-error' : ''; ?>" value="<?php echo $username; ?>">
                    <span class="error-text"><?php echo $username_err; ?></span>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control <?php echo (!empty($password_err)) ? 'input-error' : ''; ?>">
                    <span class="error-text"><?php echo $password_err; ?></span>
                </div>
                

                <div class="form-actions">
                    <button class="btn btn-success" type="submit">
                        Daftar
                    </button>
                    <a href="login.php">
                        Sudah punya akun? Login
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
