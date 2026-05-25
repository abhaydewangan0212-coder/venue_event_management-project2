<?php
session_start();
require 'db.php';

$error = "";

if (isset($_POST['login'])) {

    $login_id = $conn->real_escape_string($_POST['login_id']);
    $pass     = $_POST['password'];

    if (filter_var($login_id, FILTER_VALIDATE_EMAIL)) {
        $sql = "SELECT * FROM users WHERE email='$login_id'";
    } elseif (preg_match('/^[0-9]{10}$/', $login_id)) {
        $sql = "SELECT * FROM users WHERE contact='$login_id'";
    } else {
        $error = "Enter valid email or 10-digit phone number";
    }

    if ($error == "") {

        $res = $conn->query($sql);

        if ($res && $res->num_rows == 1) {

            $user = $res->fetch_assoc();
            

            if ($pass === $user['password']) {

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['profile_photo'] = $user['profile_photo'];

                header("Location: index.php");
                exit;

            } else {
                $error = "Invalid password";
            }

        } else {
            $error = "User not found";
        }
    }
}

include 'header.php';
?>

<h2>User Login</h2>
<head>
<style>
body {
    margin: 0;
    padding: 0;
    background: url("images/bg.jpg") no-repeat center center fixed;
    background-size: cover;
    font-family: Arial, sans-serif;
}
.login-box {
    width: 380px;
    padding: 25px;
    margin: 120px auto;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(8px);
    box-shadow: 0 0 10px rgba(0,0,0,0.4);
    border-radius: 15px;
    color: #fff;
}
.login-box input, .login-box button {
    width: 100%;
    padding: 12px;
    border-radius: 6px;
    margin-top: 10px;
    border: none;
}
.login-box button {
    background: #ff5e57;
    color: white;
    cursor: pointer;
}
.login-box button:hover {
    background: #ff2e20;
}
</style>
</head>

<?php if ($error): ?>
<div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<form method="post" class="mt-3 col-md-6">
    <div class="mb-3">
        <label class="form-label">Email or Contact</label>
        <input type="text" name="login_id" class="form-control" 
        placeholder="Email or 10-digit Phone number." required>
    </div>
    <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" 
        placeholder="Enter your password." required>
    </div>
    <button type="submit" name="login" class="btn btn-primary">Login</button>
    <a href="user_register.php" class="btn btn-link">Create new account</a>
    <br>
<a href="forgot_password.php">Forgot Password?</a>
</form>

<?php include 'footer.php'; ?>