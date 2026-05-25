<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT * FROM users WHERE id = $user_id");
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
.profile-pic-wrapper {
    text-align: center;
    margin-bottom: 30px;
}

.profile-pic {
    width: 140px;
    height: 140px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #0d6efd;
    cursor: pointer;
}

.change-text {
    display: block;
    color: #0d6efd;
    font-size: 14px;
    margin-top: 8px;
    cursor: pointer;
}
</style>
</head>
<body class="bg-light">

<div class="container mt-5 d-flex justify-content-center">
    <div class="card shadow p-4" style="width: 420px; border-radius: 15px;">

        <h4 class="text-center mb-3">Edit Profile</h4>

        <form method="POST" action="update_profile.php" enctype="multipart/form-data">

    <div class="profile-pic-wrapper">
    <label for="profile_photo">
        <img 
            src="<?= !empty($user['profile_photo']) 
                ? 'images/'.$user['profile_photo'] 
                : 'images/bg.jpg'; ?>" 
            id="profilePreview"
            class="profile-pic"
        >
        <span class="change-text">Change Photo</span>
    </label>

    <input 
        type="file" 
        name="profile_photo" 
        id="profile_photo"
        accept="image/*"
        hidden
        onchange="previewImage(this)"
    >
</div>
        <!-- Name -->
        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control"
                   value="<?= htmlspecialchars($user['name']) ?>" required>
        </div>

        <!-- Email (readonly) -->
        <div class="mb-3">
            <label>Email</label>
            <input type="email" class="form-control"
                   value="<?= htmlspecialchars($user['email']) ?>" readonly>
        </div>

        <!-- Contact Number -->
        <div class="mb-3">
            <label>Contact Number</label>
            <input type="text" name="contact" class="form-control"
                   value="<?= htmlspecialchars($user['contact']) ?>">
        </div>

        <!-- Address -->
        <div class="mb-3">
            <label>Address</label>
            <textarea name="address" class="form-control"><?= htmlspecialchars($user['address']) ?></textarea>
        </div>

        <!-- Date of Birth -->
        <div class="mb-3">
            <label>Date of Birth</label>
            <input type="date" name="dob" class="form-control"
                   value="<?= $user['dob'] ? date('Y-m-d', strtotime($user['dob'])) : '' ?>">
        </div>

        <!-- Gender -->
        <div class="mb-3">
            <label>Gender</label>
            <select name="gender" class="form-control">
                <option value="">Select</option>
                <option value="Male" <?= $user['gender']=='Male' ? 'selected' : '' ?>>Male</option>
                <option value="Female" <?= $user['gender']=='Female' ? 'selected' : '' ?>>Female</option>
                <option value="Other" <?= $user['gender']=='Other' ? 'selected' : '' ?>>Other</option>
            </select>
        </div>

      

        <button type="submit" class="btn btn-primary">Update Profile</button>

    </form>
</div>
<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById('profilePreview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
</body>
</html>