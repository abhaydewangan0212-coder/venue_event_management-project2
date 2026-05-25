<?php
session_start();
require 'db.php';

$message = "";

if (isset($_POST['reset'])) {
    $contact = $conn->real_escape_string($_POST['contact']);
    $new_pass = $conn->real_escape_string($_POST['new_password']);

    // ✅ contact column used
    $check = $conn->query(
        "SELECT * FROM users WHERE email='$contact' OR contact='$contact'"
    );

    if ($check->num_rows == 1) {
        $conn->query(
            "UPDATE users SET password='$new_pass'
             WHERE email='$contact' OR contact='$contact'"
        );
        $message = "✅ Password reset successful!";
    } else {
        $message = "❌ User not found!";
    }
}
?>

<?php include 'header.php'; ?>

<div class="login-box">
    <h2>Forgot Password</h2>

    <?php if ($message != "") echo "<p>$message</p>"; ?>

    <form method="post">
        <label>Email or 10-digit Contact</label>
        <input type="text" name="contact" required>

        <label>New Password</label>
        <input type="password" name="new_password" required>

        <button type="submit" name="reset">Reset Password</button>
    </form>

    <br>
    <a href="user_login.php">← Back to Login</a>
</div>

<?php include 'footer.php'; ?>