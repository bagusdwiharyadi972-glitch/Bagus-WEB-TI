<?php
include "koneksi.php";

$query = mysqli_query($conn, "SELECT * FROM mahasiswa");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Mahasiswa</title>

    <style>
        body{
            font-family: Arial, Helvetica, sans-serif;
            background:#f4f4f4;
        }

        h1,h2{
            text-align:center;
        }

        .menu{
            margin:auto;
            border-collapse:collapse;
        }

        .menu td{
            padding:10px 20px;
            background:#007bff;
        }

        .menu a{
            color:white;
            text-decoration:none;
            font-weight:bold;
        }

        .menu td:hover{
            background:#0056b3;
        }

        table{
            margin:auto;
            border-collapse:collapse;
        }

        th{
            background:#007bff;
            color:white;
        }

        th,td{
            border:1px solid black;
            padding:10px;
            text-align:center;
        }

        img{
            width:80px;
            height:100px;
            object-fit:cover;
            border-radius:5px;
        }

    </style>

</head>
<body>

<h1>WEB TI DWI - 2026</h1>

<table class="menu">
    <tr>
        <td><a href="index.php">Home</a></td>
        <td><a href="profile.php">Profile</a></td>
        <td><a href="contact.php">Contact</a></td>
        <td><a href="mahasiswa.php">Data Mahasiswa</a></td>
    </tr>
</table>

<br>

<h2>DATA MAHASASISWA</h2>

<a href="tambahdata.php">
    <button>Tambah Data</button>
</a>

<table>

<tr>
    <th>No</th>
    <th>Nama</th>
    <th>NIM</th>
    <th>Prodi</th>
    <th>Email</th>
    <th>No HP</th>
    <th>Foto</th>
</tr>

<?php

$no=1;

while($data=mysqli_fetch_array($query))
{

?>

<tr>

<td><?= $no++; ?></td>

<td><?= $data['nama']; ?></td>

<td><?= $data['nim']; ?></td>

<td><?= $data['prodi']; ?></td>

<td><?= $data['email']; ?></td>

<td><?= $data['no_hp']; ?></td>

<td>
    <img src="assets/img/<?= $data['foto']; ?>">
</td>

</tr>

<?php
}
?>

</table>

</body>
</html>