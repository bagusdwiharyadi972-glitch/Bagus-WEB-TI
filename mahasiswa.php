<?php
include "koneksi.php";

if (isset($_POST['update'])) {
    $no    = $_POST['no'];
    $nama  = $_POST['nama'];
    $nim   = $_POST['nim'];
    $prodi = $_POST['prodi'];
    $email = $_POST['email'];
    $no_hp = $_POST['no_hp'];

    $query = mysqli_query($conn, "UPDATE mahasiswa SET
        nama='$nama', nim='$nim', prodi='$prodi', email='$email', no_hp='$no_hp'
        WHERE no='$no'");

    if ($query) {
        echo "<script>
            alert('Data berhasil diupdate!');
            window.location='mahasiswa.php';
        </script>";
    } else {
        echo "<script>
            alert('Data gagal diupdate!');
        </script>";
    }
}

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

        .btn-edit{
            background:#ffc107;
            color:#000;
            border:none;
            padding:6px 12px;
            border-radius:4px;
            cursor:pointer;
            font-weight:bold;
        }

        .btn-edit:hover{
            background:#e0a800;
        }

        .btn-hapus{
            background:#dc3545;
            color:white;
            border:none;
            padding:6px 12px;
            border-radius:4px;
            cursor:pointer;
            font-weight:bold;
            margin-left:4px;
        }

        .btn-hapus:hover{
            background:#c82333;
        }

        /* Modal */
        .modal{
            display:none;
            position:fixed;
            z-index:999;
            left:0; top:0;
            width:100%; height:100%;
            background:rgba(0,0,0,0.5);
        }

        .modal-content{
            background:#fff;
            width:450px;
            margin:60px auto;
            padding:25px;
            border-radius:10px;
            box-shadow:0 5px 15px rgba(0,0,0,0.3);
        }

        .modal-content h2{
            margin-top:0;
        }

        .modal-content label{
            display:block;
            margin-top:12px;
            font-weight:bold;
        }

        .modal-content input{
            width:100%;
            padding:8px;
            margin-top:4px;
            box-sizing:border-box;
            border:1px solid #ccc;
            border-radius:4px;
        }

        .modal-content .btn-simpan{
            background:#28a745;
            color:white;
            border:none;
            padding:10px;
            width:100%;
            margin-top:18px;
            border-radius:4px;
            cursor:pointer;
            font-weight:bold;
        }

        .modal-content .btn-simpan:hover{
            background:#218838;
        }

        .modal-content .btn-tutup{
            background:#dc3545;
            color:white;
            border:none;
            padding:6px 14px;
            border-radius:4px;
            cursor:pointer;
            float:right;
            font-weight:bold;
        }

        .modal-content .btn-tutup:hover{
            background:#c82333;
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
    <th>Aksi</th>
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
    <?php if ($data['foto']): ?>
        <?php $foto_path = 'foto/' . $data['foto']; ?>
        <?php $cache_buster = file_exists($foto_path) ? '?v=' . filemtime($foto_path) : ''; ?>
        <img src="<?= $foto_path . $cache_buster; ?>">
    <?php else: ?>
        No Photo
    <?php endif; ?>
</td>

<td>
    <button class="btn-edit"
        data-no="<?= $data['no'] ?>"
        data-nama="<?= $data['nama'] ?>"
        data-nim="<?= $data['nim'] ?>"
        data-prodi="<?= $data['prodi'] ?>"
        data-email="<?= $data['email'] ?>"
        data-no_hp="<?= $data['no_hp'] ?>"
        onclick="editData(this)">Edit</button>
    <button class="btn-hapus"
        data-nim="<?= $data['nim'] ?>"
        data-nama="<?= $data['nama'] ?>"
        onclick="hapusData(this)">Hapus</button>
</td>

</tr>

<?php
}
?>

</table>

<!-- Modal Edit -->
<div id="modalEdit" class="modal">
    <div class="modal-content">
        <button class="btn-tutup" onclick="tutupModal()">X</button>
        <h2>Edit Data Mahasiswa</h2>
        <form method="POST">
            <input type="hidden" name="no" id="edit_no">

            <label>Nama</label>
            <input type="text" name="nama" id="edit_nama" required>

            <label>NIM</label>
            <input type="text" name="nim" id="edit_nim" required>

            <label>Prodi</label>
            <input type="text" name="prodi" id="edit_prodi" required>

            <label>Email</label>
            <input type="email" name="email" id="edit_email" required>

            <label>No HP</label>
            <input type="text" name="no_hp" id="edit_no_hp" required>

            <input class="btn-simpan" type="submit" name="update" value="Simpan Perubahan">
        </form>
    </div>
</div>

<script>
    function editData(btn) {
        document.getElementById('edit_no').value    = btn.getAttribute('data-no');
        document.getElementById('edit_nama').value  = btn.getAttribute('data-nama');
        document.getElementById('edit_nim').value   = btn.getAttribute('data-nim');
        document.getElementById('edit_prodi').value = btn.getAttribute('data-prodi');
        document.getElementById('edit_email').value = btn.getAttribute('data-email');
        document.getElementById('edit_no_hp').value = btn.getAttribute('data-no_hp');
        document.getElementById('modalEdit').style.display = 'block';
    }

    function tutupModal() {
        document.getElementById('modalEdit').style.display = 'none';
    }

    function hapusData(btn) {
        var nim  = btn.getAttribute('data-nim');
        var nama = btn.getAttribute('data-nama');
        if (confirm('Are you sure you want to delete this student?')) {
            window.location = 'hapusdata.php?nim=' + encodeURIComponent(nim);
        }
    }

    window.onclick = function(e) {
        var modal = document.getElementById('modalEdit');
        if (e.target == modal) {
            modal.style.display = 'none';
        }
    }
</script>

</body>
</html>