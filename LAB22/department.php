<?php
require_once 'db.php';
session_start();

// Delete (only if no employees linked)
if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];
    $check = $conn->prepare("SELECT id FROM employees WHERE department_id = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        $_SESSION['msg'] = "Cannot delete: employees assigned to this department.";
    } else {
        $stmt = $conn->prepare("DELETE FROM departments WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $_SESSION['msg'] = "Department deleted.";
        $stmt->close();
    }
    $check->close();
    header("Location: department.php");
    exit();
}

// Edit fetch
$edit_id = isset($_GET['edit_id']) ? (int)$_GET['edit_id'] : null;
$edit_data = null;
if ($edit_id) {
    $stmt = $conn->prepare("SELECT * FROM departments WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Save / Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_dept'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $update_id = isset($_POST['update_id']) ? (int)$_POST['update_id'] : null;

    if (empty($name)) {
        $error = "Department name required.";
    } else {
        if ($update_id) {
            $stmt = $conn->prepare("UPDATE departments SET name=?, description=? WHERE id=?");
            $stmt->bind_param("ssi", $name, $description, $update_id);
            $msg = "Department updated.";
        } else {
            $stmt = $conn->prepare("INSERT INTO departments (name, description) VALUES (?, ?)");
            $stmt->bind_param("ss", $name, $description);
            $msg = "Department added.";
        }
        if ($stmt->execute()) {
            $_SESSION['msg'] = $msg;
        } else {
            $_SESSION['msg'] = "Error saving department.";
        }
        $stmt->close();
        header("Location: department.php");
        exit();
    }
}

$departments = $conn->query("SELECT id, name, description FROM departments ORDER BY id")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Department Management</title>
</head>
<body>
    <p><a href="index.html">← Back to Home</a></p>

    <h1>Department Registration</h1>
    <?php if(isset($_SESSION['msg'])): ?>
        <p><strong><?= $_SESSION['msg']; unset($_SESSION['msg']); ?></strong></p>
    <?php endif; ?>
    <?php if(isset($error)): ?>
        <p style="color:red;"><?= $error ?></p>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="update_id" value="<?= $edit_data['id'] ?? '' ?>">
        <table border="0">
            <tr>
                <td>Department Name:</td>
                <td><input type="text" name="name" value="<?= htmlspecialchars($edit_data['name'] ?? '') ?>" required></td>
            </tr>
            <tr>
                <td>Description:</td>
                <td><textarea name="description" rows="2" cols="30"><?= htmlspecialchars($edit_data['description'] ?? '') ?></textarea></td>
            </tr>
            <tr>
                <td colspan="2"><button type="submit" name="save_dept"><?= $edit_data ? 'Update' : 'Save' ?></button></td>
            </tr>
        </table>
    </form>

    <h1>Department List</h1>
    <table border="1" cellpadding="5">
        <thead>
            <tr><th>ID</th><th>Name</th><th>Description</th><th>Action</th></tr>
        </thead>
        <tbody>
            <?php if(count($departments) > 0): ?>
                <?php foreach($departments as $dept): ?>
                    <tr>
                        <td><?= $dept['id'] ?></td>
                        <td><?= htmlspecialchars($dept['name']) ?></td>
                        <td><?= htmlspecialchars($dept['description']) ?></td>
                        <td>
                            <a href="department.php?edit_id=<?= $dept['id'] ?>">Update</a> |
                            <a href="department.php?delete_id=<?= $dept['id'] ?>" onclick="return confirm('Delete?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4">No departments found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>