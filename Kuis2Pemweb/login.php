<?php
// login.php
require_once "koneksi/db.php";

// Inisialisasi variabel
$username = $password = "";
$username_err = $password_err = $login_err = "";

// Proses data form saat form disubmit
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Cek jika username kosong
    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter username.";
    } else{
        $username = trim($_POST["username"]);
    }
    
    // Cek jika password kosong
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter your password.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validasi kredensial
    if(empty($username_err) && empty($password_err)){
        // Siapkan statement select
        $sql = "SELECT id, username, password, nama_lengkap FROM users WHERE username = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = $username;
            
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                
                // Cek jika username ada, lalu verifikasi password
                if(mysqli_stmt_num_rows($stmt) == 1){                    
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password, $nama_lengkap);
                    if(mysqli_stmt_fetch($stmt)){
                        if(password_verify($password, $hashed_password)){
                            // Password benar, mulai session baru
                            session_start();
                            
                            // Simpan data di session
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;
                            $_SESSION["nama_lengkap"] = $nama_lengkap;
                            
                            // Redirect ke halaman utama
                            header("location: index.php");
                        } else{
                            // Password salah
                            $login_err = "Invalid username or password.";
                        }
                    }
                } else{
                    // Username tidak ditemukan
                    $login_err = "Invalid username or password.";
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
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
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-container">
        <div class="card">
            <h2 class="card-title">Login</h2>
            <?php 
            if(!empty($login_err)){
                echo '<div class="alert alert-danger">' . $login_err . '</div>';
            }        
            ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
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
                    <button class="btn btn-primary" type="submit">
                        Login
                    </button>
                    <a href="register.php">
                        Belum punya akun? Daftar
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
