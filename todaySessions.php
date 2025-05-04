<?php
// Start a session
session_start();

// Redirect to login if the user is not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: sign_in.php");
    exit;
}

// Database connection
$conn = new mysqli("localhost", "root", "", "clinic_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$sessions = [];
$totalIncome = 0;
$selectedDate = '';
$selectedDoctor = '';

// Fetch distinct doctor names for the dropdown
$doctors = [];
$doctorQuery = $conn->query("SELECT DISTINCT doctor_name FROM session");
if ($doctorQuery) {
    while ($row = $doctorQuery->fetch_assoc()) {
        if (!empty($row['doctor_name'])) {
            $doctors[] = $row['doctor_name'];
        }
    }
} else {
    $doctors[] = "Error: " . $conn->error; // Debug in case of query failure
}

// Fetch sessions based on selected filters
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $selectedDate = $_POST['selected_date'] ?? '';
    $selectedDoctor = $_POST['selected_doctor'] ?? '';

    // Build the query dynamically
    $query = "
        SELECT session.session_id, session.session_type, session.session_time, session.price,
               session.doctor_name, patients.name AS patient_name
        FROM session
        INNER JOIN patients ON session.patient_id = patients.id
        WHERE session.session_date = ?";
    
    // Add doctor filter if selected
    if (!empty($selectedDoctor)) {
        $query .= " AND session.doctor_name = ?";
    }

    $stmt = $conn->prepare($query);
    if (!empty($selectedDoctor)) {
        $stmt->bind_param("ss", $selectedDate, $selectedDoctor);
    } else {
        $stmt->bind_param("s", $selectedDate);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $sessions[] = $row;
        $totalIncome += $row['price'];
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Today's Sessions</title>
    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f5f5f5;
            font-family: 'Arial', sans-serif;
            color: #333;
        }
        .container {
            max-width: 90%;
            margin-top: 50px;
        }
        .header {
            background-color: #0b3d6e;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
        }
        .table {
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .table th {
            background-color: #0b3d6e;
            color: white;
            text-align: center;
        }
        .table td {
            text-align: center;
        }
        .total-income {
            font-size: 1.5rem;
            font-weight: bold;
            color: #28a745;
            text-align: right;
            margin-top: 20px;
        }
        @media print {
            body * {
                visibility: hidden;
            }
            .print-area, .print-area * {
                visibility: visible;
            }
            .print-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Today's Sessions</h1>
    </div>

    <!-- Date & Doctor Selection Form -->
    <form method="POST" class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <input type="date" name="selected_date" class="form-control" value="<?= htmlspecialchars($selectedDate) ?>" required>
            </div>
            <div class="col-md-4">
                <select name="selected_doctor" class="form-control">
                    <option value="">All Doctors</option>
                    <?php foreach ($doctors as $doctor): ?>
                        <option value="<?= htmlspecialchars($doctor) ?>" <?= $selectedDoctor == $doctor ? 'selected' : '' ?>>
                            <?= htmlspecialchars($doctor) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Show Sessions</button>
            </div>
        </div>
    </form>

    <!-- Print Area -->
    <div class="print-area">
        <?php if ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
            <div class="mb-4">
                <strong>Selected Date:</strong> <?= htmlspecialchars($selectedDate) ?><br>
                <strong>Doctor:</strong> <?= empty($selectedDoctor) ? 'All Doctors' : htmlspecialchars($selectedDoctor) ?>
            </div>
        <?php endif; ?>

        <!-- Sessions Table -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Session ID</th>
                    <th>Patient Name</th>
                    <th>Doctor</th>
                    <th>Session Type</th>
                    <th>Session Time</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($sessions)): ?>
                    <?php foreach ($sessions as $session): ?>
                        <tr>
                            <td><?= htmlspecialchars($session['session_id']) ?></td>
                            <td><?= htmlspecialchars($session['patient_name']) ?></td>
                            <td><?= htmlspecialchars($session['doctor_name']) ?></td>
                            <td><?= htmlspecialchars($session['session_type']) ?></td>
                            <td><?= htmlspecialchars($session['session_time']) ?></td>
                            <td><?= htmlspecialchars($session['price']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No sessions found for the selected date.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Total Income -->
        <?php if ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
            <div class="total-income">Total Income: <?= number_format($totalIncome, 2) ?> EGP</div>
        <?php endif; ?>
    </div>

    <!-- Print & Home Buttons -->
    <a href="index.php" class="btn btn-success mt-3">Home</a>
    <button onclick="window.print()" class="btn btn-success mt-3">Print</button>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
