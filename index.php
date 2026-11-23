<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

if (file_exists('koneksi.php')) {
    require_once 'koneksi.php';
} else {
    die("Error: File koneksi.php tidak ditemukan!");
}

$error = '';
$success = '';

if (isset($_GET['registered'])) {
    $success = 'Registrasi berhasil! Silakan masuk dengan akun Anda.';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        $stmt = $conn->prepare("SELECT username, password, nama_lengkap FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                
                // Regenerasi ID session untuk keamanan ekstra
                session_regenerate_id(true);
                $new_session_id = session_id();

                // Simpan session ID unik ke database untuk mencegah login ganda
                $update_session = $conn->prepare("UPDATE users SET current_session_id = ? WHERE username = ?");
                $update_session->bind_param("ss", $new_session_id, $row['username']);
                $update_session->execute();
                $update_session->close();

                // Set variabel session
                $_SESSION['user'] = $row['username'];
                $_SESSION['nama'] = $row['nama_lengkap'];
                $_SESSION['login_session_id'] = $new_session_id;
                
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Username atau password salah!';
            }
        } else {
            $error = 'Username atau password salah!';
        }
        $stmt->close();
    } else {
        $error = 'Harap isi semua kolom!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Absensi Siswa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        body {
            background: linear-gradient(-45deg, #4f46e5, #7c3aed, #db2777, #2563eb);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white/10 backdrop-blur-xl border border-white/20 rounded-3xl shadow-2xl p-8 md:p-10 text-white relative overflow-hidden">
        
        <div class="text-center mb-8 relative z-10">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-tr from-white/20 to-white/5 border border-white/30 rounded-2xl mb-4 shadow-inner">
                <i class="fa-solid fa-shield-halved text-3xl text-white drop-shadow-md"></i>
            </div>
            <h1 class="text-3xl font-extrabold tracking-tight">AbsensiKu</h1>
            <p class="text-sm text-white/70 mt-1">Sistem Absensi Aman & Anti-Curang</p>
        </div>

        <?php if (!empty($success)): ?>
            <div class="mb-6 p-4 bg-emerald-500/20 border border-emerald-500/40 rounded-xl text-sm text-emerald-100 flex items-center shadow-lg backdrop-blur-md relative z-10">
                <i class="fa-solid fa-circle-check text-emerald-400 text-lg mr-3 flex-shrink-0"></i>
                <span><?php echo $success; ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="mb-6 p-4 bg-rose-500/20 border border-rose-500/40 rounded-xl text-sm text-rose-100 flex items-center shadow-lg backdrop-blur-md relative z-10">
                <i class="fa-solid fa-triangle-exclamation text-rose-400 text-lg mr-3 flex-shrink-0"></i>
                <span><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-5 relative z-10">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider mb-2 text-white/80">Username</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-white/50"><i class="fa-solid fa-user"></i></span>
                    <input type="text" name="username" required class="w-full pl-11 pr-4 py-3.5 bg-white/10 border border-white/20 rounded-xl focus:outline-none focus:ring-2 focus:ring-white/60 text-white placeholder-white/40 text-sm shadow-inner" placeholder="Masukkan username Anda">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider mb-2 text-white/80">Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-white/50"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" name="password" required class="w-full pl-11 pr-4 py-3.5 bg-white/10 border border-white/20 rounded-xl focus:outline-none focus:ring-2 focus:ring-white/60 text-white placeholder-white/40 text-sm shadow-inner" placeholder="••••••••">
                </div>
            </div>

            <button type="submit" class="w-full py-3.5 px-4 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 hover:from-indigo-600 hover:to-pink-600 text-white font-bold rounded-xl shadow-lg transition duration-200 text-sm tracking-wide">
                <i class="fa-solid fa-right-to-bracket mr-2"></i> Masuk Sistem
            </button>
        </form>

        <div class="text-center mt-8 text-sm text-white/80 relative z-10 border-t border-white/10 pt-6">
            Belum punya akun? <a href="register.php" class="font-bold text-white hover:text-pink-200 underline transition">Daftar sekarang</a>
        </div>
    </div>
</body>
</html>