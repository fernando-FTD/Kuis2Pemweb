<?php
require_once "koneksi/db.php";

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Manajemen User</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header class="page-header">
             <h1>Selamat Datang, <?php echo htmlspecialchars($_SESSION["nama_lengkap"]); ?>!</h1>
             <div>
                <a href="register.php" class="btn btn-success">Tambah User Baru</a>
                <a href="logout.php" class="btn btn-danger">Logout</a>
            </div>
        </header>

        <div class="table-wrapper">
            <h2 class="table-title">Daftar User</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Nama Lengkap</th>
                        <th>Username</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT id, nama_lengkap, username, foto_profil FROM users";
                    if($result = mysqli_query($link, $sql)){
                        if(mysqli_num_rows($result) > 0){
                            while($row = mysqli_fetch_array($result)){
                                echo "<tr>";
                                    echo "<td><img src='" . UPLOAD_DIR . htmlspecialchars($row['foto_profil']) . "' alt='Foto Profil' class='profile-pic' onerror=\"this.onerror=null;this.src='https://placehold.co/100x100/EBF4FF/76A9FA?text=Foto'\"/></td>";
                                    echo "<td>" . htmlspecialchars($row['nama_lengkap']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                                    echo "<td class='action-links'>";
                                        echo "<a href='edit.php?id=". $row['id'] ."' class='edit'>Edit</a>";
                                        echo "<a href='delete.php?id=". $row['id'] ."' class='delete' onclick='return confirm(\"Apakah Anda yakin ingin menghapus user ini?\")'>Hapus</a>";
                                    echo "</td>";
                                echo "</tr>";
                            }
                            mysqli_free_result($result);
                        } else{
                            echo "<tr><td colspan='4' style='text-align: center; padding: 2rem;'>Tidak ada data user.</td></tr>";
                        }
                    } else{
                        echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
                    }
                    mysqli_close($link);
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
