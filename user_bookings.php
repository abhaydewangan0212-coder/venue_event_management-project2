<?php
require 'db.php';
include 'header.php';

if (!isset($_SESSION['user_id'])) {
    echo "<div class='alert alert-warning'>Please login to view your bookings.</div>";
    include 'footer.php';
    exit;
}

$user_email = $_SESSION['user_email'];

$sql = "SELECT 
            b.*, 
            e.title AS event_title,
            v.name AS venue_name,
            v.location AS venue_location
        FROM bookings b
        JOIN events e ON b.event_id = e.id
        LEFT JOIN venues v ON e.venue_id = v.id
        WHERE b.customer_email = '$user_email'
        ORDER BY b.id DESC";

$result = $conn->query($sql);
?>

<h2>My Bookings</h2>

<?php if ($result->num_rows > 0): ?>
<table class="table table-bordered table-striped mt-3">
   <thead class="table-dark">
<tr>
    <th>ID</th>
    <th>Event</th>
    <th>Venue</th>          
    <th>Date</th>
    <th>Occasion</th>
    <th>Theme</th>
    <th>Food Menu</th>
    <th>Room</th>
    <th>Guests</th>
    <th>Total (₹)</th>
    <th>Status</th>
    <th>Action</th>
</tr>
</thead>
    <tbody>
    <?php while ($b = $result->fetch_assoc()): ?>
        <tr>
    <td><?php echo $b['id']; ?></td>
    <td><?php echo htmlspecialchars($b['event_title']); ?></td>
    <td>
        <?php 
        // Venue name + location (agar mila to)
        if (!empty($b['venue_name'])) {
            echo htmlspecialchars($b['venue_name']) . " (" . htmlspecialchars($b['venue_location']) . ")";
        } else {
            echo "N/A";
        }
        ?>
    </td>
    <td><?php echo htmlspecialchars($b['event_date']); ?></td>
    <td><?php echo htmlspecialchars($b['occasion']); ?></td>
    <td><?php echo htmlspecialchars($b['theme']); ?></td>
    <td><?php echo htmlspecialchars($b['food_menu']); ?></td>
    <td><?php echo htmlspecialchars($b['room_type']); ?></td>
    <td><?php echo htmlspecialchars($b['guests']); ?></td>
    <td>₹<?php echo number_format($b['total_amount'], 2); ?></td>
    <td><?php echo htmlspecialchars($b['status']); ?></td>
<td>
<?php if ($b['status'] === 'Unpaid') { ?>

    <!-- Only unpaid bookings can be cancelled -->
    <a href="cancel_booking.php?id=<?php echo $b['id']; ?>"
       onclick="return confirm('Are you sure you want to cancel this booking?')"
       class="btn btn-danger btn-sm">
        Cancel
    </a>

<?php } elseif ($b['status'] === 'Paid') { ?>

    <!-- Paid booking -->
    <button class="btn btn-secondary btn-sm" disabled>
        Paid
    </button>

<?php } else { ?>

    <!-- Cancelled booking -->
    <button class="btn btn-dark btn-sm" disabled>
        Cancelled
    </button>

<?php } ?>
</td>
</tr>
    <?php endwhile; ?>
    </tbody>
</table>
<?php else: ?>
    <div class="alert alert-info mt-3">You have no bookings yet.</div>
<?php endif; ?>

<?php include 'footer.php'; ?>