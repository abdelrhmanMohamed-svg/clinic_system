<?php
// Start session
session_start();

// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: sign_in.php");
    exit;
}

// Database connection
$conn = new mysqli("localhost", "root", "", "clinic_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if session_id is passed as a GET parameter
if (isset($_GET['session_id'])) {
    $session_id = $_GET['session_id'];

    // Make sure to use the correct column name (session_id in this case)
    $stmt = $conn->prepare("DELETE FROM session WHERE session_id = ?");
    $stmt->bind_param("i", $session_id);

    if ($stmt->execute()) {
        echo "<script>alert('Session deleted successfully!');</script>";
    } else {
        echo "<script>alert('Error deleting session.');</script>";
    }

    // Close the statement
    $stmt->close();
}

// Redirect back to the main page
header("Location: index.php");
exit;
?>
