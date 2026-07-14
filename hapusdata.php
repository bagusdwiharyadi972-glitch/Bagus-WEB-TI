<?php
require "koneksi.php";

// ── 1. Validate that the "nim" parameter exists ─────────
if (!isset($_GET['nim']) || trim($_GET['nim']) === '') {
    echo "
    <script>
        alert('NIM tidak ditemukan.');
        window.location='mahasiswa.php';
    </script>
    ";
    exit;
}

$nim = mysqli_real_escape_string($conn, $_GET['nim']);

// ── 2. Retrieve the student's data from the database ────
$query = mysqli_query($conn, "SELECT * FROM mahasiswa WHERE nim='$nim'");

if (!$query) {
    echo "
    <script>
        alert('Terjadi kesalahan database.');
        window.location='mahasiswa.php';
    </script>
    ";
    exit;
}

$data = mysqli_fetch_assoc($query);

// ── 3. If the student does not exist ────────────────────
if (!$data) {
    echo "
    <script>
        alert('Data mahasiswa tidak ditemukan.');
        window.location='mahasiswa.php';
    </script>
    ";
    exit;
}

// ── 4. If the student has an uploaded photo, delete it ──
if (!empty($data['foto'])) {
    $foto_path = 'foto/' . $data['foto'];
    if (file_exists($foto_path)) {
        unlink($foto_path);
    }
}

// ── 5. Delete the student's record from the database ────
$delete = mysqli_query($conn, "DELETE FROM mahasiswa WHERE nim='$nim'");

if ($delete) {
    echo "
    <script>
        alert('Student data has been successfully deleted.');
        window.location='mahasiswa.php';
    </script>
    ";
} else {
    echo "
    <script>
        alert('Gagal menghapus data mahasiswa.');
        window.location='mahasiswa.php';
    </script>
    ";
}
