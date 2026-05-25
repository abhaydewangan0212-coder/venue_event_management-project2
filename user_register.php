<?php
require 'db.php';
include 'header.php';

$message = "";

if (isset($_POST['register'])) {

    $name    = $conn->real_escape_string($_POST['name']);
    $email   = $conn->real_escape_string($_POST['email']);

    $contact = trim($_POST['contact']);
    $pass    = $_POST['password'];
    // ===== STEP 2: Extra registration fields =====
$address = $conn->real_escape_string($_POST['address']);
$dob     = $_POST['dob'];
$gender  = $_POST['gender'];

// ===== Profile photo (optional) =====
$photoName = NULL;

if (!empty($_FILES['profile_photo']['name'])) {
    $photoName = time() . '_' . $_FILES['profile_photo']['name'];
    $tmpName   = $_FILES['profile_photo']['tmp_name'];
    $uploadDir = "images/";

    move_uploaded_file($tmpName, $uploadDir . $photoName);
}

    // ✅ Step 2: Contact validation (exactly 10 digits)
    if (!preg_match('/^[0-9]{10}$/', $contact)) {

        $message = "<div class='alert alert-danger'>
                        Contact number must be exactly 10 digits.
                    </div>";

    } else {

        // ✅ Check if email already exists
        $check = $conn->query("SELECT id FROM users WHERE email='$email'");

        if ($check->num_rows > 0) {

            $message = "<div class='alert alert-danger'>
                            This email is already registered.
                        </div>";

        } else {

            // ✅ Secure password hashing
            $password_hash = password_hash($pass, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (name, email, contact, password, profile_photo, address, dob, gender)
                    VALUES ('$name', '$email', '$contact', '$password_hash', '$photoName', '$address', '$dob', '$gender')";

            if ($conn->query($sql)) {
                $message = "<div class='alert alert-success'>
                                Registration successful! You can now login.
                            </div>";
            } else {
                $message = "<div class='alert alert-danger'>
                                Error: {$conn->error}
                            </div>";
            }
        }
    }
}
?>

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
    background: rgba(150, 150, 150, 0.15);
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
.profile-container {
    display: flex;
    justify-content: center;
    margin-bottom: 25px;
}

.profile-container label {
    position: relative;
    cursor: pointer;
}

#profilePreview {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #fff;
    box-shadow: 0 0 8px rgba(0,0,0,0.3);
    background: #ccc;
}
#initialAvatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: #bbb;
    color: white;
    font-size: 36px;
    font-weight: bold;
    text-align: center;
    line-height: 100px;
}

/* + icon */
.edit-icon {
    position: absolute;
    bottom: 5px;
    right: 5px;
    background: #ff5e57;
    color: #fff;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    text-align: center;
    line-height: 28px;
    font-size: 18px;
}
</style>
</head>

<h2>User Registration</h2>
<?php echo $message; ?>

<form method="post" enctype="multipart/form-data" class="mt-3 col-md-6">
<div class="profile-container">
    <div id="initialAvatar">U</div>

    <label for="profile_photo">
        <img id="profilePreview" style="display:none;">
        <span class="edit-icon">+</span>
    </label>

    <input type="file" id="profile_photo" name="profile_photo"
           accept="image/*" onchange="previewProfile(this)" hidden>
</div>
    <div class="mb-3">
        <label class="form-label">Full Name</label>
        <input type="text" name="name" class="form-control" 
        placeholder="Enter your full-name" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" 
        placeholder="Enter your email" required>
    </div>
    <div class="mb-3">
    <label class="form-label">Contact Number</label>
    <input type="tel" 
           name="contact" 
           pattern="\d{10}"
           minlength="10" maxlength="10"
           class="form-control" 
           placeholder="Enter contact number, 10-digit phone(digits only)"
           required>
</div>


<!-- Address -->
<div class="mb-3">
  <label class="form-label">Address</label>
  <textarea name="address" class="form-control" placeholder="Enter your address"></textarea>
</div>

<!-- Date of Birth -->
<div class="mb-3">
  <label class="form-label">Date of Birth</label>
  <input type="date" name="dob" class="form-control">
</div>

<!-- Gender -->
<div class="mb-3">
  <label class="form-label">Gender</label>
  <select name="gender" class="form-control">
    <option value="">Select Gender</option>
    <option value="Male">Male</option>
    <option value="Female">Female</option>
    <option value="Other">Other</option>
  </select>
</div>
    <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" 
        placeholder="Please Enter a strong password" required>
    </div>
    <button type="submit" name="register" class="btn btn-success">Register</button>
    <a href="user_login.php" class="btn btn-link">Already have an account? Login</a>
</form>
<script>
function previewProfile(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById('profilePreview').src = e.target.result;
            document.getElementById('profilePreview').style.display = 'block';

            // hide initials avatar
            const initial = document.getElementById('initialAvatar');
            if (initial) initial.style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>


<?php include 'footer.php'; ?>
