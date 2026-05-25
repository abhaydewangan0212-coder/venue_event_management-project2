<?php
session_start();
require 'db.php';

$error = "";

if (isset($_POST['login'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $conn->real_escape_string($_POST['password']);

    $sql = "SELECT * FROM admins WHERE username='$username' AND password='$password'";
    $res = $conn->query($sql);

    if ($res->num_rows == 1) {
        $admin = $res->fetch_assoc();
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        header("Location: admin_dashboard.php");
        exit;
    } else {
        $error = "Invalid username or password";
    }
}

include 'header.php';
?>
<head>
    <style>
body {
    margin: 0;
    padding: 0;
    background: url("images/bg2.jpg") no-repeat center center fixed;
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
<h2>Admin Login</h2>

<?php if ($error): ?>
<div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>



<form method="post" class="mt-3 col-md-6">
    <div class="mb-3">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control"
        placeholder="Enter your username" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control"
        placeholder="Enter your password" required>
    </div>
    <button type="submit" name="login" class="btn btn-primary">Login</button>
</form>

<?php include 'footer.php'; ?>