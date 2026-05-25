<?php
include 'db.php';

$query = mysqli_real_escape_string($conn, $_GET['query']);

$sql = "SELECT * FROM venues 
        WHERE name LIKE '%$query%' 
        OR location LIKE '%$query%'";

$result = mysqli_query($conn, $sql);
?>

<h2>Search Results for "<?php echo $query; ?>"</h2>

<?php
if (mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        echo "<div>";
        echo "<h3>" . $row['name'] . "</h3>";
        echo "<p>Location: " . $row['location'] . "</p>";
        echo "</div><hr>";
    }
} else {
    echo "<p>No matching venues found.</p>";
}
?>