<?php
// Memasukkan file koneksi database
include 'koneksi.php';

// Pastikan variabel $conn tersedia dan tidak error
if (!$conn) {
    die("Koneksi database belum terhubung dengan benar di file koneksi.php");
}

// Ambil filter tanggal jika ada yang dikirimkan via form GET
$filter_tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : '';

// Query untuk mengambil data absensi digabung dengan data siswa
$query_sql = "SELECT absensi.tanggal, siswa.nis, siswa.nama, siswa.kelas, absensi.status 
              FROM absensi 
              JOIN siswa ON absensi.siswa_id = siswa.id";

if (!empty($filter_tanggal)) {
    // Escape string untuk keamanan dasar terhadap SQL Injection
    $filter_tanggal_safe = mysqli_real_escape_string($conn, $filter_tanggal);
    $query_sql .= " WHERE absensi.tanggal = '$filter_tanggal_safe'";
}

$query_sql .= " ORDER BY absensi.tanggal DESC, siswa.kelas ASC, siswa.nama ASC";

// Eksekusi query dengan penanganan error yang jelas
$result = mysqli_query($conn, $query_sql);
if (!$result) {
    die("Query Error: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Absensi Siswa</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Rekapitulasi Absensi Siswa</h4>
                    <a href="index.php" class="btn btn-light btn-sm">Kembali ke Form Absen</a>
                </div>
                <div class="card-body">
                    <!-- Form Filter Tanggal -->
                    <form method="GET" action="" class="row g-3 mb-4">
                        <div class="col-auto">
                            <label for="tanggal" class="col-form-label">Filter Tanggal:</label>
                        </div>
                        <div class="col-auto">
                            <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?php echo htmlspecialchars($filter_tanggal); ?>">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary mb-3">Tampilkan</button>
                            <a href="rekap.php" class="btn btn-secondary mb-3">Reset</a>
                        </div>
                    </form>

                    <!-- Tabel Rekap -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th class="text-center" width="5%">No</th>
                                    <th width="15%">Tanggal</th>
                                    <th width="15%">NIS</th>
                                    <th width="35%">Nama Siswa</th>
                                    <th width="15%">Kelas</th>
                                    <th class="text-center" width="15%">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (mysqli_num_rows($result) > 0) {
                                    $no = 1;
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        // Menentukan warna badge berdasarkan status
                                        $badge_color = 'bg-secondary';
                                        if ($row['status'] == 'Hadir') $badge_color = 'bg-success';
                                        elseif ($row['status'] == 'Izin') $badge_color = 'bg-primary';
                                        elseif ($row['status'] == 'Sakit') $badge_color = 'bg-warning text-dark';
                                        elseif ($row['status'] == 'Alpa') $badge_color = 'bg-danger';
                                    ?>
                                        <tr>
                                            <td class="text-center"><?php echo $no++; ?></td>
                                            <td><?php echo htmlspecialchars($row['tanggal']); ?></td>
                                            <td><?php echo htmlspecialchars($row['nis']); ?></td>
                                            <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                            <td><?php echo htmlspecialchars($row['kelas']); ?></td>
                                            <td class="text-center">
                                                <span class="badge <?php echo $badge_color; ?>">
                                                    <?php echo htmlspecialchars($row['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php 
                                    }
                                } else {
                                    echo '<tr><td colspan="6" class="text-center text-muted">Belum ada data absensi yang tercatat.</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>