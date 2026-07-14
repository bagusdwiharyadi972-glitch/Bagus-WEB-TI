<?php
require "koneksi.php";

// ── Debug mode ────────────────────────────────────────────
// Enable by visiting:  tambahdata.php?debug=1
$debug = isset($_GET['debug']);
function debug_log($label, $value) {
    global $debug;
    if ($debug) {
        echo "<pre style='background:#fff3cd;border:1px solid #ffc107;padding:10px;margin:10px;border-radius:6px;'>";
        echo "<strong>" . htmlspecialchars($label) . ":</strong> ";
        if (is_array($value)) {
            echo htmlspecialchars(print_r($value, true));
        } else {
            echo htmlspecialchars(var_export($value, true));
        }
        echo "</pre>";
    }
}

// ── Handle form submission ───────────────────────────────
if (isset($_POST['simpan'])) {

    $nama   = trim($_POST['nama']);
    $nim    = trim($_POST['nim']);
    $prodi  = trim($_POST['prodi']);
    $email  = trim($_POST['email']);
    $no_hp  = trim($_POST['no_hp']);

    debug_log('$_POST (text fields)', [
        'nama'  => $nama,
        'nim'   => $nim,
        'prodi' => $prodi,
        'email' => $email,
        'no_hp' => $no_hp,
    ]);

    // ── 1. Check $_FILES exists ──────────────────────────
    if (!isset($_FILES['foto']) || $_FILES['foto']['error'] === UPLOAD_ERR_NO_FILE) {
        debug_log('$_FILES', $_FILES);
        echo "<script>alert('Upload failed.');</script>";
        exit;
    }

    debug_log('$_FILES', [
        'name'     => $_FILES['foto']['name'],
        'type'     => $_FILES['foto']['type'],
        'tmp_name' => $_FILES['foto']['tmp_name'],
        'error'    => $_FILES['foto']['error'],
        'size'     => $_FILES['foto']['size'],
    ]);

    // ── 2. Check for upload errors ───────────────────────
    $upload_error = $_FILES['foto']['error'];
    if ($upload_error !== UPLOAD_ERR_OK) {
        $error_msg = match ($upload_error) {
            UPLOAD_ERR_INI_SIZE   => 'File melebihi batas upload_max_filesize di php.ini.',
            UPLOAD_ERR_FORM_SIZE  => 'File melebihi batas MAX_FILE_SIZE di form.',
            UPLOAD_ERR_PARTIAL    => 'File hanya terupload sebagian.',
            UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary upload tidak ditemukan.',
            UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk.',
            UPLOAD_ERR_EXTENSION  => 'Upload dihentikan oleh ekstensi PHP.',
            default               => 'Unknown upload error (code ' . $upload_error . ').',
        };
        debug_log('Upload error', $error_msg);
        echo "<script>alert('Upload failed.');</script>";
        exit;
    }

    $file_tmp  = $_FILES['foto']['tmp_name'];
    $file_size = $_FILES['foto']['size'];
    $orig_name = $_FILES['foto']['name'];
    $file_ext  = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));

    // ── 3. Verify the temp file actually exists ──────────
    if (!is_file($file_tmp) || !is_readable($file_tmp)) {
        debug_log('Temp file check', "Temp file does not exist or is not readable: $file_tmp");
        echo "<script>alert('Upload failed.');</script>";
        exit;
    }

    debug_log('Temp file exists', 'YES – ' . $file_tmp . ' (' . filesize($file_tmp) . ' bytes)');

    // ── 4. Validate file extension ───────────────────────
    $allowed_ext = ['jpg', 'jpeg', 'png'];
    if (!in_array($file_ext, $allowed_ext)) {
        debug_log('Extension validation', "Rejected: .$file_ext (allowed: " . implode(', ', $allowed_ext) . ")");
        echo "<script>alert('Invalid image format.');</script>";
        exit;
    }

    // ── 5. Validate file size ────────────────────────────
    $max_size = 2 * 1024 * 1024; // 2 MB
    if ($file_size > $max_size) {
        debug_log('Size validation', "Rejected: $file_size bytes (max: $max_size)");
        echo "<script>alert('Image size exceeds 2MB.');</script>";
        exit;
    }

    // ── 6. Validate actual image content ─────────────────
    $detected_mime = mime_content_type($file_tmp);
    $allowed_mime  = ['image/jpeg', 'image/png'];
    if (!in_array($detected_mime, $allowed_mime)) {
        debug_log('MIME validation', "Rejected MIME type: $detected_mime");
        echo "<script>alert('Invalid image format.');</script>";
        exit;
    }
    debug_log('MIME type', $detected_mime);

    // Also verify with getimagesize() as an extra check
    $image_info = @getimagesize($file_tmp);
    if ($image_info === false) {
        debug_log('getimagesize', 'File is not a valid image');
        echo "<script>alert('Invalid image format.');</script>";
        exit;
    }
    debug_log('Image dimensions', $image_info[0] . 'x' . $image_info[1]);

    // ── 7. Generate a guaranteed-unique filename ─────────
    // uniqid('', true) adds more entropy via trailing decimal digits.
    // We also verify the file doesn't already exist and retry.
    $upload_dir = 'foto/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0775, true);
    }

    $max_attempts = 10;
    for ($attempt = 1; $attempt <= $max_attempts; $attempt++) {
        $foto_name = uniqid('', true) . '.' . $file_ext;
        // Replace dots from more_entropy with underscore for clean filename
        $foto_name = str_replace('.', '_', $foto_name);
        $dest_path = $upload_dir . $foto_name;
        if (!file_exists($dest_path)) {
            break;
        }
        debug_log("Filename collision (attempt $attempt)", "Retrying – $dest_path already exists");
    }

    debug_log('Generated filename', $foto_name);
    debug_log('Destination path', $dest_path);

    // ── 8. Move uploaded file ────────────────────────────
    if (!move_uploaded_file($file_tmp, $dest_path)) {
        debug_log('move_uploaded_file', 'FAILED – php error: ' . error_get_last()['message'] ?? 'unknown');
        echo "<script>alert('Upload failed.');</script>";
        exit;
    }

    // ── 9. Verify the saved file matches the upload ──────
    clearstatcache(true, $dest_path);
    if (!is_file($dest_path)) {
        debug_log('Verification', "File does not exist after move: $dest_path");
        echo "<script>alert('Upload failed.');</script>";
        exit;
    }
    $saved_size = filesize($dest_path);
    if ($saved_size !== $file_size) {
        debug_log('Size mismatch', "Original: $file_size bytes, Saved: $saved_size bytes");
        // This is a serious integrity issue – delete the bad file
        @unlink($dest_path);
        echo "<script>alert('Upload failed.');</script>";
        exit;
    }
    debug_log('File size verification', "OK ($saved_size bytes matches original)");

    debug_log('Upload complete', "File saved to: $dest_path");

    // ── 10. Insert into database ─────────────────────────
    $query = mysqli_query($conn, "INSERT INTO mahasiswa
    (nama, nim, prodi, email, no_hp, foto)
    VALUES
    ('$nama', '$nim', '$prodi', '$email', '$no_hp', '$foto_name')");

    debug_log('SQL', "INSERT INTO mahasiswa ... foto='$foto_name'");
    debug_log('MySQL result', $query ? 'SUCCESS' : 'FAILED: ' . mysqli_error($conn));

    if ($query) {
        echo "
        <script>
            alert('Data successfully added.');
            window.location='mahasiswa.php';
        </script>
        ";
    } else {
        // Insert failed – clean up the orphaned file
        @unlink($dest_path);
        echo "
        <script>
            alert('Data gagal ditambahkan!');
        </script>
        ";
    }
}

if ($debug) {
    echo '<div style="background:#cfe2ff;border:1px solid #0d6efd;padding:10px;margin:10px;border-radius:6px;">';
    echo '<strong>🔍 Debug mode aktif.</strong> Hapus <code>?debug=1</code> dari URL untuk menonaktifkan.';
    echo '</div>';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Mahasiswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
            min-height: 100vh;
        }
        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background: linear-gradient(135deg, #0d6efd, #0b5ed7);
            color: white;
            border-radius: 1rem 1rem 0 0 !important;
            padding: 1.25rem 1.5rem;
        }
        .card-header h4 {
            margin: 0;
            font-weight: 600;
        }
        .card-body {
            padding: 2rem;
        }
        .form-label {
            font-weight: 500;
            color: #212529;
        }
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
        }
        .upload-wrapper {
            position: relative;
        }
        .upload-wrapper input[type="file"] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            cursor: pointer;
            z-index: 2;
        }
        .upload-box {
            border: 2px dashed #ced4da;
            border-radius: 0.75rem;
            padding: 2rem 1rem;
            text-align: center;
            transition: all 0.3s ease;
            background: #f8f9fa;
            cursor: pointer;
        }
        .upload-box:hover {
            border-color: #0d6efd;
            background: #e9ecef;
        }
        .upload-box.has-image {
            border-color: #198754;
            background: #f0fdf4;
        }
        .upload-box .upload-text {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .upload-box .upload-text strong {
            color: #0d6efd;
        }
        #preview {
            max-width: 100%;
            max-height: 180px;
            border-radius: 0.5rem;
            object-fit: cover;
            margin-top: 0.75rem;
        }
        .btn-success {
            padding: 0.6rem 1.5rem;
            font-weight: 500;
        }
        .btn-outline-secondary {
            padding: 0.6rem 1.5rem;
            font-weight: 500;
        }
    </style>

</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6 col-xl-5">

            <div class="card">

                <div class="card-header text-center">
                    <h4>Tambah Data Mahasiswa</h4>
                    <p class="mb-0 small opacity-75">Isi data mahasiswa baru</p>
                </div>

                <div class="card-body">

                    <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>

                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama" name="nama"
                                   placeholder="Masukkan nama" required>
                            <div class="invalid-feedback">Nama wajib diisi.</div>
                        </div>

                        <div class="mb-3">
                            <label for="nim" class="form-label">NIM</label>
                            <input type="text" class="form-control" id="nim" name="nim"
                                   placeholder="Masukkan NIM" required>
                            <div class="invalid-feedback">NIM wajib diisi.</div>
                        </div>

                        <div class="mb-3">
                            <label for="prodi" class="form-label">Program Studi</label>
                            <input type="text" class="form-control" id="prodi" name="prodi"
                                   placeholder="Contoh: Teknik Informatika" required>
                            <div class="invalid-feedback">Prodi wajib diisi.</div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                   placeholder="nama@email.com" required>
                            <div class="invalid-feedback">Masukkan email yang valid.</div>
                        </div>

                        <div class="mb-3">
                            <label for="no_hp" class="form-label">No. HP</label>
                            <input type="text" class="form-control" id="no_hp" name="no_hp"
                                   placeholder="08xxxxxxxxxx" required>
                            <div class="invalid-feedback">No. HP wajib diisi.</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Foto Mahasiswa</label>
                            <div class="upload-wrapper">
                                <input type="file" name="foto" id="foto"
                                       accept=".jpg,.jpeg,.png" required
                                       onchange="previewImage(event)">
                                <div class="upload-box" id="uploadBox">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40"
                                         fill="currentColor" class="d-block mx-auto mb-2 text-muted" viewBox="0 0 16 16">
                                        <path d="M6.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z"/>
                                        <path d="M2.002 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2h-12zm12 1a1 1 0 0 1 1 1v6.5l-3.777-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062L1.002 12V3a1 1 0 0 1 1-1h12z"/>
                                    </svg>
                                    <div class="upload-text">
                                        <strong>Klik atau tarik file</strong> untuk upload<br>
                                        <small>Format: JPG, JPEG, PNG (Max 2MB)</small>
                                    </div>
                                    <img id="preview" style="display:none;">
                                </div>
                            </div>
                            <div class="invalid-feedback">Foto wajib diupload.</div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" name="simpan" class="btn btn-success flex-fill">
                                Simpan Data
                            </button>
                            <a href="mahasiswa.php" class="btn btn-outline-secondary">Kembali</a>
                        </div>

                    </form>

                </div>

            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function previewImage(event) {
        var file = event.target.files[0];
        var preview = document.getElementById('preview');
        var uploadBox = document.getElementById('uploadBox');
        var uploadText = uploadBox.querySelector('.upload-text');

        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                uploadText.style.display = 'none';
                uploadBox.classList.add('has-image');
            };
            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none';
            uploadText.style.display = 'block';
            uploadBox.classList.remove('has-image');
        }
    }

    (function() {
        'use strict';
        var forms = document.querySelectorAll('.needs-validation');
        Array.prototype.slice.call(forms).forEach(function(form) {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    })();
</script>

</body>
</html>
