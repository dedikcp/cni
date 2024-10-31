<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$task_id = $_GET['id'] ?? null;
if (!$task_id) {
    header('Location: ../index.php');
    exit;
}

// Ambil data task
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
$stmt->execute([$task_id]);
$task = $stmt->fetch();

if (!$task) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uploadDir = '../uploads/';
    $attachment_path = $task['attachment_path'];

    // Handle file upload jika ada file baru
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === 0) {
        // Hapus file lama jika ada
        if ($attachment_path && file_exists($uploadDir . $attachment_path)) {
            unlink($uploadDir . $attachment_path);
        }

        $filename = time() . '_' . $_FILES['attachment']['name'];
        $uploadFile = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['attachment']['tmp_name'], $uploadFile)) {
            $attachment_path = $filename;
        }
    }

    try {
        $sql = "UPDATE tasks SET
                status = :status,
                task_description = :task_description,
                location_site = :location_site,
                assignee = :assignee,
                due_date = :due_date,
                notes = :notes,
                priority = :priority,
                attachment_path = :attachment_path
                WHERE id = :id";

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
            ':id' => $task_id,
        ]);

        header('Location: ../index.php');
        exit;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Task - NOC Task Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                Edit Task
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="pending" <?php echo $task['status'] == 'pending' ? 'selected' : ''; ?>>
                                    Pending</option>
                                <option value="in_progress"
                                    <?php echo $task['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress
                                </option>
                                <option value="completed"
                                    <?php echo $task['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-select" required>
                                <option value="low" <?php echo $task['priority'] == 'low' ? 'selected' : ''; ?>>Low
                                </option>
                                <option value="medium" <?php echo $task['priority'] == 'medium' ? 'selected' : ''; ?>>
                                    Medium</option>
                                <option value="high" <?php echo $task['priority'] == 'high' ? 'selected' : ''; ?>>High
                                </option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Location Site</label>
                            <input type="text" name="location_site" class="form-control" required
                                value="<?php echo htmlspecialchars($task['location_site']); ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Task Description</label>
                        <textarea name="task_description" class="form-control" rows="3"
                            required><?php echo htmlspecialchars($task['task_description']); ?></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Assignee</label>
                            <input type="text" name="assignee" class="form-control" required
                                value="<?php echo htmlspecialchars($task['assignee']); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Due Date</label>
                            <input type="date" name="due_date" class="form-control" required
                                value="<?php echo $task['due_date']; ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Attachment</label>
                            <?php if ($task['attachment_path']): ?>
                            <div class="mb-2">
                                <a href="../uploads/<?php echo $task['attachment_path']; ?>" target="_blank">
                                    Current File
                                </a>
                            </div>
                            <?php endif;?>
                            <input type="file" name="attachment" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control"
                            rows="2"><?php echo htmlspecialchars($task['notes']); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Task</button>
                    <a href="../index.php" class="btn btn-secondary">Kembali</a>
                </form>
            </div>
        </div>
    </div>
</body>

</html>