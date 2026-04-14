<?php
require_once 'db.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$employees = [];

$sql = "SELECT e.id, e.firstname, e.lastname, d.name as department_name, d.description as department_description
        FROM employees e
        JOIN departments d ON e.department_id = d.id";

if (!empty($search)) {
    $search_param = "%$search%";
    $sql .= " WHERE e.firstname LIKE ? OR e.lastname LIKE ? OR d.name LIKE ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $search_param, $search_param, $search_param);
} else {
    $stmt = $conn->prepare($sql);
}
$stmt->execute();
$employees = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Complete Employee Report</title>
</head>
<body>
    <p><a href="index.html">← Back to Home</a></p>

    <h1>Complete Employee Report</h1>
    
    <form method="get">
        <input type="text" name="search" placeholder="Search by employee name or department" value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Search</button>
        <a href="display.php">Reset</a>
    </form>

    <table border="1" cellpadding="5">
        <thead>
            <tr><th>ID</th><th>Firstname</th><th>Lastname</th><th>Department</th><th>Description</th></tr>
        </thead>
        <tbody>
            <?php if(count($employees) > 0): ?>
                <?php foreach($employees as $emp): ?>
                    <tr>
                        <td><?= $emp['id'] ?></td>
                        <td><?= htmlspecialchars($emp['firstname']) ?></td>
                        <td><?= htmlspecialchars($emp['lastname']) ?></td>
                        <td><?= htmlspecialchars($emp['department_name']) ?></td>
                        <td><?= htmlspecialchars($emp['department_description']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5">No records found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>