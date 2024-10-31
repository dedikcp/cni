<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uploadDir = '../uploads/';
    $attachment_path = null;

    // Handle file upload
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === 0) {
        $filename = time() . '_' . $_FILES['attachment']['name'];
        $uploadFile = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['attachment']['tmp_name'], $uploadFile)) {
            $attachment_path = $filename;
        }
    }

    $sql = "INSERT INTO tasks (status, task_description, location_site, assignee, due_date, notes, priority, attachment_path, created_by)
            VALUES (:status, :task_description, :location_site, :assignee, :due_date, :notes, :priority, :attachment_path, :created_by)";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':status' => $_POST['status'],
            ':task_description' => $_POST['task_description'],
            ':location_site' => $_POST['location_site'],
            ':assignee' => $_POST['assignee'],
            ':due_date' => $_POST['due_date'],
            ':notes' => $_POST['notes'],
            ':priority' => $_POST['priority'],
            ':attachment_path' => $attachment_path,
            ':created_by' => $_SESSION['user_id'],
        ]);

        header('Location: ../index.php');
        exit;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
