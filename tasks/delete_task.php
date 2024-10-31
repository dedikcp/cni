<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$task_id = $_GET['id'] ?? null;
if ($task_id) {
    try {
        // Ambil informasi attachment sebelum menghapus
        $stmt = $pdo->prepare("SELECT attachment_path FROM tasks WHERE id = ?");
        $stmt->execute([$task_id]);
        $task = $stmt->fetch();

        // Hapus file attachment jika ada
        if ($task['attachment_path']) {
            $file_path = '../uploads/' . $task['attachment_path'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }

        // Hapus task dari database
        $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
        $stmt->execute([$task_id]);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

header('Location: ../index.php');
exit;
