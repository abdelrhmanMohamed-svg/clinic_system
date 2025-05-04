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

// Fetch all patients
$result = $conn->query("SELECT * FROM patients");

// Fetch sessions for each patient
function getSessions($patient_id, $conn) {
    $stmt = $conn->prepare("SELECT * FROM session WHERE patient_id = ?");
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    return $stmt->get_result();
}

// Update patient details (handle form submission)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $age = $_POST['age'];

    $stmt = $conn->prepare("UPDATE patients SET name = ?, email = ?, phone = ?, gender = ?, address = ?, age = ? WHERE id = ?");
    $stmt->bind_param("ssssssi", $name, $email, $phone, $gender, $address, $age, $id);

    if ($stmt->execute()) {
        echo "<script>alert('Patient updated successfully!');</script>";
        header("Location: index.php");
        exit;
    } else {
        echo "<script>alert('Error updating patient.');</script>";
    }

    $stmt->close();
}
// Define $searchQuery to avoid undefined variable errors
$searchQuery = "";

if (isset($_GET['search'])) {
    $searchQuery = $_GET['search'];
    if (is_numeric($searchQuery)) {
        $sql = "SELECT * FROM patients WHERE id = '$searchQuery'";
    } else {
        $sql = "SELECT * FROM patients WHERE name LIKE '%$searchQuery%'";
    }
} else {
    $sql = "SELECT * FROM patients";
}
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clinic Management</title>
    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f5f5f5;
            font-family: 'Arial', sans-serif;
            color: #333;
        }
        .container {
            max-width: 90%; /* Removes the limit for the container width */
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
        .header .logo {
            width: 200px;
            height: auto;
            margin-right: 10px;
        }
        .header h1 {
            font-size: 1.7rem;
            font-weight: bold;
            margin: 0;
            padding-top: 10px;
        }
        .header p {
            font-size: 1.2rem;
            font-style: italic;
            margin: 5px 0 0;
        }
        .welcome-msg {
            font-size: 1.5rem;
            font-weight: bold;
            color: #007bff;
            padding: 10px;
            background-color: #e9f4fb;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            text-align: center;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        .table {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            background-color: white;
        }
        .table th, .table td {
            padding: 1rem;
            text-align: center;
        }
        .table th {
            background-color: #0b3d6e;
            color: white;
        }
        .btn-success, .btn-danger {
            width: 100px;
        }
        .modal-header {
            background-color: #007bff;
            color: white;
        }
        .modal-content {
            border-radius: 10px;
        }
        .modal-footer {
            justify-content: space-between;
        }
        .error-message {
            color: red;
            font-size: 14px;
            text-align: center;
        }
        .logout-btn {
            background-color: #f39c12;
            border-color: #f39c12;
            color: white;
        }
        .logout-btn:hover {
            background-color: #e67e22;
            border-color: #e67e22;
        }
        .navbar {
            position: absolute;
            right: 20px;
            top: 20px;
        }
    </style>
</head>
<body>

<div class="header">
    <img src="assets/logo.png" alt="Clinic Logo" class="logo">
    <h1>Physical Clinic</h1>
</div>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <div class="d-flex">
            <span class="navbar-text">
                Welcome, <?= $_SESSION['username']; ?>!
            </span>
            <a href="logout.php" class="btn logout-btn ms-3">Logout</a>
        </div>
    </div>
</nav>

<div class="container d-flex justify-content-between ms-0 ">
    <div class="d-flex mb-3 w-100">
        <!-- Search form on the left -->
        <form class="d-flex me-auto" role="search">
            <input class="form-control me-2" type="search" name="search" placeholder="Search by ID or Name" aria-label="Search" value="<?= htmlspecialchars($searchQuery); ?>">
            <button class="btn btn-outline-success" type="submit">Search</button>
        </form>
        
        <!-- 4 buttons on the absolute right -->
        <div class="d-flex position-absolute" style="right: 0;">
            <a href="todaySessions.php" class="btn btn-primary ms-3">Today's Sessions</a>
            <a href="schedule_doctors.php" class="btn btn-primary ms-3">Schedule Doctors</a>
            <a href="add_patient.php" class="btn btn-primary ms-3">Add New Patient</a>
            <a href="doctor_page.php" class="btn btn-primary ms-3 me-3">Manage Doctors</a>
        </div>
    </div>
</div>

    

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Gender</th>
                <th>Address</th>
                <th>Age</th>
                <th>Joining Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['name'] ?></td>
                        <td><?= $row['email'] ?></td>
                        <td><?= $row['phone'] ?></td>
                        <td><?= $row['gender'] ?></td>
                        <td><?= $row['address'] ?></td>
                        <td><?= $row['age'] ?></td>
                        <td><?= $row['joining_date'] ?></td>
                        <td>
                            <div class="d-flex justify-content-start">
                                <!-- View Sessions Button -->
                                <button id="view-sessions-btn-<?= $row['id'] ?>" class="btn btn-info me-2" onclick="toggleSessions(<?= $row['id'] ?>)" style="background-color:#28A745;">
                                    View Sessions
                                </button>

                                <!-- Edit Button -->
                                <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-primary me-2" style="background-color:#006BFF;">Edit</a>

                                <!-- Add Session Button -->
                                <a href="add_session.php?patient_id=<?= $row['id'] ?>" class="btn btn-primary me-2" style="background-color:#024CAA;">Add Session</a>

                                <!-- Delete Button -->
                                <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-primary" style="background-color:#EC8305;" onclick="return confirm('Are you sure you want to delete this patient?');">Delete</a>
                            </div>
                        </td>
                    </tr>

                    <!-- Session Rows, hidden initially -->
                    <tr id="sessions-row-<?= $row['id'] ?>" class="session-row" style="display: none;">
                        <td colspan="9">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Session Type</th>
                                        <th>Session Date</th>
                                        <th>Doctor</th>
                                        <th>Time</th> 
                                        <th>Price</th>
                                        <th>checked</th>  
                                        <th>delete<th>
                                    </tr>
                                </thead>
                                <tbody>
    <?php
    // Get sessions for this patient
    $sessions = getSessions($row['id'], $conn);
    $currentDate = date("Y-m-d"); // Current date
    if ($sessions->num_rows > 0):
        while ($session = $sessions->fetch_assoc()):
            $isPast = (strtotime($session['session_date']) < strtotime($currentDate));
    ?>
            
</td>

<tr>
    <td><?= $session['session_type'] ?></td>
    <td><?= $session['session_date'] ?></td>
    <td><?= $session['doctor_name'] ?></td>
    <td><?= $session['session_time'] ?></td>
    <td><?= $session['price'] ?></td>
    <td>
        <input type="checkbox" <?= $isPast ? 'checked' : '' ?> disabled>
        <span><?= $isPast ? '✔' : '✘' ?></span>
    </td>
    <td>
    <a href="delete_session.php?session_id=<?= $session['session_id'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this session?');">Delete</a>

    </td>
</tr>

    <?php
        endwhile;
    else:
    ?>
        <tr><td colspan="6" class="text-center">No sessions found</td></tr>
    <?php endif; ?>
</tbody>

                            </table>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" class="text-center">No patients found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Toggle the visibility of the sessions for each patient and update the button text
    function toggleSessions(patientId) {
        var sessionsRow = document.getElementById('sessions-row-' + patientId);
        var viewButton = document.getElementById('view-sessions-btn-' + patientId);
        
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

