<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

require_once 'koneksi.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($nama_lengkap) && !empty($username) && !empty($password)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $error = 'Username sudah terpakai, silakan gunakan yang lain!';
            $stmt->close();
        } else {
            $stmt->close();
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $insert = $conn->prepare("INSERT INTO users (username, password, nama_lengkap) VALUES (?, ?, ?)");
            $insert->bind_param("sss", $username, $hashed_password, $nama_lengkap);
            
            if ($insert->execute()) {
                header('Location: index.php?registered=success');
                exit;
            } else {
                $error = 'Terjadi kesalahan saat mendaftar: ' . $conn->error;
            }
            $insert->close();
        }
    } else {
        $error = 'Semua kolom wajib diisi!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Web App Absen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style> body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); } </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl shadow-2xl p-8 text-white">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-white/20 rounded-full mb-4 shadow-inner">
                <i class="fa-solid fa-user-plus text-2xl text-white"></i>
            </div>
            <h1 class="text-2xl font-bold tracking-wide">Buat Akun Baru</h1>
            <p class="text-sm text-white/70 mt-1">Daftar untuk mengakses sistem</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mb-4 p-3 bg-red-500/80 border border-red-600 rounded-lg text-sm text-center font-medium">
                <i class="fa-solid fa-triangle-exclamation mr-2"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1 text-white/90">Nama Lengkap</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-white/60"><i class="fa-solid fa-id-card"></i></span>
                    <input type="text" name="nama_lengkap" required class="w-full pl-10 pr-4 py-3 bg-white/10 border border-white/20 rounded-xl focus:outline-none focus:ring-2 focus:ring-white/50 text-white placeholder-white/50 text-sm" placeholder="Nama lengkap Anda">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1 text-white/90">Username</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-white/60"><i class="fa-solid fa-user"></i></span>
                    <input type="text" name="username" required class="w-full pl-10 pr-4 py-3 bg-white/10 border border-white/20 rounded-xl focus:outline-none focus:ring-2 focus:ring-white/50 text-white placeholder-white/50 text-sm" placeholder="Buat username unik">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1 text-white/90">Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-white/60"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" name="password" required class="w-full pl-10 pr-4 py-3 bg-white/10 border border-white/20 rounded-xl focus:outline-none focus:ring-2 focus:ring-white/50 text-white placeholder-white/50 text-sm" placeholder="••••••••">
                </div>
            </div>
            <button type="submit" class="w-full py-3 px-4 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-semibold rounded-xl shadow-lg transition duration-200 mt-2">
                Daftar Sekarang
            </button>
        </form>

        <div class="text-center mt-6 text-sm text-white/80">
            Sudah punya akun? <a href="index.php" class="font-bold text-white hover:underline">Masuk di sini</a>
        </div>
    </div>
</body>
</html>