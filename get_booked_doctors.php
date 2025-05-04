<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "clinic_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['date']) && isset($_GET['time'])) {
    $session_date = $_GET['date'];
    $session_time = $_GET['time'];

    // Fetch booked doctors for the selected date and time
    $query = "SELECT doctor_id FROM session WHERE session_date = ? AND session_time = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $session_date, $session_time);
    $stmt->execute();
    $result = $stmt->get_result();

    $booked_doctors = [];
    while ($row = $result->fetch_assoc()) {
        $booked_doctors[] = $row['doctor_id'];
    }

    echo json_encode($booked_doctors);
}
$conn->close();
?>
