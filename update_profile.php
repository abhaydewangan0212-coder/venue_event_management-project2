<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$name    = $_POST['name'];
$address = $_POST['address'];
$dob     = $_POST['dob'];
$gender  = $_POST['gender'];

/* PROFILE PHOTO */
$photo_name = null;

if (!empty($_FILES['profile_photo']['name'])) {
    $photo_name = time() . '_' . $_FILES['profile_photo']['name'];
    move_uploaded_file(
        $_FILES['profile_photo']['tmp_name'],
        "images/" . $photo_name
    );
}

/* UPDATE QUERY */
if ($photo_name) {
    $sql = "UPDATE users SET 
            name='$name',
            address='$address',
            dob='$dob',
            gender='$gender',
            profile_photo='$photo_name'
            WHERE id=$user_id";
} else {
    $sql = "UPDATE users SET 
            name='$name',
            address='$address',
            dob='$dob',
            gender='$gender'
            WHERE id=$user_id";
}

$conn->query($sql);

/* UPDATE SESSION PHOTO */
if ($photo_name) {
    $_SESSION['profile_photo'] = $photo_name;
}

header("Location: index.php");
exit;