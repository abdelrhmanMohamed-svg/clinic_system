<?php
// Start session and include necessary authentication logic
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: sign_in.php");
    exit;
}

// Database connection
$conn = new mysqli("localhost", "root", "", "clinic_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle adding session
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_session'])) {
    // Check if session type is custom
    if ($_POST['session_type'] == "Custom") {
        $session_type = $_POST['custom_session_type'];
        $price = $_POST['custom_session_price']; // Get custom price
    } else {
        // Predefined session types and their prices
        $session_type = $_POST['session_type'];
        $sessionPrices = [
            "Neurological physiotherapy" => 500,
            "Musculoskeletal physiotherapy" => 400,
            "Pediatric physiotherapy" => 250,
            "Magnetic therapy" => 200,
            "Taping" => 50,
            "Joint mobilization" => 150,
            "Stretches and exercises" => 300,
            "Massage" => 900
        ];
        $price = $sessionPrices[$session_type] ?? 0; // Get price based on selected type
    }

    $session_date = $_POST['session_date'];
    $session_time = $_POST['session_time'];
    $doctor_id = $_POST['doctor_id'];
    $patient_id = $_POST['patient_id'];

    // Check if the patient already has a session at the same date and time
    $patient_check_query = $conn->prepare(
        "SELECT * FROM session WHERE patient_id = ? AND session_date = ? AND session_time = ?"
    );
    $patient_check_query->bind_param("iss", $patient_id, $session_date, $session_time);
    $patient_check_query->execute();
    $patient_check_result = $patient_check_query->get_result();

    if ($patient_check_result->num_rows > 0) {
        echo "<script>
                alert('You already have a session at the selected time and date.');
                window.location.href = 'add_session.php?patient_id=$patient_id';
              </script>";
        exit;
    }

    // Check if the selected doctor is already booked at the same date and time
    $availability_query = $conn->prepare(
        "SELECT * FROM session WHERE doctor_id = ? AND session_date = ? AND session_time = ?"
    );
    $availability_query->bind_param("iss", $doctor_id, $session_date, $session_time);
    $availability_query->execute();
    $availability_result = $availability_query->get_result();

    if ($availability_result->num_rows > 0) {
        echo "<script>
                alert('This doctor is already booked for the selected time. Please choose another time.');
                window.location.href = 'add_session.php?patient_id=$patient_id';
              </script>";
        exit;
    } else {
        // Fetch doctor name
        $doctor_query = $conn->prepare("SELECT name FROM doctor WHERE id = ?");
        $doctor_query->bind_param("i", $doctor_id);
        $doctor_query->execute();
        $doctor_result = $doctor_query->get_result();
        $doctor_name = $doctor_result->num_rows > 0 ? $doctor_result->fetch_assoc()['name'] : "";
        $doctor_query->close();

        // Insert the session into the database
        $stmt = $conn->prepare(
            "INSERT INTO session (session_type, session_date, session_time, doctor_id, doctor_name, patient_id, price) 
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("sssissi", $session_type, $session_date, $session_time, $doctor_id, $doctor_name, $patient_id, $price);

        if ($stmt->execute()) {
            echo "<script>
                    alert('Session added successfully!');
                    window.location.href = 'index.php';
                  </script>";
            exit;
        } else {
            echo "<script>alert('Error adding session.');</script>";
        }
        $stmt->close();
    }
    $availability_query->close();
}

// Get patient_id from query string
$patient_id = $_GET['patient_id'];  

// Get the selected date and time for availability check (if already set)
$session_date = isset($_POST['session_date']) ? $_POST['session_date'] : null;
$session_time = isset($_POST['session_time']) ? $_POST['session_time'] : null;

// Fetch available doctors based on selected date and time
if ($session_date && $session_time) {
    // Get doctors that are NOT booked at the selected date and time
    $query = "
        SELECT * FROM doctor 
        WHERE id NOT IN (
            SELECT doctor_id FROM session WHERE session_date = ? AND session_time = ?
        )
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $session_date, $session_time);
} else {
    $query = "SELECT * FROM doctor"; // Show all doctors if no date/time is selected
    $stmt = $conn->prepare($query);
}

$stmt->execute();
$doctor_result = $stmt->get_result();
$stmt->close();

// Fetch all session times for the patient on the selected date
$all_session_times_query = $conn->prepare(
    "SELECT session_time FROM session WHERE patient_id = ? AND session_date = ?"
);
$all_session_times_query->bind_param("is", $patient_id, $session_date);
$all_session_times_query->execute();
$all_session_times_result = $all_session_times_query->get_result();
$all_session_times = [];

while ($row = $all_session_times_result->fetch_assoc()) {
    $all_session_times[] = $row['session_time'];
}

$all_session_times_query->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Session</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
    <script>
        // Price mapping for session types
        const sessionPrices = {
            "Neurological physiotherapy": 500,
            "Musculoskeletal physiotherapy": 400,
            "Pediatric physiotherapy": 250,
            "Magnetic therapy": 200,
            "Taping": 50,
            "Joint mobilization": 150,
            "Stretches and exercises": 300,
            "Massage": 900
        };

        // Function to update price and handle custom session fields
        function updatePrice() {
            const sessionType = document.getElementById("session_type").value;
            const priceField = document.getElementById("price");
            const customFields = document.getElementById("custom-session-fields");

            // Hide or show the custom fields based on selection
            if (sessionType === "Custom") {
                customFields.style.display = 'block';
                priceField.value = ''; // Clear price for custom sessions
            } else {
                customFields.style.display = 'none';
                priceField.value = sessionPrices[sessionType] || 0;
            }
        }

        // Function to hide time slots that the patient already has a session for
        function hideUnavailableTimes() {
            const unavailableTimes = <?php echo json_encode($all_session_times); ?>;
            const timeSelect = document.getElementById("session_time");

            // Loop through all options and hide the ones that are unavailable
            for (let option of timeSelect.options) {
                if (unavailableTimes.includes(option.value)) {
                    option.disabled = true; // Disable the option if the patient already has a session at that time
                }
            }
        }
    </script>
</head>
<body onload="hideUnavailableTimes()">

<div class="container mt-5">
    <h2>Add Session for Patient ID: <?= $patient_id ?></h2>
    <form method="POST" action="add_session.php">
        <input type="hidden" name="patient_id" value="<?= $patient_id ?>">

        <!-- Session Type Dropdown -->
        <div class="mb-3">
            <label for="session_type" class="form-label">Session Type</label>
            <select class="form-select" id="session_type" name="session_type" required onchange="updatePrice()">
                <option value="">Select a session type</option>
                <option value="Neurological physiotherapy">Neurological physiotherapy</option>
                <option value="Musculoskeletal physiotherapy">Musculoskeletal physiotherapy</option>
                <option value="Pediatric physiotherapy">Pediatric physiotherapy</option>
                <option value="Magnetic therapy">Magnetic therapy</option>
                <option value="Taping">Taping</option>
                <option value="Joint mobilization">Joint mobilization</option>
                <option value="Stretches and exercises">Stretches and exercises</option>
                <option value="Massage">Massage</option>
                <option value="Custom">Custom</option> <!-- Option for custom session -->
            </select>
        </div>

        <!-- Custom Session Type and Price Fields -->
        <div id="custom-session-fields" class="mb-3" style="display: none;">
            <label for="custom_session_type" class="form-label">Custom Session Type</label>
            <input type="text" class="form-control" id="custom_session_type" name="custom_session_type" placeholder="Enter session type">
            
            <label for="custom_session_price" class="form-label">Custom Session Price (EGP)</label>
            <input type="number" class="form-control" id="custom_session_price" name="custom_session_price" placeholder="Enter session price">
        </div>

        <!-- Price Field -->
        <div class="mb-3">
            <label for="price" class="form-label">Price (EGP)</label>
            <input type="number" class="form-control" id="price" name="price" readonly required>
        </div>

        <!-- Session Date -->
        <div class="mb-3">
            <label for="session_date" class="form-label">Session Date</label>
            <input type="date" class="form-control" id="session_date" name="session_date" required>
        </div>

        <!-- Session Time -->
        <div class="mb-3">
            <label for="session_time" class="form-label">Session Time</label>
            <select class="form-select" id="session_time" name="session_time" required>
                <?php for ($hour = 10; $hour <= 21; $hour++): ?>
                    <option value="<?= sprintf("%02d:00", $hour) ?>"><?= sprintf("%02d:00", $hour) ?></option>
                <?php endfor; ?>
            </select>
        </div>

        <!-- Doctor Dropdown -->
        <div class="mb-3">
            <label for="doctor_id" class="form-label">Select Doctor</label>
            <select class="form-select" id="doctor_id" name="doctor_id" required>
                <?php while ($doctor = $doctor_result->fetch_assoc()): ?>
                    <option value="<?= $doctor['id'] ?>"><?= $doctor['name'] ?> (ID: <?= $doctor['id'] ?>)</option>
                <?php endwhile; ?>
            </select>
        </div>

        <!-- Submit Button -->
        <button type="submit" name="add_session" class="btn btn-primary">Add Session</button>
        <a href="index.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

</body>
</html>
