<?php
include 'db.php';

$venue_id = $_GET['id'];
// Fetch venue details
$venueQuery = mysqli_query($conn, "SELECT * FROM venues WHERE id = $venue_id");
$venue = mysqli_fetch_assoc($venueQuery);

// assume $conn is your mysqli connection and $venue_id came from GET earlier
$venue_id = intval($_GET['id']); // sanitize

// Total bookings for this venue (join bookings -> events)
$totalBookingsRow = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS total
     FROM bookings b
     JOIN events e ON b.event_id = e.id
     WHERE e.venue_id = {$venue_id}"
));
$totalBookings = $totalBookingsRow['total'] ?? 0;

// Total revenue for this venue
$totalRevenueRow = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COALESCE(SUM(b.total_amount),0) AS revenue
     FROM bookings b
     JOIN events e ON b.event_id = e.id
     WHERE e.venue_id = {$venue_id}"
));
$totalRevenue = $totalRevenueRow['revenue'] ?? 0;

// Total guests for this venue
$totalGuestsRow = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COALESCE(SUM(b.guests),0) AS guests
     FROM bookings b
     JOIN events e ON b.event_id = e.id
     WHERE e.venue_id = {$venue_id}"
));
$totalGuests = $totalGuestsRow['guests'] ?? 0;

// Fetch events (packages) for this venue and count how many bookings each event has
$packageQuery = mysqli_query($conn,
    "SELECT 
        e.title,
        COUNT(b.id) AS booking_count,
        COALESCE(SUM(b.total_amount),0) AS revenue
     FROM events e
     LEFT JOIN bookings b ON b.event_id = e.id
     WHERE e.venue_id = {$venue_id}
     GROUP BY e.id, e.title"
);

// check for SQL error (useful while debugging)
if(!$packageQuery){
    die("SQL error: " . mysqli_error($conn));
}
// ===============================
// Step 1: Find Most Selected Package
// ===============================
$mostPopularPackage = "N/A";

$popularPackageQuery = mysqli_query($conn, "
    SELECT e.title, COUNT(b.id) AS total_bookings
    FROM bookings b
    JOIN events e ON b.event_id = e.id
    WHERE e.venue_id = {$venue_id}
    GROUP BY e.id
    ORDER BY total_bookings DESC
    LIMIT 1
");

if ($popularPackageQuery && mysqli_num_rows($popularPackageQuery) > 0) {
    $popularRow = mysqli_fetch_assoc($popularPackageQuery);
    $mostPopularPackage = $popularRow['title'];
}


// Build chart data
$packageLabels = "";
$bookingCounts = "";
$packageRevenue = "";


while($row = mysqli_fetch_assoc($packageQuery)){
    $packageLabels   .= "'".$row['title']."',";
    $bookingCounts   .= $row['booking_count'].",";
    $packageRevenue  .= $row['revenue'].",";
}

// ===============================
// Step 3: Fetch booking details table for this venue
// ===============================
$bookingTableQuery = mysqli_query($conn, "
    SELECT 
        b.booking_date,
        b.event_date,
        e.title AS package_name,
        b.customer_name AS user_name,
        b.customer_email AS user_email,
        b.guests,
        b.total_amount
    FROM bookings b
    JOIN events e ON b.event_id = e.id
    
    WHERE e.venue_id = {$venue_id}
    ORDER BY b.booking_date ASC
");

if (!$bookingTableQuery) {
    die('Booking table SQL error: ' . mysqli_error($conn));
}
?>
<h2>Report for: <?php echo $venue['name']; ?></h2>

<div class="stats">
    <div class="stat-card">Total Bookings: <?php echo $totalBookings; ?></div>
    <div class="stat-card">Total Revenue: ₹<?php echo $totalRevenue; ?></div>
    <div class="stat-card">Total Guests: <?php echo $totalGuests; ?></div>
    <div class="stat-card">Most Selected Package: <b><?php echo $mostPopularPackage; ?></b></div>
</div>
<h3 style="margin-top:30px;"><u>Booking Details</u></h3>

<table class="booking-table">
    <thead>
        <tr>
            <th>Sr No.</th>
            <th>Package Name</th>
            <th>Booked By</th>
            <th>Customer Email</th>
            <th>Booking Date</th>
            <th>Occasion Date</th>
            
            <th>Total Guests</th>
            <th>Revenue (₹)</th>
        </tr>
    </thead>
    <tbody>
        <?php if (mysqli_num_rows($bookingTableQuery) > 0): ?>
            <?php
            $count = 1;
            while ($row = mysqli_fetch_assoc($bookingTableQuery)): ?>
                <tr>
                    <td><?= $count++; ?></td>
                    <td><?php echo htmlspecialchars($row['package_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['user_email']); ?></td>
                    <td><?php echo date('d-m-Y', strtotime($row['booking_date'])); ?></td>
                    <td><?php echo date('d-m-Y', strtotime($row['event_date'])); ?></td>
                    
                    <td><?php echo $row['guests']; ?></td>
                    <td>₹<?php echo number_format($row['total_amount'], 2); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" class="text-center">No bookings found</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<style>
.stats {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
}
.stat-card {
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    width: 230px;
    font-size: 16px;
}
#chart-container {
    width: 1000%;
    max-width: 600px;
    height: 300px; 
}
.booking-table {
    width: 70%;
    border-collapse: collapse;
    margin-top: 15px;
    background: #fff;
}

.booking-table th,
.booking-table td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: center;
}

.booking-table th {
    background-color: #f5f5f5;
    font-weight: bold;
}

.booking-table tr:nth-child(even) {
    background-color: #fafafa;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<h3>Package Selection Chart</h3>

<div id="chart-container">
    <canvas id="packageChart"></canvas>
</div>

<script>
var ctx = document.getElementById('packageChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: [<?= $packageLabels ?>],
        datasets: [
            {
                label: 'Bookings',
                data: [<?= $bookingCounts ?>],
                backgroundColor: 'rgba(235, 105, 54, 0.6)'
            },
            {
                label: 'Revenue (₹)',
                data: [<?= $packageRevenue ?>],
                backgroundColor: 'rgba(192, 75, 139, 0.6)'
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