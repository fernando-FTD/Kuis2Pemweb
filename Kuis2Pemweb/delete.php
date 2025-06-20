<?php
require_once "koneksi/db.php";

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}


if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){

    $id_to_delete = trim($_GET["id"]);
    

    $current_user_id = $_SESSION["id"];


    $sql_select_foto = "SELECT foto_profil FROM users WHERE id = ?";
    if($stmt_select = mysqli_prepare($link, $sql_select_foto)){
        mysqli_stmt_bind_param($stmt_select, "i", $id_to_delete);
        if(mysqli_stmt_execute($stmt_select)){
            $result = mysqli_stmt_get_result($stmt_select);
            if(mysqli_num_rows($result) == 1){
                $row = mysqli_fetch_assoc($result);
                $foto_to_delete = $row['foto_profil'];


                if($foto_to_delete != 'default.png' && file_exists(UPLOAD_DIR . $foto_to_delete)){
                    unlink(UPLOAD_DIR . $foto_to_delete);
                }
            }
        }
        mysqli_stmt_close($stmt_select);
    }
    

    $sql_delete = "DELETE FROM users WHERE id = ?";
    if($stmt_delete = mysqli_prepare($link, $sql_delete)){
        mysqli_stmt_bind_param($stmt_delete, "i", $id_to_delete);
        

        if(mysqli_stmt_execute($stmt_delete)){
            

            if($id_to_delete == $current_user_id){
                $_SESSION = array();
                session_destroy();
                
                header("location: login.php");
                exit();
            } else {
                header("location: index.php");
                exit();
            }

        } else {
            echo "Oops! Terjadi kesalahan. Silakan coba lagi nanti.";
        }
    }
    mysqli_stmt_close($stmt_delete);
    mysqli_close($link);

} else {
    header("location: index.php");
    exit();
}
?>
