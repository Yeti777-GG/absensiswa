<?php
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tanggal = $_POST['tanggal'];
    $siswa_ids = $_POST['siswa_id'];
    $statuses = $_POST['status'];

    // Lakukan perulangan (looping) sebanyak data siswa yang dikirim
    for ($i = 0; $i < count($siswa_ids); $i++) {
        $siswa_id = mysqli_real_escape_string($conn, $siswa_ids[$i]);
        $status = mysqli_real_escape_string($conn, $statuses[$i]);

        // Opsional: Cek apakah data absensi siswa pada tanggal tersebut sudah ada, 
        // jika ingin mencegah duplikasi atau melakukan update. Di sini kita langsung insert:
        $query = "INSERT INTO absensi (siswa_id, tanggal, status) VALUES ('$siswa_id', '$tanggal', '$status')";
        mysqli_query($conn, $query);
    }

    // Redirect kembali ke index dengan status sukses
    header("Location: index.php?status=sukses");
    exit();
} else {
    header("Location: index.php");
    exit();
}
?>