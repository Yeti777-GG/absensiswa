<?php
// Detektor Error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

require_once 'koneksi.php';

$success = '';
$error = '';
$username = $_SESSION['user'];
$tanggal_hari_ini = date('Y-m-d');

// Cek apakah hari ini sudah pernah absen sebelumnya
$cek_absen = $conn->prepare("SELECT status, keterangan FROM absensi WHERE username = ? AND tanggal = ?");
$cek_absen->bind_param("ss", $username, $tanggal_hari_ini);
$cek_absen->execute();
$result_absen = $cek_absen->get_result();
$sudah_absen = $result_absen->fetch_assoc();
$cek_absen->close();

// Proses ketika form absen dikirim
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['kirim_absen'])) {
    $status = $_POST['status'];
    $keterangan = trim($_POST['keterangan']);

    if (!$sudah_absen) {
        $insert = $conn->prepare("INSERT INTO absensi (username, tanggal, status, keterangan) VALUES (?, ?, ?, ?)");
        $insert->bind_param("ssss", $username, $tanggal_hari_ini, $status, $keterangan);
        
        if ($insert->execute()) {
            $success = 'Absensi hari ini berhasil direkam!';
            // Refresh data status absen setelah insert
            $sudah_absen = ['status' => $status, 'keterangan' => $keterangan];
        } else {
            $error = 'Gagal merekam absen: ' . $conn->error;
        }
        $insert->close();
    } else {
        $error = 'Anda sudah melakukan absensi hari ini!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard & Absensi - Web App</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style> body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); } </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-lg bg-white/15 backdrop-blur-md border border-white/20 rounded-2xl shadow-2xl p-8 text-white">
        
        <!-- Header Profil -->
        <div class="flex justify-between items-center mb-6 pb-4 border-b border-white/20">
            <div>
                <h1 class="text-xl font-bold tracking-wide">Halo, <?php echo htmlspecialchars($_SESSION['nama']); ?>!</h1>
                <p class="text-xs text-white/70">Tanggal: <?php echo date('d-m-Y'); ?></p>
            </div>
            <a href="logout.php" class="px-4 py-2 bg-red-500/80 hover:bg-red-600 text-white text-sm font-semibold rounded-xl transition shadow-md">
                <i class="fa-solid fa-right-from-bracket mr-1"></i> Logout
            </a>
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

        <!-- Jika sudah absen, tampilkan status. Jika belum, tampilkan form -->
        <?php if ($sudah_absen): ?>
            <div class="bg-white/10 border border-white/20 rounded-xl p-6 text-center space-y-3">
                <div class="inline-flex items-center justify-center w-14 h-14 bg-green-500/20 text-green-300 rounded-full mb-1">
                    <i class="fa-solid fa-check text-2xl"></i>
                </div>
                <h2 class="text-lg font-bold">Anda Sudah Melakukan Absen</h2>
                <p class="text-sm text-white/80">Status Kehadiran: <span class="font-semibold px-2 py-1 bg-white/20 rounded-md"><?php echo $sudah_absen['status']; ?></span></p>
                <?php if (!empty($sudah_absen['keterangan'])): ?>
                    <p class="text-xs text-white/70 italic">Keterangan: "<?php echo htmlspecialchars($sudah_absen['keterangan']); ?>"</p>
                <?php endif; ?>
                <p class="text-xs text-white/60 pt-2">Terima kasih telah melakukan absensi hari ini.</p>
            </div>
        <?php else: ?>
            <form action="" method="POST" class="space-y-4">
                <h2 class="text-lg font-semibold mb-2"><i class="fa-solid fa-clipboard-user mr-2"></i> Form Absensi Harian</h2>
                
                <div>
                    <label class="block text-sm font-medium mb-1 text-white/90">Status Kehadiran</label>
                    <select name="status" required class="w-full px-4 py-3 bg-gray-900 border border-white/20 rounded-xl focus:outline-none focus:ring-2 focus:ring-white/50 text-white text-sm">
                        <option value="Hadir">Hadir</option>
                        <option value="Izin">Izin</option>
                        <option value="Sakit">Sakit</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1 text-white/90">Keterangan (Opsional)</label>
                    <textarea name="keterangan" rows="3" class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl focus:outline-none focus:ring-2 focus:ring-white/50 text-white placeholder-white/50 text-sm" placeholder="Catatan tambahan..."></textarea>
                </div>

                <button type="submit" name="kirim_absen" class="w-full py-3 px-4 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-semibold rounded-xl shadow-lg transition duration-200">
                    Kirim Absen Sekarang
                </button>
            </form>
        <?php endif; ?>

    </div>
</body>
</html>

<a href="rekap_absen.php" class="block w-full py-3 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-semibold rounded-xl transition shadow-lg text-center mt-3">
    <i class="fa-solid fa-clipboard-list mr-2"></i> Lihat Rekap Absen (Guru)
</a>