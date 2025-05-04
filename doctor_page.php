<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "clinic_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch doctors and calculate their balance
$doctor_result = $conn->query("
    SELECT d.*, COALESCE(SUM(s.price * 0.5), 0) AS balance
    FROM doctor d
    LEFT JOIN session s ON d.name = s.doctor_name
    GROUP BY d.id
");

// Handle adding a new doctor
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_doctor'])) {
    $name = $_POST['name'];
    $gender = $_POST['gender'];
    $salary = $_POST['salary'];
    $phone = $_POST['phone'];

    $stmt = $conn->prepare("INSERT INTO doctor (name, gender, salary, phone) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssds", $name, $gender, $salary, $phone);

    if ($stmt->execute()) {
        header("Location: doctor_page.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Handle deleting a doctor
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM doctor WHERE id = ?");
    $stmt->bind_param("i", $delete_id);

    if ($stmt->execute()) {
        header("Location: doctor_page.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f5f5f5;
            font-family: 'Arial', sans-serif;
            color: #333;
        }
        .container { margin-top: 50px; }
        .header { background-color: #0b3d6e; color: white; text-align: center; padding: 20px; border-radius: 10px; }
        .table th { background-color: #0b3d6e; color: white; }
        .btn-success, .btn-danger { width: 100px; }
    </style>
</head>
<body>
<div class="header">
    <h1>Doctor Management</h1>
</div>

<div class="container">
    <h3 class="mt-5">List of Doctors</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Gender</th>
                <th>Salary</th>
                <th>Phone</th>
                <th>Balance</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $doctor_result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['name'] ?></td>
                    <td><?= $row['gender'] ?></td>
                    <td><?= $row['salary'] ?></td>
                    <td><?= $row['phone'] ?></td>
                    <td><?= number_format($row['balance'], 2) ?>EGP</td>
                    <td>
                        <a href="doctor_page.php?delete_id=<?= $row['id'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this doctor?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <hr>
    <h4>Add New Doctor</h4>
    <form method="POST" action="doctor_page.php">
        <div class="mb-3">
            <label for="name" class="form-label">Doctor's Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="gender" class="form-label">Gender</label>
            <select class="form-select" id="gender" name="gender" required>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="salary" class="form-label">Salary</label>
            <input type="number" class="form-control" id="salary" name="salary" required>
        </div>
        <div class="mb-3">
            <label for="phone" class="form-label">Phone</label>
            <input type="text" class="form-control" id="phone" name="phone" required>
        </div>
        <button type="submit" name="add_doctor" class="btn btn-primary">Add Doctor</button>
        <a href="index.php" class="btn btn-primary">Home</a>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
