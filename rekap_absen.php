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

// Cek apakah user sudah login (Bisa dibatasi jika ada role guru, atau untuk sementara semua user yang login bisa akses)
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

// Ambil data rekap absensi digabung dengan nama lengkap dari tabel users
$query = "SELECT absensi.*, users.nama_lengkap 
          FROM absensi 
          JOIN users ON absensi.username = users.username 
          ORDER BY absensi.tanggal DESC, absensi.id DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Absen Guru - Web App</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style> body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); } </style>
</head>
<body class="min-h-screen p-4 md:p-8 flex flex-col items-center">
    
    <div class="w-full max-w-5xl bg-white/15 backdrop-blur-md border border-white/20 rounded-2xl shadow-2xl p-6 md:p-8 text-white">
        
        <!-- Header Rekap -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 pb-4 border-b border-white/20 gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-wide"><i class="fa-solid fa-clipboard-list mr-2"></i> Rekapitulasi Absensi Siswa</h1>
                <p class="text-sm text-white/70">Daftar kehadiran seluruh siswa yang telah masuk ke sistem</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="dashboard.php" class="px-4 py-2 bg-white/20 hover:bg-white/30 text-white text-sm font-semibold rounded-xl transition shadow-md">
                    <i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Dashboard
                </a>
                <a href="logout.php" class="px-4 py-2 bg-red-500/80 hover:bg-red-600 text-white text-sm font-semibold rounded-xl transition shadow-md">
                    <i class="fa-solid fa-right-from-bracket mr-1"></i> Logout
                </a>
            </div>
        </div>

        <!-- Tabel Rekap Absen -->
        <div class="overflow-x-auto rounded-xl border border-white/20 bg-white/10">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-white/20 text-white border-b border-white/20">
                        <th class="p-3 md:p-4 font-semibold">No</th>
                        <th class="p-3 md:p-4 font-semibold">Tanggal</th>
                        <th class="p-3 md:p-4 font-semibold">Nama Lengkap</th>
                        <th class="p-3 md:p-4 font-semibold">Username</th>
                        <th class="p-3 md:p-4 font-semibold">Status</th>
                        <th class="p-3 md:p-4 font-semibold">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
                            <tr class="hover:bg-white/5 transition">
                                <td class="p-3 md:p-4"><?php echo $no++; ?></td>
                                <td class="p-3 md:p-4 whitespace-nowrap"><?php echo date('d-m-Y', strtotime($row['tanggal'])); ?></td>
                                <td class="p-3 md:p-4 font-medium"><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                <td class="p-3 md:p-4 text-white/80"><?php echo htmlspecialchars($row['username']); ?></td>
                                <td class="p-3 md:p-4">
                                    <?php 
                                        $badgeColor = 'bg-gray-500/30 text-gray-200';
                                        if ($row['status'] == 'Hadir') $badgeColor = 'bg-green-500/30 text-green-200 border border-green-500/40';
                                        elseif ($row['status'] == 'Izin') $badgeColor = 'bg-yellow-500/30 text-yellow-200 border border-yellow-500/40';
                                        elseif ($row['status'] == 'Sakit') $badgeColor = 'bg-blue-500/30 text-blue-200 border border-blue-500/40';
                                    ?>
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $badgeColor; ?>">
                                        <?php echo $row['status']; ?>
                                    </span>
                                </td>
                                <td class="p-3 md:p-4 text-white/80 italic">
                                    <?php echo !empty($row['keterangan']) ? htmlspecialchars($row['keterangan']) : '<span class="text-white/40">-</span>'; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="p-6 text-center text-white/70 italic">
                                Belum ada data absensi yang tercatat.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>