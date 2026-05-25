<?php
require 'db.php';
include 'header.php';
?>

<!-- HERO SLIDER -->
<div id="heroCarousel" class="carousel slide mb-4 hero-section" data-bs-ride="carousel">
  <div class="carousel-inner">
    <div class="carousel-item active">
      <img src="images/banner1.jpg" class="d-block w-100" alt="Banner 1">
      <div class="hero-overlay"></div>
      <div class="hero-content">
        <h1>💫Showcasing all our veneus from festivoEventVenues for you to book💫</h1>
        <p>Look through all the available packages and book the one your vibes match the best.</p>
        <a href="#events-list" class="btn btn-warning btn-lg mt-2">View Packages</a>
      </div>
    </div>
    <div class="carousel-item">
      <img src="images/banner2.jpg" class="d-block w-100" alt="Banner 2">
      <div class="hero-overlay"></div>
      <div class="hero-content">
        <h1>Perfect for Weddings & Parties</h1>
        <p>Available custom themes, occasions and food menus just for you.</p>
      </div>
    </div>
    <div class="carousel-item">
      <img src="images/banner5.jpeg" class="d-block w-100" alt="Banner 5">
      <div class="hero-overlay"></div>
      <div class="hero-content">
        <h1>Easy Booking For any customer</h1>
        <p>You can search your favorite venue here</p>
      </div>
    </div>
  </div>
  <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
    <span class="carousel-control-prev-icon"></span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
    <span class="carousel-control-next-icon"></span>
  </button>
</div>

<div class="container mt-4">
    <form action="index.php" method="GET" class="d-flex">
       <input list="suggestions" 
       name="query" 
       class="form-control" 
       placeholder="Search by venue name or location">

<datalist id="suggestions">
    <?php
    include 'db.php';
    $sql = "SELECT name, location FROM venues";
    $result = mysqli_query($conn, $sql);

    while ($row = mysqli_fetch_assoc($result)) {
        echo "<option value='" . $row['name'] . "'>";
        echo "<option value='" . $row['location'] . "'>";
    }
    ?>
</datalist>
        <button class="btn btn-primary ms-2">Search</button>
    </form>
</div>

<?php
if (isset($_GET['query'])) {

    include 'db.php';

    $query = mysqli_real_escape_string($conn, $_GET['query']);

    echo "<h3 class='mt-4'>Search Results for '$query'</h3>";

    $sql = "SELECT * FROM venues
            WHERE name LIKE '%$query%'
            OR location LIKE '%$query%'";

    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {

        while ($row = mysqli_fetch_assoc($result)) {

            echo "
            <div class='card p-3 mb-3'>
                <h4><a href='start_venue_booking.php?id={$row['id']}'>" . $row['name'] . "</a></h4>
                <p>Location: " . $row['location'] . "</p>
            ";

            // fetch packages
            $venue_id = $row['id'];
            $pkg_sql = "SELECT * FROM events WHERE id = $venue_id";
            $pkg_result = mysqli_query($conn, $pkg_sql);

            if (mysqli_num_rows($pkg_result) > 0) {
                echo "<h5>Packages available:</h5>";
                while ($pkg = mysqli_fetch_assoc($pkg_result)) {
echo "
    <div class='card p-2 mb-2'>
        <a href='index.php?query=" . urlencode($row['location']) . "#events-list'>
            <strong>" . $pkg['title'] . "</strong> – ₹" . $pkg['price'] . "
        </a>
    </div>";} }
            echo "</div>"; // end venue card
        }

    } else {
        echo "<p class='text-danger mt-3'>No venues found.</p>";
    }
}
?>


<!-- ================= Venue Packages (Events) ================= -->
<h2 id="events-list" class="mb-3 fw-bold text-dark">Available Venue Packages</h2>

<?php
$sql = "
SELECT 
    e.*,
    v.name AS venue_name,
    v.location,
    e.price AS display_price
FROM events e
JOIN venues v ON e.venue_id = v.id
ORDER BY e.event_date ASC
";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0): ?>
    <div class="row">
    <?php while ($row = $result->fetch_assoc()): ?>

        <?php
        // Gallery images
        $imgRes = $conn->query(
            "SELECT image FROM event_images WHERE event_id=" . (int)$row['id']
        );

        // Reviews (avg + count)
        $avg = null;
        $count = 0;
        $revRes = $conn->query(
            "SELECT AVG(rating) AS avg_rating, COUNT(*) AS total_reviews
             FROM reviews
             WHERE event_id=" . (int)$row['id']
        );
        if ($revRes && $revRes->num_rows > 0) {
            $rr    = $revRes->fetch_assoc();
            $avg   = $rr['avg_rating'];
            $count = $rr['total_reviews'];
        }
        ?>

        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <!-- IMAGE / SLIDER -->
                <?php if ($imgRes && $imgRes->num_rows > 0): ?>
                    <div id="carousel-<?php echo $row['id']; ?>" class="carousel slide" data-bs-ride="carousel">
                      <div class="carousel-inner">
                        <?php
                        $active = true;
                        while ($im = $imgRes->fetch_assoc()): ?>
                            <div class="carousel-item <?php echo $active ? 'active' : ''; ?>">
                                <img src="images/<?php echo htmlspecialchars($im['image']); ?>"
                                     class="d-block w-100 card-img-top"
                                     style="height:200px;object-fit:cover;"
                                     alt="Venue Image">
                            </div>
                        <?php
                          $active = false;
                        endwhile; ?>
                      </div>
                      <button class="carousel-control-prev" type="button"
                              data-bs-target="#carousel-<?php echo $row['id']; ?>" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon"></span>
                      </button>
                      <button class="carousel-control-next" type="button"
                              data-bs-target="#carousel-<?php echo $row['id']; ?>" data-bs-slide="next">
                        <span class="carousel-control-next-icon"></span>
                      </button>
                    </div>
                <?php elseif (!empty($row['image'])): ?>
                    <img src="images/<?php echo htmlspecialchars($row['image']); ?>"
                         class="card-img-top"
                         style="height:200px;object-fit:cover;"
                         alt="Venue Image">
                <?php endif; ?>

                <div class="card-body">
                    <span class="badge bg-warning text-dark mb-1">Featured</span>
                    <h5 class="card-title mb-1"><?php echo htmlspecialchars($row['title']); ?></h5>
                    <h6 class="card-subtitle mb-2 text-muted">
                        <?php echo htmlspecialchars($row['venue_name']); ?>
                        (<?php echo htmlspecialchars($row['location']); ?>)
                    </h6>
<span class="badge bg-danger mb-2">
  Owned by Red Door Venue Group
</span>
                    <p class="card-text mb-1">
                        <strong>Price per Guest:</strong>
                        ₹<?php echo number_format($row['display_price'], 2); ?>
                    </p>

                    <?php if ($count > 0 && $avg): ?>
                        <p class="mb-1">
                            <strong>Rating:</strong>
                            <?php echo number_format($avg, 1); ?>/5
                            (<?php echo $count; ?> reviews)
                        </p>
                    <?php else: ?>
                        <p class="text-muted mb-1 small">No reviews yet</p>
                    <?php endif; ?>

                    <p class="card-text text-truncate mb-3">
                        <?php echo nl2br(htmlspecialchars($row['description'])); ?>
                    </p>

                    <div class="d-flex gap-2">
                        <a href="book.php?event_id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">
                            Book this Venue
                        </a>
                        <a href="review.php?event_id=<?php echo $row['id']; ?>" class="btn btn-outline-secondary btn-sm">
                            Reviews
                        </a>
                    </div>
                </div>
            </div>
        </div>

    <?php endwhile; ?>
    </div>

<?php else: ?>
    <div class="alert alert-info">
        No venue packages created yet. Please login as admin and add some events.
    </div>
<?php endif; ?>


<!-- ================= Venues without any package ================= -->
<hr class="my-4">

<h3 class="mb-3">Other Registered Venues (without packages)</h3>

<?php
$venueSql = "
    SELECT v.*
    FROM venues v
    LEFT JOIN events e ON e.venue_id = v.id
    WHERE e.id IS NULL
    ORDER BY v.id DESC
";
$vres = $conn->query($venueSql);

if ($vres && $vres->num_rows > 0): ?>
    <div class="row">
        <?php while ($v = $vres->fetch_assoc()): ?>
            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($v['name']); ?></h5>
                        <h6 class="card-subtitle mb-2 text-muted">
                            <?php echo htmlspecialchars($v['location']); ?>
                        </h6>
                        <p class="mb-1">
                            <strong>Capacity:</strong>
                            <?php echo (int)$v['capacity']; ?> guests
                        <p class="card-text mb-2">
    <?php echo nl2br(htmlspecialchars($v['description'])); ?>
</p>

<p class="small text-warning mb-2">
    No packages added yet for this venue. You can still request a custom booking.
</p>

<a href="start_venue_booking.php?venue_id=<?php echo $v['id']; ?>"
   class="btn btn-primary btn-sm">
    Book this Venue
</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <div class="alert alert-light border">
        All registered venues already have packages.
    </div>
<?php endif; ?>
<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 15px;">
            
            <div class="modal-header">
                <h5 class="modal-title">Edit Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <?php include 'edit_profile_form.php'; ?>
            </div>

        </div>
    </div>
</div>
<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('profilePreview').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
<?php include 'footer.php'; ?>