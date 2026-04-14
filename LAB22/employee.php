<?php
require_once 'db.php';
session_start();

// Delete
if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM employees WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    $_SESSION['msg'] = "Employee deleted.";
    header("Location: employee.php");
    exit();
}

// Edit fetch
$edit_id = isset($_GET['edit_id']) ? (int)$_GET['edit_id'] : null;
$edit_data = null;
if ($edit_id) {
    $stmt = $conn->prepare("SELECT * FROM employees WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Save / Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_employee'])) {
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $gender = $_POST['gender'];
    $department_id = (int)$_POST['department_id'];
    $update_id = isset($_POST['update_id']) ? (int)$_POST['update_id'] : null;

    if ($update_id) {
        $stmt = $conn->prepare("UPDATE employees SET firstname=?, lastname=?, gender=?, department_id=? WHERE id=?");
        $stmt->bind_param("sssii", $firstname, $lastname, $gender, $department_id, $update_id);
        $stmt->execute();
        $_SESSION['msg'] = "Employee updated.";
    } else {
        $stmt = $conn->prepare("INSERT INTO employees (firstname, lastname, gender, department_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $firstname, $lastname, $gender, $department_id);
        $stmt->execute();
        $_SESSION['msg'] = "Employee added.";
    }
    $stmt->close();
    header("Location: employee.php");
    exit();
}

// Search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sql = "SELECT e.id, e.firstname, e.lastname, e.gender, d.name as department_name 
        FROM employees e 
        JOIN departments d ON e.department_id = d.id";
if (!empty($search)) {
    $search_param = "%$search%";
    $sql .= " WHERE e.firstname LIKE ? OR e.lastname LIKE ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $search_param, $search_param);
} else {
    $stmt = $conn->prepare($sql);
}
$stmt->execute();
$employee_list = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$departments = $conn->query("SELECT id, name FROM departments ORDER BY name")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Employee Management</title>
</head>
<body>
    <p><a href="index.html">← Back to Home</a></p>

    <h1>Employee Registration</h1>
    <?php if(isset($_SESSION['msg'])): ?>
        <p><strong><?= $_SESSION['msg']; unset($_SESSION['msg']); ?></strong></p>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="update_id" value="<?= $edit_data['id'] ?? '' ?>">
        <table border="0">
            <tr>
                <td>First Name:</td>
                <td><input type="text" name="firstname" value="<?= htmlspecialchars($edit_data['firstname'] ?? '') ?>" required></td>
            </tr>
            <tr>
                <td>Last Name:</td>
                <td><input type="text" name="lastname" value="<?= htmlspecialchars($edit_data['lastname'] ?? '') ?>" required></td>
            </tr>
            <tr>
                <td>Gender:</td>
                <td>
                    <select name="gender" required>
                        <option value="">Select</option>
                        <option value="Male" <?= (isset($edit_data['gender']) && $edit_data['gender'] == 'Male') ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?= (isset($edit_data['gender']) && $edit_data['gender'] == 'Female') ? 'selected' : '' ?>>Female</option>
                        <option value="Other" <?= (isset($edit_data['gender']) && $edit_data['gender'] == 'Other') ? 'selected' : '' ?>>Other</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Department:</td>
                <td>
                    <select name="department_id" required>
                        <option value="">Select Department</option>
                        <?php foreach($departments as $dept): ?>
                            <option value="<?= $dept['id'] ?>" <?= (isset($edit_data['department_id']) && $edit_data['department_id'] == $dept['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($dept['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td colspan="2"><button type="submit" name="save_employee"><?= $edit_data ? 'Update' : 'Save' ?></button></td>
            </tr>
        </table>
    </form>

    <hr>
    <h2>Search Employee</h2>
    <form method="get">
        <input type="text" name="search" placeholder="First name or Last name" value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Search</button>
        <a href="employee.php">Reset</a>
    </form>

    <h1>Employee List</h1>
    <table border="1" cellpadding="5">
        <thead>
            <tr><th>ID</th><th>Firstname</th><th>Lastname</th><th>Gender</th><th>Department Name</th><th>Action</th></tr>
        </thead>
        <tbody>
            <?php if(count($employee_list) > 0): ?>
                <?php foreach($employee_list as $emp): ?>
                    <tr>
                        <td><?= $emp['id'] ?></td>
                        <td><?= htmlspecialchars($emp['firstname']) ?></td>
                        <td><?= htmlspecialchars($emp['lastname']) ?></td>
                        <td><?= $emp['gender'] ?></td>
                        <td><?= htmlspecialchars($emp['department_name']) ?></td>
                        <td>
                            <a href="employee.php?edit_id=<?= $emp['id'] ?>">Update</a> |
                            <a href="employee.php?delete_id=<?= $emp['id'] ?>" onclick="return confirm('Delete?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6">No employees found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>