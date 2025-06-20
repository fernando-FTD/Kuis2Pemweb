<?php
require_once "koneksi/db.php";

// Cek session
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

$nama_lengkap = $username = "";
$nama_lengkap_err = $username_err = $password_err = $foto_profil_err = "";
$current_foto = "";
$id = 0;

// Ambil ID dari GET atau POST
if(isset($_POST['id'])){
    $id = $_POST['id'];
} elseif(isset($_GET['id'])){
    $id = $_GET['id'];
} else {
    header("location: index.php");
    exit();
}


// Proses form disubmit
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    if(empty(trim($_POST["nama_lengkap"]))){
        $nama_lengkap_err = "Nama lengkap tidak boleh kosong.";
    } else {
        $nama_lengkap = trim($_POST["nama_lengkap"]);
    }

    if(empty(trim($_POST["username"]))){
        $username_err = "Username tidak boleh kosong.";
    } else {
        // Cek jika username sudah dipakai user lain
        $sql_check = "SELECT id FROM users WHERE username = ? AND id != ?";
        if($stmt_check = mysqli_prepare($link, $sql_check)){
            mysqli_stmt_bind_param($stmt_check, "si", $param_username_check, $param_id_check);
            $param_username_check = trim($_POST["username"]);
            $param_id_check = $id;
            if(mysqli_stmt_execute($stmt_check)){
                mysqli_stmt_store_result($stmt_check);
                if(mysqli_stmt_num_rows($stmt_check) == 1){
                    $username_err = "Username ini sudah digunakan.";
                } else {
                    $username = trim($_POST["username"]);
                }
            }
            mysqli_stmt_close($stmt_check);
        }
    }
    
    // Siapkan statement UPDATE
    $sql_parts = ["nama_lengkap = ?", "username = ?"];
    $params = [$nama_lengkap, $username];
    $types = "ss";

    // Cek jika password diisi
    if(!empty(trim($_POST['password']))){
        if(strlen(trim($_POST['password'])) < 6){
            $password_err = "Password minimal 6 karakter.";
        } else {
            $sql_parts[] = "password = ?";
            $types .= "s";
            $params[] = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
        }
    }

    if(isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] == 0){
        $new_foto_name = uniqid() . '_' . basename($_FILES['foto_profil']['name']);
        $target_file = UPLOAD_DIR . $new_foto_name;
        
        if(move_uploaded_file($_FILES['foto_profil']['tmp_name'], $target_file)){
            $sql_parts[] = "foto_profil = ?";
            $types .= "s";
            $params[] = $new_foto_name;
            
            // Hapus foto lama jika bukan default
            $old_foto = $_POST['current_foto'];
            if($old_foto != 'default.png' && file_exists(UPLOAD_DIR . $old_foto)){
                unlink(UPLOAD_DIR . $old_foto);
            }
        } else {
            $foto_profil_err = "Gagal mengupload foto baru.";
        }
    }
    
    $sql = "UPDATE users SET " . implode(", ", $sql_parts) . " WHERE id = ?";
    $types .= "i";
    $params[] = $id;

    if(empty($nama_lengkap_err) && empty($username_err) && empty($password_err) && empty($foto_profil_err)){
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, $types, ...$params);
            if(mysqli_stmt_execute($stmt)){
                header("location: index.php");
                exit();
            } else {
                echo "Oops! Something went wrong.";
            }
            mysqli_stmt_close($stmt);
        }
    }

} 
// Ambil data user dari database untuk ditampilkan di form
$sql_get = "SELECT * FROM users WHERE id = ?";
if($stmt_get = mysqli_prepare($link, $sql_get)){
    mysqli_stmt_bind_param($stmt_get, "i", $id);
    if(mysqli_stmt_execute($stmt_get)){
        $result = mysqli_stmt_get_result($stmt_get);
        if(mysqli_num_rows($result) == 1){
            $row = mysqli_fetch_assoc($result);
            // Hanya isi variabel jika form belum disubmit
            if($_SERVER["REQUEST_METHOD"] != "POST"){
                $nama_lengkap = $row['nama_lengkap'];
                $username = $row['username'];
            }
            $current_foto = $row['foto_profil'];
        } else {
            header("location: index.php");
            exit();
        }
    }
    mysqli_stmt_close($stmt_get);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-container">
        <div class="card">
            <h2 class="card-title">Edit User</h2>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $id; ?>"/>
                <input type="hidden" name="current_foto" value="<?php echo $current_foto; ?>"/>

                <div class="form-group">
                    <img src="<?php echo UPLOAD_DIR . $current_foto; ?>" alt="Foto Profil" class="edit-form-pic" onerror="this.onerror=null;this.src='https://placehold.co/128x128/EBF4FF/76A9FA?text=Foto';">
                </div>

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
                    <label for="password">Password Baru</label>
                    <input type="password" name="password" id="password" class="form-control <?php echo (!empty($password_err)) ? 'input-error' : ''; ?>">
                    <p class="form-note">Kosongkan jika tidak ingin mengubah password.</p>
                    <span class="error-text"><?php echo $password_err; ?></span>
                </div>
                <div class="form-group">
                    <label for="foto_profil">Ganti Foto Profil</label>
                    <input type="file" name="foto_profil" id="foto_profil" class="form-control">
                    <span class="error-text"><?php echo $foto_profil_err; ?></span>
                </div>
                <div class="form-actions">
                    <button class="btn btn-primary" type="submit">
                        Simpan Perubahan
                    </button>
                    <a href="index.php">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
