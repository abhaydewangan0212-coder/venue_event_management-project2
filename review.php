<?php
require 'db.php';
include 'header.php';

if (!isset($_GET['event_id'])) {
    echo "<div class='alert alert-danger'>Invalid event.</div>";
    include 'footer.php';
    exit;
}

$event_id = (int) $_GET['event_id'];

// event detail
$evtRes = $conn->query("
    SELECT e.*, v.name AS venue_name, v.location
    FROM events e
    JOIN venues v ON e.venue_id = v.id
    WHERE e.id = $event_id
");

if (!$evtRes || $evtRes->num_rows == 0) {
    echo "<div class='alert alert-danger'>Event / package not found.</div>";
    include 'footer.php';
    exit;
}

$event = $evtRes->fetch_assoc();
$message = "";

// review submit
if (isset($_POST['submit_review'])) {
    if (!isset($_SESSION['user_id'])) {
        $message = "<div class='alert alert-warning'>Please login to submit a review.</div>";
    } else {
        $rating  = (int) $_POST['rating'];
        if ($rating < 1) $rating = 1;
        if ($rating > 5) $rating = 5;

        $comment = $conn->real_escape_string($_POST['comment']);
        $user_id = (int) $_SESSION['user_id'];

        $sql = "INSERT INTO reviews(event_id, user_id, rating, comment)
                VALUES ($event_id, $user_id, $rating, '$comment')";

        if ($conn->query($sql)) {
            $message = "<div class='alert alert-success'>Thanks for your review!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
        }
    }
}

// saare reviews list
$revList = $conn->query("
    SELECT r.*, u.name AS user_name
    FROM reviews r
    LEFT JOIN users u ON r.user_id = u.id
    WHERE r.event_id = $event_id
    ORDER BY r.created_at DESC
");

// average rating
$avgRes = $conn->query("
    SELECT AVG(rating) AS avg_rating, COUNT(*) AS total_reviews
    FROM reviews
    WHERE event_id = $event_id
");
$avg = null;
$count = 0;
if ($avgRes && $avgRes->num_rows > 0) {
    $ar    = $avgRes->fetch_assoc();
    $avg   = $ar['avg_rating'];
    $count = $ar['total_reviews'];
}
?>

<h2>Reviews for: <?php echo htmlspecialchars($event['title']); ?></h2>
<p class="text-muted">
    Venue: <?php echo htmlspecialchars($event['venue_name']); ?>
    (<?php echo htmlspecialchars($event['location']); ?>)
</p>

<?php if ($avg && $count > 0): ?>
    <p><strong>Average Rating:</strong>
        <?php echo number_format($avg, 1); ?>/5
        (<?php echo $count; ?> reviews)
    </p>
<?php else: ?>
    <p class="text-muted">No reviews yet. Be the first one!</p>
<?php endif; ?>

<?php echo $message; ?>

<?php if (isset($_SESSION['user_id'])): ?>
    <h4 class="mt-3">Write a Review</h4>
    <form method="post" class="col-md-6 mt-2">
        <div class="mb-2">
            <label class="form-label">Rating (1–5)</label>
            <select name="rating" class="form-select" required>
                <option value="5">5 - Excellent</option>
                <option value="4">4 - Very Good</option>
                <option value="3">3 - Good</option>
                <option value="2">2 - Average</option>
                <option value="1">1 - Poor</option>
            </select>
        </div>
        <div class="mb-2">
            <label class="form-label">Your Review</label>
            <textarea name="comment" class="form-control" rows="3"
                      placeholder="Share your experience..." required></textarea>
        </div>
        <button type="submit" name="submit_review" class="btn btn-primary mt-1">Submit Review</button>
    </form>
<?php else: ?>
    <div class="alert alert-info mt-3">
        Please <a href="user_login.php" class="alert-link">login</a> to write a review.
    </div>
<?php endif; ?>

<h4 class="mt-4">All Reviews</h4>
<?php if ($revList && $revList->num_rows > 0): ?>
    <div class="list-group mt-2 mb-4">
        <?php while ($r = $revList->fetch_assoc()): ?>
            <div class="list-group-item">
                <div class="d-flex justify-content-between">
                    <strong>
                        <?php echo htmlspecialchars($r['user_name'] ?? 'Guest'); ?>
                    </strong>
                    <span>
                        <?php echo (int)$r['rating']; ?>/5
                    </span>
                </div>
                <p class="mb-1"><?php echo nl2br(htmlspecialchars($r['comment'])); ?></p>
                <small class="text-muted">
                    <?php echo htmlspecialchars($r['created_at']); ?>
                </small>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <p class="text-muted">No reviews yet.</p>
<?php endif; ?>

<a href="index.php" class="btn btn-secondary mb-4">Back to Home</a>

<?php include 'footer.php'; ?>