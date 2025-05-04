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

// Fetch all doctors
$doctor_result = $conn->query("SELECT * FROM doctor");

// Function to fetch sessions for each doctor
function getDoctorSessions($doctor_id, $conn) {
    $stmt = $conn->prepare("SELECT * FROM session WHERE doctor_id = ?");
    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();
    return $stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Doctors</title>
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
            padding: 30px 20px;
            width: 400px;
            text-align: center;
            margin-bottom: 10px;
            margin-left: 550px;
            border-radius: 0px 0px 20px 20px;
        }
        .navbar {
            position: absolute;
            right: 20px;
            top: 20px;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        .table th, .table td {
            padding: 1rem;
            text-align: center;
        }
        .table th {
            background-color: #0b3d6e;
            color: white;
        }
        .session-row {
            display: none; /* Ensure sessions are hidden by default */
        }
    </style>
</head>
<body>

<div class="header">
    <h1>Schedule Doctors</h1>
</div>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <div class="d-flex">
              <!-- Home Button -->
              <a href="index.php" class="btn btn-secondary me-3">Home</a>
            <span class="navbar-text">

                Welcome, <?= $_SESSION['username']; ?>!
            </span>
            <a href="logout.php" class="btn logout-btn ms-3">Logout</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="mb-3 text-end">
        <a href="doctor_page.php" class="btn btn-primary">Manage Doctors</a> <!-- Link back to Manage Doctors -->
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Gender</th>
                <th>Phone</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($doctor_result->num_rows > 0): ?>
                <?php while ($row = $doctor_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['name'] ?></td>
                        <td><?= $row['gender'] ?></td>
                        <td><?= $row['phone'] ?></td>
                        <td>
                            <!-- View Sessions Button -->
                            <button id="view-sessions-btn-<?= $row['id'] ?>" class="btn btn-info" onclick="toggleSessions(<?= $row['id'] ?>)">
                                View Sessions
                            </button>
                        </td>
                    </tr>

                    <!-- Session Rows -->
                    <tr id="sessions-row-<?= $row['id'] ?>" class="session-row">
                        <td colspan="5">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Session Type</th>
                                        <th>Session Date</th>
                                        <th>Time</th>
                                        <th>Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Get sessions for this doctor
                                    $sessions = getDoctorSessions($row['id'], $conn);
                                    if ($sessions->num_rows > 0):
                                        while ($session = $sessions->fetch_assoc()):
                                    ?>
                                            <tr>
                                                <td><?= $session['session_type'] ?></td>
                                                <td><?= $session['session_date'] ?></td>
                                                <td><?= $session['session_time'] ?></td>
                                                <td><?= $session['price'] ?></td>
                                            </tr>
                                    <?php
                                        endwhile;
                                    else:
                                    ?>
                                        <tr><td colspan="4" class="text-center">No sessions found</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center">No doctors found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Toggle the visibility of the sessions for each doctor and update the button text
    function toggleSessions(doctorId) {
        var sessionsRow = document.getElementById('sessions-row-' + doctorId);
        var viewButton = document.getElementById('view-sessions-btn-' + doctorId);
        
        if (sessionsRow.style.display === 'none' || sessionsRow.style.display === '') {
            sessionsRow.style.display = 'table-row';
            viewButton.textContent = 'Hide Sessions';  // Change text to 'Hide Sessions'
        } else {
            sessionsRow.style.display = 'none';
            viewButton.textContent = 'View Sessions';  // Change text to 'View Sessions'
        }
    }
</script>

</body>
</html>
<?php
// Close the connection at the end of the script
$conn->close();
?>
