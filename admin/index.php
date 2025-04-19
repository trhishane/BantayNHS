<?php  
session_start();
include('includes/dbconn.php');

$errorMessage = "";

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $sql = "SELECT superAdminId, username, password, role FROM tblsuperadmin WHERE username = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Query failed: " . $conn->error);
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        // Debugging output (remove this after testing)
        // echo "Entered: " . $password . "<br>";
        // echo "Stored: " . $user['password'] . "<br>";
        // var_dump($password === $user['password']); 
        // die();

        if ($password === $user['password']) { 
            // ✅ Store session
            $_SESSION['superAdminId'] = $user['superAdminId'];
            $_SESSION['role'] = $user['role'];

            // ✅ Redirect based on role
            if ($user['role'] == 'Admin') {
                header("Location: dashboard.php");
            } elseif ($user['role'] == 'Principal') {
                header("Location: ../principal/dashboard.php");
            }
            exit();
        } else {
            $errorMessage = 'Incorrect Password'; 
        }
    } else {
        $errorMessage = 'Username not found';
    }
}
?>

<?php include('includes/links.php'); ?>

<body class="" style="background-image: url('../assets/System Images/sp-bg.png'); background-size:cover;">
    <div class="container d-flex align-items-center vh-100" style="width: 400px">
        <div class="bg-white border border-1 rounded-4 p-3 w-100">
            <div class="text-center mb-2">
                <img src="../assets/System Images/Bulag Logo.jpg" style="width: 100px">
            </div>
            <div class="card-body">
                <h4 class="text-center">Super Admin Login</h4>
                <hr>
                <?php if (!empty($errorMessage)) : ?>
                    <div class="alert alert-danger text-center"> <?= $errorMessage; ?> </div>
                <?php endif; ?>
                <form action="index.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="showPassword" onclick="togglePassword()">
                        <label class="form-check-label" for="showPassword">Show Password</label>
                    </div>
                    <div style="text-align: center;">
                        <button type="submit" name="login" class="btn btn-primary w-50">Login</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            var passwordField = document.getElementById("password");
            passwordField.type = passwordField.type === "password" ? "text" : "password";
        }
    </script>
</body>  
