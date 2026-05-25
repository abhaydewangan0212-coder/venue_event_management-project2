<?php
// start_venue_booking.php
require 'db.php';

if (!isset($_GET['venue_id'])) {
    die("Invalid venue.");
}

$venue_id = (int) $_GET['venue_id'];

// Check venue exists
$vres = $conn->query("SELECT * FROM venues WHERE id = $venue_id");
if (!$vres || $vres->num_rows == 0) {
    die("Venue not found.");
}


$venue = $vres->fetch_assoc();

// Create a temporary / custom event for this venue
$title       = "Custom Booking - " . $conn->real_escape_string($venue['name']);
$event_date  = date('Y-m-d');             
$price       = $venue['price'];                         
$description = "Custom booking created directly from venue card.";
$image       = "";                   



$sql = "INSERT INTO events (title, venue_id, event_date, price, description, image)
        VALUES ('$title', $venue_id, '$event_date', $price, '$description', '$image')";

if ($conn->query($sql)) {
    $event_id = $conn->insert_id;
    header("Location: book.php?event_id=" . $event_id);
    exit;
} else {
    die("Error creating event: " . $conn->error);
}