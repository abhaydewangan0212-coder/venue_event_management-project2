<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<?php

$profileImg = "images/bg.jpg";

if (isset($_SESSION['profile_photo']) && $_SESSION['profile_photo'] != "") {
    $profileImg = "images/" . $_SESSION['profile_photo'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Venue & Event Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>


        :root {
            --primary-color: #ff6b35;
            --primary-dark: #e85a2a;
            --bg-gradient: linear-gradient(135deg, #f5f7fa 0%, #e9f0ff 100%);
            --card-radius: 18px;
        }

        body {
            background: var(--bg-gradient);
            min-height: 100vh;
        }

        .navbar {
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .navbar-brand {
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .navbar-brand img {
            border: 2px solid rgba(255,255,255,0.7);
        }

        .nav-link {
            position: relative;
            font-weight: 500;
            margin-left: 8px;
        }

        .nav-link::after {
            content: "";
            position: absolute;
            left: 8px;
            bottom: 4px;
            width: 0;
            height: 2px;
            background: var(--primary-color);
            transition: width 0.25s ease;
        }

        .nav-link:hover::after {
            width: 60%;
        }

        .nav-link:hover {
            color: #ffd699 !important;
        }

        .container {
            max-width: 1150px;
        }

        .hero-section {
            position: relative;
            color: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.35);
        }

        .hero-section img {
            height: 320px;
            object-fit: cover;
            filter: brightness(0.75);
            transform: scale(1.02);
            transition: transform 2s ease;
        }

        .hero-section:hover img {
            transform: scale(1.06);
        }

        .hero-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(
                to bottom right,
                rgba(0,0,0,0.5),
                rgba(0,0,0,0.75)
            );
        }

        .hero-content {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            flex-direction: column;
            padding: 1.5rem;
        }

        .hero-content h1 {
            font-size: 2rem;
            font-weight: 700;
            text-shadow: 0 4px 16px rgba(0,0,0,0.8);
        }

        .btn-primary,
        .btn-success,
        .btn-warning {
            border-radius: 999px;
            padding-inline: 18px;
            padding-block: 8px;
            font-weight: 600;
            box-shadow: 0 8px 18px rgba(0,0,0,0.12);
            transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
        }

        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 10px 22px rgba(0,0,0,0.18);
        }

        .card {
            border-radius: var(--card-radius);
            border: none;
            overflow: hidden;
            transition: transform 0.22s ease, box-shadow 0.22s ease;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.12);
        }

        .card-img-top {
            height: 180px;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .card:hover {
            transform: translateY(-6px);
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.22);
        }

        .card:hover .card-img-top {
            transform: scale(1.06);
        }
        /* Modal background */
.profile-modal {
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 9999;
}

/* Profile box */
.profile-box {
    background: #a13e3eff;
    width: 450px;
    max-width: 95%;
    margin: 40px auto;
    border-radius: 15px;
    padding: 15px;
    position: relative;
    max-height: 75vh;
    overflow-y: auto;
}

/* Close button */
.close-btn {
    position: absolute;
    top: 10px;
    right: 15px;
    font-size: 22px;
    cursor: pointer;
}

/* Edit Profile button (not link look) */
.edit-profile-text {
    text-decoration: none;
    color: #ffffff;
    font-weight: 500;
    cursor: pointer;
    padding: 6px 12px;
    border-radius: 6px;
    transition: background 0.3s;
}

.edit-profile-text:hover {
    background: rgba(255, 255, 255, 0.15);
}


        footer {
            box-shadow: 0 -4px 18px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="index.php">
        <img src="images/logo.png" alt="Logo" width="35" height="35" class="me-2 rounded-circle">
        VenueManager(FestivoVenues)
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsExample">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarsExample">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="index.php">Events</a></li>
        <li class="nav-item"><a class="nav-link" href="admin_login.php">Admin</a></li>

      <?php if (isset($_SESSION['user_id'])): ?>
   <span class="nav-link">
        Hello, <?php echo htmlspecialchars($_SESSION['user_name']); ?>
    </span>
</li>
<li class="nav-item">
  <a href="#" 
   class="nav-link"
   data-bs-toggle="modal"
   data-bs-target="#editProfileModal">
   Edit Profile
</a>
</li>

           
<li class="nav-item d-flex align-items-center">
    <img src="<?php echo $profileImg; ?>"
         alt="Profile"
         width="35"
         height="35"
         class="rounded-circle me-2">

  <li class="nav-item"><a class="nav-link" href="user_logout.php">Logout</a></li>
        <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="user_login.php">User Login</a></li>
            <li class="nav-item"><a class="nav-link" href="user_register.php">Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<body>
<style>
    /* Make edit profile modal shorter & compact */
#editProfileModal .modal-dialog {
    max-width: 380px;
    margin: 40px auto;
}

#editProfileModal .modal-content {
    max-height: none;
    overflow: visible;
    border-radius: 14px;
}

#editProfileModal .profile-photo
{
    width: 90px;
    height: 90px;
}
#editProfileModal .modal-body {
    padding: 12px 16px;
}
#editProfileModel label {
    margin-bottom: 2px;
    font-size: 14px;
}
#editProfile input {
    padding: 6px 8px;
}
</style>
</body>
<div class="container my-4">
