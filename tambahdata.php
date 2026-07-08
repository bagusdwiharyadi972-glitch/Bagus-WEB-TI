<?php
require "koneksi.php";

if (isset($_POST['simpan'])) {

    $nama   = $_POST['nama'];
    $nim    = $_POST['nim'];
    $prodi  = $_POST['prodi'];
    $email  = $_POST['email'];
    $no_hp  = $_POST['no_hp'];

    $query = mysqli_query($conn, "INSERT INTO mahasiswa
    (nama, nim, prodi, email, no_hp, foto)
    VALUES
    ('$nama', '$nim', '$prodi', '$email', '$no_hp', '')");

    if ($query) {
        echo "
        <script>
            alert('Data berhasil ditambahkan!');
            window.location='mahasiswa.php';
        </script>
        ";
    } else {
        echo "
        <script>
            alert('Data gagal ditambahkan!');
        </script>
        ";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Data Mahasiswa</title>

    <style>
        body{
            font-family:Arial;
            background:#f4f4f4;
        }

        .container{
            width:400px;
            margin:40px auto;
            background:#fff;
            padding:20px;
            border-radius:10px;
            box-shadow:0 0 10px rgba(0,0,0,.2);
        }

        h2{
            text-align:center;
        }

        input{
            width:100%;
            padding:10px;
            margin-bottom:15px;
            box-sizing:border-box;
        }

        .btn{
            background:#28a745;
            color:white;
            border:none;
            cursor:pointer;
        }

        .btn:hover{
            background:#218838;
        }

        .kembali{
            display:block;
            text-align:center;
            margin-top:10px;
            text-decoration:none;
        }
    </style>

</head>
<body>

<div class="container">

<h2>Tambah Data Mahasiswa</h2>

<form method="POST">

    <label>Nama</label>
    <input type="text" name="nama" required>

    <label>NIM</label>
    <input type="text" name="nim" required>

    <label>Prodi</label>
    <input type="text" name="prodi" required>

    <label>Email</label>
    <input type="email" name="email" required>

    <label>No HP</label>
    <input type="text" name="no_hp" required>

    <input class="btn" type="submit" name="simpan" value="Simpan Data">

</form>

<a class="kembali" href="mahasiswa.php">← Kembali</a>

</div>

</body>
</html>