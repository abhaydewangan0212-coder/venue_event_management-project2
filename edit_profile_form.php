<?php

require 'db.php';


$user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT * FROM users WHERE id = $user_id");
$user = $result->fetch_assoc();
?>

<div class="card shadow p-4" style="border-radius:15px;">
    
    <!-- Profile Picture -->
    <div class="text-center mb-4">
        <label for="profile_photo">
            <img 
                src="<?= !empty($user['profile_photo']) ? 'images/'.$user['profile_photo'] : 'images/bg.jpg'; ?>"
                id="profilePreview"
                class="rounded-circle border border-primary"
                style="width:130px;height:130px;object-fit:cover;cursor:pointer;"
            >
            <div class="text-primary mt-2" style="font-size:14px;">
                Change Photo
            </div>
        </label>

        <input type="file" name="profile_photo" id="profile_photo" hidden onchange="previewImage(this)">
    </div>

    <form method="POST" action="update_profile.php" enctype="multipart/form-data">

        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control" value="<?= $user['name'] ?>" required>
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input type="email" class="form-control" value="<?= $user['email'] ?>" readonly>
        </div>

        <div class="mb-3">
            <label>Contact</label>
            <input type="text" name="contact" class="form-control" value="<?= $user['contact'] ?>">
        </div>

        <div class="mb-3">
            <label>Address</label>
            <textarea name="address" class="form-control"><?= $user['address'] ?></textarea>
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

        <button class="btn btn-primary w-100">Update Profile</button>
    </form>
</div>
