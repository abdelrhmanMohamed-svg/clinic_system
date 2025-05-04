<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "clinic_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Hash the password for security
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // Insert the user into the database
    $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $password_hash);

    if ($stmt->execute()) {
        // Displaying the announcement message instead of echoing directly
        $success_message = "Registration successful! You can now <a href='sign_in.php'>log in</a>.";
    } else {
        $error_message = "Error: " . $stmt->error;
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
    <title>Sign Up</title>
    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 50px;
        }
        .signup-container {
            max-width: 450px;
            margin: 0 auto;
            background-color: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .btn-primary {
            width: 100%;
        }
        .form-label {
            font-weight: bold;
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
        }
        .go-to-sign-in-btn {
            text-align: center;
            margin-top: 20px;
        }
        .announcement-message {
            margin-top: 20px;
            text-align: center;
            padding: 15px;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            border-radius: 5px;
        }
        .logo {
            width: 200px;
            display: block;
            margin: 0 auto;
        }
    </style>
</head>
<body>

    <div class="container">
        
        <div class="signup-container">
    
        <img src="assets/logo.png" alt="Clinic Logo" class="logo" > <!-- Replace logo.png with actual logo image -->
        <h2 class="text-center mb-4 " style="color:#0b3d6e; "><b>Physical Clinic</b></h2>
        
            <h4 class="text-center mb-4">Create Your Account</h4>
            <form method="POST" action="sign_up.php">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary">Sign Up</button>
            </form>

            <!-- Display the success or error message -->
            <?php if (isset($success_message)): ?>
                <div class="announcement-message">
                    <?= $success_message ?>
                </div>
            <?php elseif (isset($error_message)): ?>
                <div class="error-message"><?= $error_message ?></div>
            <?php endif; ?>

            <div class="footer-link">
                <p>Already have an account? <a href="sign_in.php">Sign In</a></p>
            </div>

           

        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
