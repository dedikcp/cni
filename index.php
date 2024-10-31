<?php
session_start();
require_once __DIR__ . '/config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NOC Bali Task Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>NOC Bali Task Management</h2>
            <div>
                <span class="me-3">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="auth/logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>

        <!-- Advanced Filter Section -->
        <div class="card mb-4">
            <div class="card-header">
                Filter Options
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="filter_status" class="form-select">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Due Date Range</label>
                        <input type="date" name="filter_date_start" class="form-control mb-2">
                        <input type="date" name="filter_date_end" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Priority</label>
                        <select name="filter_priority" class="form-select">
                            <option value="">All Priority</option>
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Assignee</label>
                        <input type="text" name="filter_assignee" class="form-control">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Apply Filter</button>
                        <a href="index.php" class="btn btn-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Add New Task Form -->
        <div class="card mb-4">
            <div class="card-header">
                Add New Task
            </div>
            <div class="card-body">
                <form action="tasks/save_task.php" method="POST" enctype="multipart/form-data">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-select" required>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Location Site</label>
                            <input type="text" name="location_site" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Task Description</label>
                        <textarea name="task_description" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Assignee</label>
                            <input type="text" name="assignee" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Due Date</label>
                            <input type="date" name="due_date" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Attachment</label>
                            <input type="file" name="attachment" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                    <button type="submit" class="btn btn-success">Save Task</button>
                </form>
            </div>
        </div>

        <!-- Tasks List -->
        <div class="card">
            <div class="card-header">
                Tasks List
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Task Description</th>
                                <th>Location Site</th>
                                <th>Assignee</th>
                                <th>Due Date</th>
                                <th>Attachment</th>
                                <th>Notes</th>
                                <th>Actions</th>
                            </tr // Melanjutkan file: index.php (bagian tbody) </thead>
                        <tbody>
                            <?php
$where = [];
$params = [];

if (!empty($_GET['filter_status'])) {
    $where[] = "status = ?";
    $params[] = $_GET['filter_status'];
}

if (!empty($_GET['filter_date_start']) && !empty($_GET['filter_date_end'])) {
    $where[] = "due_date BETWEEN ? AND ?";
    $params[] = $_GET['filter_date_start'];
    $params[] = $_GET['filter_date_end'];
}

if (!empty($_GET['filter_priority'])) {
    $where[] = "priority = ?";
    $params[] = $_GET['filter_priority'];
}

if (!empty($_GET['filter_assignee'])) {
    $where[] = "assignee LIKE ?";
    $params[] = "%" . $_GET['filter_assignee'] . "%";
}

$sql = "SELECT * FROM tasks";
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY due_date ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

while ($row = $stmt->fetch()) {
    $priorityClass = [
        'low' => 'text-success',
        'medium' => 'text-warning',
        'high' => 'text-danger',
    ][$row['priority']] ?? '';

    echo "<tr>";
    echo "<td>
                                        <select class='form-select form-select-sm status-select' data-task-id='{$row['id']}'>
                                            <option value='pending' " . ($row['status'] == 'pending' ? 'selected' : '') . ">Pending</option>
                                            <option value='in_progress' " . ($row['status'] == 'in_progress' ? 'selected' : '') . ">In Progress</option>
                                            <option value='completed' " . ($row['status'] == 'completed' ? 'selected' : '') . ">Completed</option>
                                        </select>
                                    </td>";
    echo "<td class='{$priorityClass}'>" . ucfirst($row['priority']) . "</td>";
    echo "<td>" . htmlspecialchars($row['task_description']) . "</td>";
    echo "<td>" . htmlspecialchars($row['location_site']) . "</td>";
    echo "<td>" . htmlspecialchars($row['assignee']) . "</td>";
    echo "<td>" . htmlspecialchars($row['due_date']) . "</td>";
    echo "<td>";
    if ($row['attachment_path']) {
        echo "<a href='uploads/{$row['attachment_path']}' target='_blank' class='btn btn-sm btn-info'>
                                            <i class='fas fa-download'></i> Download
                                          </a>";
    }
    echo "</td>";
    echo "<td>" . htmlspecialchars($row['notes']) . "</td>";
    echo "<td>
                                        <a href='tasks/edit_task.php?id=" . $row['id'] . "' class='btn btn-sm btn-primary mb-1'>
                                            <i class='fas fa-edit'></i> Edit
                                        </a>
                                        <a href='tasks/delete_task.php?id=" . $row['id'] . "' class='btn btn-sm btn-danger'
                                           onclick='return confirm(\"Anda yakin ingin menghapus tugas ini?\")'>
                                            <i class='fas fa-trash'></i> Hapus
                                        </a>
                                    </td>";
    echo "</tr>";
}
?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
    $(document).ready(function() {
        // Update status menggunakan AJAX
        $('.status-select').change(function() {
            const taskId = $(this).data('task-id');
            const newStatus = $(this).val();

            $.post('tasks/update_status.php', {
                task_id: taskId,
                new_status: newStatus
            }, function(response) {
                if (response === 'success') {
                    alert('Status berhasil diperbarui!');
                } else {
                    alert('Gagal memperbarui status!');
                }
            });
        });
    });
    </script>
</body>

</html>