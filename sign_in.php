<?php
// Start a session
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "clinic_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}




// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, password_hash FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($id, $username, $password_hash);
    $stmt->fetch();

    if ($id && password_verify($password, $password_hash)) {
        $_SESSION['user_id'] = $id;
        $_SESSION['username'] = $username;
        header("Location: index.php");
        exit;
    } else {
        $error_message = "Invalid email or password.";
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
    <title>Sign In</title>
    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 60px;
        }
        .signin-container {
            max-width: 400px;
            margin: 0 auto;
            background-color: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .btn-primary {
            width: 100%;
        }
        .form-label {
            font-weight: bold;
        }
        .logo {
            width: 200px;
            display: block;
            margin: 0 auto;
        }
        .error-message {
            color: red;
            font-size: 14px;
            text-align: center;
        }
        .footer-link {
            text-align: center;
            margin-top: 20px;
        }
        .footer-link a {
            text-decoration: none;
            font-weight: bold;
        }
        .footer-link a:hover {
            text-decoration: underline;
        }
       
        
    </style>
</head>
<body>

    <div class="container">
        <div class="signin-container">
            <!-- Clinic Logo (can replace this with the actual logo) -->
            <img src="assets/logo.png" alt="Clinic Logo" class="logo" > <!-- Replace logo.png with actual logo image -->
            <h2 class="text-center mb-4 " style="color:#0b3d6e; "><b>Physical Clinic</b></h2>
            <h6 class="text-center mb-4">Sign In to Your Account</h6>

            <!-- Sign In Form -->
            <form method="POST" action="sign_in.php">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary">Sign In</button>
            </form>

            <!-- Error message -->
            <?php if (isset($error_message)): ?>
                <div class="error-message">
                    <?= $error_message ?>
                </div>
            <?php endif; ?>

            <div class="footer-link">
                <p>Don't have an account? <a href="sign_up.php">Sign Up</a></p>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
