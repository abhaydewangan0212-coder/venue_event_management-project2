<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

require 'db.php';


$tab = $_GET['tab'] ?? 'venues';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Venue Manager</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="admin_dashboard.php">Admin Panel</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item text-white nav-link">
            Logged in as: <?php echo htmlspecialchars($_SESSION['admin_username']); ?>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="admin_logout.php">Logout</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container" style="max-width:1100px; max-height: 85vh; overflow-y: auto; padding-bottom: 20px">

    <h2>Dashboard</h2>
    <h3>Venue Reports</h3>
<div class="report-boxes">
<?php
    $venueQuery = mysqli_query($conn, "SELECT id, name FROM venues");
    while($v = mysqli_fetch_assoc($venueQuery)){
        echo '
        <div class="report-card">
            <a href="venue_report.php?id='.$v['id'].'">
                '.$v['name'].'
            </a>
        </div>';
    }
?>
</div>

<style>
.report-boxes {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-top: 15px;
}
.report-card {
    background: #f2f5ff;
    padding: 15px;
    border-radius: 10px;
    width: 180px;
    text-align: center;
    font-weight: bold;
    box-shadow: 0 3px 5px rgba(0,0,0,0.1);
    transition: 0.2s;
}
.report-card:hover {
    transform: scale(1.05);
    background: #dce3ff;
}
.report-card a {
    text-decoration: none;
    color: #333;
}
</style>

    
    <ul class="nav nav-tabs mb-3">
      <li class="nav-item">
        <a class="nav-link <?php echo $tab === 'venues' ? 'active' : ''; ?>"
           href="admin_dashboard.php?tab=venues">Venues</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?php echo $tab === 'events' ? 'active' : ''; ?>"
           href="admin_dashboard.php?tab=events">Events / Packages</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?php echo $tab === 'food' ? 'active' : ''; ?>"
           href="admin_dashboard.php?tab=food">Food Menu</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?php echo $tab === 'bookings' ? 'active' : ''; ?>"
           href="admin_dashboard.php?tab=bookings">Bookings</a>
      </li>
      <li class="nav-item">
    <a class="nav-link <?php echo $tab === 'users' ? 'active' : ''; ?>"
       href="admin_dashboard.php?tab=users">
        Users
    </a>
</li>
      <li class="nav-item">
        <a class="nav-link <?php echo $tab === 'reports' ? 'active' : ''; ?>"
           href="admin_dashboard.php?tab=reports">Reports</a>
      </li>
    </ul>

    <?php
   /* ---------------- VENUES TAB ---------------- */
if ($tab === 'venues'):

    $venue_edit = null;

    // DELETE venue
    if (isset($_GET['delete_venue'])) {
        $vid = (int) $_GET['delete_venue'];
        if ($conn->query("DELETE FROM venues WHERE id=$vid")) {
            echo "<div class='alert alert-success'>Venue deleted successfully.</div>";
        } else {
            echo "<div class='alert alert-danger'>Error deleting venue: " . $conn->error . "</div>";
        }
    }

    // EDIT fetch venue
    if (isset($_GET['edit_venue'])) {
        $vid = (int) $_GET['edit_venue'];
        $res_v = $conn->query("SELECT * FROM venues WHERE id=$vid");
        if ($res_v && $res_v->num_rows == 1) {
            $venue_edit = $res_v->fetch_assoc();
        }
    }

    // ADD / UPDATE venue
    if (isset($_POST['save_venue'])) {
        $id       = !empty($_POST['id']) ? (int) $_POST['id'] : 0;
        $name     = $conn->real_escape_string($_POST['name']);
        $location = $conn->real_escape_string($_POST['location']);
        $capacity = (int) $_POST['capacity'];
        $price    = (int) $_POST['price'];
        $desc     = $conn->real_escape_string($_POST['description']);
        $contact  = $conn->real_escape_string($_POST['contact']);

        if ($id > 0) {
            $sql = "UPDATE venues 
                    SET name='$name', location='$location', capacity=$capacity, price=$price, description='$desc', contact='$contact'
                    WHERE id=$id";
            $msg = "Venue updated successfully.";
        } else {
            $sql = "INSERT INTO venues(name, location, capacity, price, description,contact)
                    VALUES('$name', '$location', $capacity, $price, '$desc', '$contact')";
            $msg = "Venue added successfully.";
        }

        if ($conn->query($sql)) {
            echo "<div class='alert alert-success'>$msg</div>";
            $venue_edit = null;
        } else {
            echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
        }
    }
    ?>

    <h4><?php echo $venue_edit ? 'Edit Venue' : 'Add Venue Branch '; ?></h4>
    <p class="text-muted mb-3">
        Owned by <strong>Red Door Venue Group</strong>
</p>
    <form method="post" action="admin_dashboard.php?tab=venues" class="row g-3 mb-4">
        <input type="hidden" name="id" value="<?php echo $venue_edit['id'] ?? ''; ?>">
        <div class="col-md-4">
            <input type="text" name="name" class="form-control"
                   placeholder="Venue Name"
                   value="<?php echo htmlspecialchars($venue_edit['name'] ?? ''); ?>" required>
        </div>
        <div class="col-md-3">
            <input type="text" name="location" class="form-control"
                   placeholder="Location"
                   value="<?php echo htmlspecialchars($venue_edit['location'] ?? ''); ?>" required>
        </div>
        <div class="col-md-3">
            <input type="text" name="contact" class="form-control"
                   placeholder="Contact Info"
                   maxllenght="10" minlenght="10"
                   pattern="[0-9]{10}"
                   value="<?php echo htmlspecialchars($venue_edit['contact'] ?? ''); ?>" required>
        </div>
        <div class="col-md-2">
            <input type="number" name="capacity" class="form-control"
                   placeholder="Capacity"
                   value="<?php echo htmlspecialchars($venue_edit['capacity'] ?? ''); ?>" required>
        </div>
        <div class="col-md-2">
    <input type="number" name="price" class="form-control"
           placeholder="Price-per guest"
           value="<?php echo htmlspecialchars($venue_edit['price'] ?? ''); ?>" required>
</div>
        <div class="col-md-3">
            <textarea name="description" class="form-control"
                   placeholder="Short Description"
                   rows="4"
                   style="resize: horizontal"><?php echo htmlspecialchars($venue_edit['description'] ?? ''); ?>
</textarea>
        </div>
        <div class="col-12">
            <button type="submit" name="save_venue" class="btn btn-primary">
                <?php echo $venue_edit ? 'Update Venue' : 'Add Venue'; ?>
            </button>
            <?php if ($venue_edit): ?>
                <a href="admin_dashboard.php?tab=venues" class="btn btn-secondary ms-2">Cancel</a>
            <?php endif; ?>
        </div>
    </form>

    <h5>All Venues</h5>
    <?php
    $venue_res = $conn->query("SELECT * FROM venues ORDER BY id ASC");
    if ($venue_res->num_rows > 0): ?>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Location</th>
                <th>Capacity</th>
                <th>Price per guest</th>
                <th>Contact-Info</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($v = $venue_res->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $v['id']; ?></td>
                    <td><?php echo htmlspecialchars($v['name']); ?></td>
                    <td><?php echo htmlspecialchars($v['location']); ?></td>
                    <td><?php echo htmlspecialchars($v['capacity']); ?></td>
                    <td><?php echo htmlspecialchars($v['price']); ?></td>
                    <td><?php echo htmlspecialchars($v['contact']); ?></td>
                
                    <td><?php echo htmlspecialchars($v['description']); ?></td>
                    <td>
                        <a href="admin_dashboard.php?tab=venues&edit_venue=<?php echo $v['id']; ?>"
                           class="btn btn-sm btn-warning">Edit</a>
                        <a href="admin_dashboard.php?tab=venues&delete_venue=<?php echo $v['id']; ?>"
                           onclick="return confirm('Delete this venue?');"
                           class="btn btn-sm btn-danger">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">No venues added yet.</div>
    <?php endif; ?>

<?php

/* ---------------- EVENTS TAB ---------------- */
elseif ($tab === 'events'):

    $event_edit = null;

    // DELETE event
    if (isset($_GET['delete_event'])) {
        $eid = (int) $_GET['delete_event'];
        if ($conn->query("DELETE FROM events WHERE id=$eid")) {
            echo "<div class='alert alert-success'>Event / Package deleted successfully.</div>";
        } else {
            echo "<div class='alert alert-danger'>Error deleting event: " . $conn->error . "</div>";
        }
    }

    // EDIT fetch event
    if (isset($_GET['edit_event'])) {
        $eid = (int) $_GET['edit_event'];
        $res_e = $conn->query("SELECT * FROM events WHERE id=$eid");
        if ($res_e && $res_e->num_rows == 1) {
            $event_edit = $res_e->fetch_assoc();
        }
    }

    // ADD / UPDATE event + MULTI IMAGE UPLOAD
    if (isset($_POST['save_event'])) {
        $id         = !empty($_POST['id']) ? (int) $_POST['id'] : 0;
        $title      = $conn->real_escape_string($_POST['title']);
        $venue_id   = (int) $_POST['venue_id'];
        $package_type = $_POST['package_type'] ?? '';
$package_type = $conn->real_escape_string($package_type);
        $event_date = $conn->real_escape_string($_POST['event_date']);
        $price      = (float) $_POST['price'];
        $desc       = $conn->real_escape_string($_POST['description']);
        $image      = $conn->real_escape_string($_POST['image']); // optional cover image

        if ($id > 0) {
            $sql = "UPDATE events
                    SET title='$title',
                        venue_id=$venue_id,
                        package_type = '$package_type',
                        event_date='$event_date',
                        price=$price,
                        description='$desc',
                        image='$image'
                    WHERE id=$id";
            $msg = "Event / Package updated successfully.";
        } else {
            $sql = "INSERT INTO events
        (title, venue_id, package_type, event_date, price, description, image)
        VALUES
        ('$title', $venue_id, '$package_type', '$event_date', $price, '$desc', '$image')";
            $msg = "Event / Package added successfully.";
        }

        if ($conn->query($sql)) {

            
            $event_id = ($id > 0) ? $id : $conn->insert_id;

            // multiple files upload
            if (!empty($_FILES['images']['name'][0])) {
                for ($i = 0; $i < count($_FILES['images']['name']); $i++) {
                    if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                        $original = basename($_FILES['images']['name'][$i]);
                        $filename = time() . '_' . $i . '_' . $original;
                        $target   = __DIR__ . "/images/" . $filename;

                        if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $target)) {
                            $filename_esc = $conn->real_escape_string($filename);
                            $conn->query("INSERT INTO event_images(event_id, image) VALUES($event_id, '$filename_esc')");
                        }
                    }
                }
            }

            echo "<div class='alert alert-success'>$msg</div>";
            $event_edit = null;
        } else {
            echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
        }
    }

    // list of venues for dropdown
    $venue_list = $conn->query("SELECT * FROM venues");
    ?>

    <h4><?php echo $event_edit ? 'Edit Event / Package' : 'Add Event / Package'; ?></h4>
    <form method="post" action="admin_dashboard.php?tab=events"
          class="row g-3 mb-4" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $event_edit['id'] ?? ''; ?>">
        <div class="col-md-4">
            <input type="text" name="title" class="form-control"
                   placeholder="Package Title"
                   value="<?php echo htmlspecialchars($event_edit['title'] ?? ''); ?>" required>
        </div>
        <div class="col-md-3">
            <select name="venue_id" class="form-select" required>
                <option value="">Select Venue</option>
                <?php while ($vl = $venue_list->fetch_assoc()): ?>
                    <option value="<?php echo $vl['id']; ?>"
                        <?php
                        if (!empty($event_edit) && $event_edit['venue_id'] == $vl['id']) {
                            echo 'selected';
                        }
                        ?>>
                        <?php echo htmlspecialchars($vl['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <div class="col-md-2">
            <input type="date" name="event_date" class="form-control"
                   value="<?php echo htmlspecialchars($event_edit['event_date'] ?? ''); ?>" required>
        </div>
        <div class="col-md-3">
            <input type="number" step="0.01" name="price" class="form-control"
                   placeholder="Price per Guest (₹)"
                   value="<?php echo htmlspecialchars($event_edit['price'] ?? ''); ?>" required>
        </div>
        <div class="col-12">
            <input type="text" name="description" class="form-control"
                   placeholder="Short Description"
                   value="<?php echo htmlspecialchars($event_edit['description'] ?? ''); ?>">
        </div>
        <div class="col-12">
            <input type="text" name="image" class="form-control"
                   placeholder="Cover image file (e.g. hall1.jpg)"
                   value="<?php echo htmlspecialchars($event_edit['image'] ?? ''); ?>">
            <small class="text-muted">Optional: write file name from /images folder for main cover image.</small>
        </div>
        <div class="col-12">
            <label class="form-label mt-2">Upload extra images (gallery)</label>
            <input type="file" name="images[]" class="form-control" multiple>
            <small class="text-muted">You can upload multiple jpg/png images. They will be shown as slider.</small>
        </div>
        <div class="col-12">
            <button type="submit" name="save_event" class="btn btn-primary">
                <?php echo $event_edit ? 'Update Event / Package' : 'Add Event / Package'; ?>
            </button>
            <?php if ($event_edit): ?>
                <a href="admin_dashboard.php?tab=events" class="btn btn-secondary ms-2">Cancel</a>
            <?php endif; ?>
        </div>
    </form>

    <h5>All Events / Packages</h5>
    <?php
    $event_res = $conn->query("
        SELECT e.*, v.name AS venue_name,
               (SELECT COUNT(*) FROM event_images ei WHERE ei.event_id = e.id) AS img_count
        FROM events e
        JOIN venues v ON e.venue_id = v.id
        WHERE e.event_type = 'package'
        ORDER BY e.id ASC
    ");
    if ($event_res->num_rows > 0): ?>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Venue</th>
                <th>Creation-Date</th>
                <th>Price/Guest</th>
                <th>Cover Image</th>
                <th>#Gallery Images</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($e = $event_res->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $e['id']; ?></td>
                    <td><?php echo htmlspecialchars($e['title']); ?></td>
                    <td><?php echo htmlspecialchars($e['venue_name']); ?></td>
                    <td><?php echo htmlspecialchars($e['event_date']); ?></td>
                    <td>₹<?php echo htmlspecialchars($e['price']); ?></td>
                    <td><?php echo htmlspecialchars($e['image']); ?></td>
                    <td><?php echo (int)$e['img_count']; ?></td>
                    <td><?php echo htmlspecialchars($e['description']); ?></td>
                    <td>
                        <a href="admin_dashboard.php?tab=events&edit_event=<?php echo $e['id']; ?>"
                           class="btn btn-sm btn-warning">Edit</a>
                        <a href="admin_dashboard.php?tab=events&delete_event=<?php echo $e['id']; ?>"
                           onclick="return confirm('Delete this event/package?');"
                           class="btn btn-sm btn-danger">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">No events added yet.</div>
    <?php endif; ?>

<?php


    /* ---------------- FOOD MENU TAB ---------------- */
    elseif ($tab === 'food'):

        $food_edit = null;

        if (isset($_GET['delete_food'])) {
            $fid = (int) $_GET['delete_food'];
            if ($conn->query("DELETE FROM food_menu WHERE id=$fid")) {
                echo "<div class='alert alert-success'>Food item deleted.</div>";
            } else {
                echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
            }
        }

        if (isset($_GET['edit_food'])) {
            $fid = (int) $_GET['edit_food'];
            $res_f = $conn->query("SELECT * FROM food_menu WHERE id=$fid");
            if ($res_f && $res_f->num_rows == 1) {
                $food_edit = $res_f->fetch_assoc();
            }
        }

        if (isset($_POST['save_food'])) {
            $name = $conn->real_escape_string($_POST['name']);
            $price = (float) $_POST['price'];
            $desc = $conn->real_escape_string($_POST['description']);
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            if (!empty($_POST['id'])) {
                $id = (int) $_POST['id'];
                $sql = "UPDATE food_menu
                        SET name='$name', price_per_person=$price, description='$desc', is_active=$is_active
                        WHERE id=$id";
                $msg = "Food item updated.";
            } else {
                $sql = "INSERT INTO food_menu(name, price_per_person, description, is_active)
                        VALUES('$name', $price, '$desc', $is_active)";
                $msg = "Food item added.";
            }

            if ($conn->query($sql)) {
                echo "<div class='alert alert-success'>$msg</div>";
                $food_edit = null;
            } else {
                echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
            }
        }
        ?>

        <h4>Food Menu Management</h4>
        <form method="post" action="admin_dashboard.php?tab=food" class="row g-3 mb-4">
            <input type="hidden" name="id" value="<?php echo $food_edit['id'] ?? ''; ?>">
            <div class="col-md-4">
                <input type="text" name="name" class="form-control"
                       placeholder="Food Name (e.g. Veg Thali)"
                       value="<?php echo htmlspecialchars($food_edit['name'] ?? ''); ?>" required>
            </div>
            <div class="col-md-3">
                <input type="number" step="0.01" name="price" class="form-control"
                       placeholder="Price per Person"
                       value="<?php echo htmlspecialchars($food_edit['price_per_person'] ?? ''); ?>" required>
            </div>
            <div class="col-md-4">
                <input type="text" name="description" class="form-control"
                       placeholder="Short Description"
                       value="<?php echo htmlspecialchars($food_edit['description'] ?? ''); ?>">
            </div>
            <div class="col-md-1 d-flex align-items-center">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                           <?php echo (!isset($food_edit) || !empty($food_edit['is_active'])) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>
            </div>
            <div class="col-12">
                <button type="submit" name="save_food" class="btn btn-primary">
                    <?php echo isset($food_edit) ? 'Update Food Item' : 'Add Food Item'; ?>
                </button>
            </div>
        </form>

        <h5>All Food Items</h5>
        <?php
        $food_res = $conn->query("SELECT * FROM food_menu ORDER BY id ASC");
        if ($food_res && $food_res->num_rows > 0): ?>
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Price/Person (₹)</th>
                    <th>Description</th>
                    <th>Active</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php while ($f = $food_res->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $f['id']; ?></td>
                        <td><?php echo htmlspecialchars($f['name']); ?></td>
                        <td><?php echo number_format($f['price_per_person'], 2); ?></td>
                        <td><?php echo htmlspecialchars($f['description']); ?></td>
                        <td><?php echo $f['is_active'] ? 'Yes' : 'No'; ?></td>
                        <td>
                            <a href="admin_dashboard.php?tab=food&edit_food=<?php echo $f['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="admin_dashboard.php?tab=food&delete_food=<?php echo $f['id']; ?>"
                               onclick="return confirm('Delete this food item?');"
                               class="btn btn-sm btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info">No food items yet.</div>
        <?php endif; ?>

    <?php
    /* ---------------- BOOKINGS TAB ---------------- */
    elseif ($tab === 'bookings'): ?>

        <h4>All Bookings</h4>
        <?php
        $book_res = $conn->query("SELECT bookings.*, events.title AS event_title
                                  FROM bookings
                                  JOIN events ON bookings.event_id = events.id
                                  ORDER BY bookings.id ASC");
        if ($book_res->num_rows > 0): ?>
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Event / Package</th>
                    <th>Customer</th>
                    <th>Email</th>
                    <th>Occasion Date</th>
                    <th>Occasion</th>
                    <th>Theme</th>
                    <th>Food Menu</th>
                    <th>Room</th>
                    <th>Guests</th>
                    <th>Total (₹)</th>
                    <th>Payment</th>
                    <th>Txn ID</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                <?php while ($b = $book_res->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $b['id']; ?></td>
                        <td><?php echo htmlspecialchars($b['event_title']); ?></td>
                        <td><?php echo htmlspecialchars($b['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($b['customer_email']); ?></td>
                        <td><?php echo htmlspecialchars($b['event_date']); ?></td>
                        <td><?php echo htmlspecialchars($b['occasion']); ?></td>
                        <td><?php echo htmlspecialchars($b['theme']); ?></td>
                        <td><?php echo htmlspecialchars($b['food_menu']); ?></td>
                        <td><?php echo htmlspecialchars($b['room_type']); ?></td>
                        <td><?php echo htmlspecialchars($b['guests']); ?></td>
                        <td>₹<?php echo number_format($b['total_amount'], 2); ?></td>
                        <td><?php echo htmlspecialchars($b['payment_method']); ?></td>
                        <td><?php echo htmlspecialchars($b['transaction_id']); ?></td>
                        <td><?php echo htmlspecialchars($b['status']); ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info">No bookings yet.</div>
        <?php endif; ?>
        

    <?php

    /* -------- USERS TAB -------- */
elseif ($tab == 'users') :
    include 'users.php';

    /* ---------------- REPORTS TAB ---------------- */
    elseif ($tab === 'reports'): ?>

        <h4>Monthly Booking Report</h4>
        <p class="text-muted">Summary of bookings, guests, and revenue per month (Paid bookings only).</p>

        <?php
        $report_sql = "
    SELECT 
        DATE_FORMAT(booking_date, '%Y-%m') AS month,
        COUNT(*) AS total_bookings,
        COALESCE(SUM(guests), 0) AS total_guests,
        COALESCE(SUM(total_amount), 0) AS total_revenue
    FROM bookings
    WHERE status = 'Paid'
    GROUP BY DATE_FORMAT(booking_date, '%Y-%m')
    ORDER BY DATE_FORMAT(booking_date, '%Y-%m')
";
        $report_res = $conn->query($report_sql);

        $labels = [];
        $bookData = [];
        $guestData = [];
        $revenueData = [];
        ?>

        <?php if ($report_res && $report_res->num_rows > 0): ?>
            <div class="table-responsive mt-3">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                    <tr>
                        <th>Month (YYYY-MM)</th>
                        <th>Total Bookings</th>
                        <th>Total Guests</th>
                        <th>Revenue (₹)</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php while ($r = $report_res->fetch_assoc()): ?>
                        <?php
                            $labels[] = $r['month'];
                            $bookData[] = (int)$r['total_bookings'];
                            $guestData[] = (int)$r['total_guests'];
                            $revenueData[] = (float)$r['total_revenue'];
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($r['month']); ?></td>
                            <td><?php echo htmlspecialchars($r['total_bookings']); ?></td>
                            <td><?php echo htmlspecialchars($r['total_guests']); ?></td>
                            <td>₹<?php echo number_format($r['total_revenue'], 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <h5 class="mt-4">Graph View</h5>
            <canvas id="bookingChart" height="120"></canvas>

            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                const ctx = document.getElementById('bookingChart').getContext('2d');
                const bookingChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: <?php echo json_encode($labels); ?>,
                        datasets: [
                            {
                                label: 'Total Bookings',
                                data: <?php echo json_encode($bookData); ?>,
                            },
                            {
                                label: 'Total Guests',
                                data: <?php echo json_encode($guestData); ?>,
                            },
                            {
                                label: 'Revenue (₹)',
                                data: <?php echo json_encode($revenueData); ?>,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            </script>

        <?php else: ?>
            <div class="alert alert-info mt-3">No booking data available to generate report.</div>
        <?php endif; ?>

    <?php endif; ?>

</div><!-- /container -->

</body>
</html>