<?php
include 'db.php';
include 'admin_header.php';
?>

<h2>Venue Reports</h2>

<div class="report-boxes">
<?php
$venueQuery = mysqli_query($conn,"SELECT id, name FROM venues");
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

<?php include 'admin_footer.php'; ?>