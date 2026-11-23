<?php
// Detektor Error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (file_exists('koneksi.php')) {
    require_once 'koneksi.php';
} else {
    die("Error: File koneksi.php tidak ditemukan!");
}

// Cek apakah user sudah login
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$success = '';
$error = '';

// Proses ketika tombol "Kirim Absen" ditekan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_SESSION['user'];
    $status = $_POST['status']; 
    $keterangan = trim($_POST['keterangan']);
    $tanggal = date('Y-m-d');

    // Cek apakah hari ini sudah pernah absen
    $cek = $conn->prepare("SELECT id FROM absensi WHERE username = ? AND tanggal = ?");
    $cek->bind_param("ss", $username, $tanggal);
    $cek->execute();
    $cek->store_result();

    if ($cek->num_rows > 0) {
        $error = 'Anda sudah melakukan absensi hari ini!';
        $cek->close();
    } else {
        $cek->close();
        
        // Simpan data absen ke database
        $insert = $conn->prepare("INSERT INTO absensi (username, tanggal, status, keterangan) VALUES (?, ?, ?, ?)");
        $insert->bind_param("ssss", $username, $tanggal, $status, $keterangan);
        
        if ($insert->execute()) {
            $success = 'Absensi berhasil direkam!';
        } else {
            $error = 'Gagal merekam absen: ' . $conn->error;
        }
        $insert->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Absensi - Web App</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style> body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); } </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl shadow-2xl p-8 text-white">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold tracking-wide">Form Absensi Harian</h1>
            <p class="text-sm text-white/70 mt-1">Tanggal: <?php echo date('d-m-Y'); ?></p>
        </div>

        <?php if (!empty($success)): ?>
            <div class="mb-4 p-3 bg-green-500/80 border border-green-600 rounded-lg text-sm text-center font-medium">
                <i class="fa-solid fa-circle-check mr-2"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="mb-4 p-3 bg-red-500/80 border border-red-600 rounded-lg text-sm text-center font-medium">
                <i class="fa-solid fa-triangle-exclamation mr-2"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1 text-white/90">Status Kehadiran</label>
                <select name="status" required class="w-full px-4 py-3 bg-gray-800 border border-white/20 rounded-xl focus:outline-none focus:ring-2 focus:ring-white/50 text-white text-sm">
                    <option value="Hadir">Hadir</option>
                    <option value="Izin">Izin</option>
                    <option value="Sakit">Sakit</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1 text-white/90">Keterangan (Opsional)</label>
                <textarea name="keterangan" rows="3" class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl focus:outline-none focus:ring-2 focus:ring-white/50 text-white placeholder-white/50 text-sm" placeholder="Catatan tambahan..."></textarea>
            </div>

            <button type="submit" class="w-full py-3 px-4 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-semibold rounded-xl shadow-lg transition duration-200">
                Kirim Absen
            </button>
        </form>

        <div class="mt-4 text-center">
            <a href="dashboard.php" class="text-sm text-white/80 hover:underline"><i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Dashboard</a>
        </div>
    </div>
</body>
</html>