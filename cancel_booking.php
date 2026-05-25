<?php
include 'db.php';

$id = $_GET['id'];

$q = mysqli_query($conn, "SELECT status FROM bookings WHERE id='$id'");
$data = mysqli_fetch_assoc($q);

if ($data['status'] === 'Unpaid') {

    mysqli_query($conn, "
        UPDATE bookings 
        SET status='Cancelled' 
        WHERE id='$id'
    ");

    echo "<script>
            alert('Booking cancelled successfully');
            window.location='user_bookings.php';
          </script>";

} else {

    echo "<script>
            alert('Paid bookings cannot be cancelled online. Please contact the venue.');
            window.location='user_bookings.php';
          </script>";
}
?>