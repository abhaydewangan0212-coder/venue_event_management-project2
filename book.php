<?php
require 'db.php';
include 'header.php';



// Check what user is booking
$event_id = isset($_GET['event_id']) ? (int) $_GET['event_id'] : 0;
$venue_id = isset($_GET['venue_id']) ? (int) $_GET['venue_id'] : 0;

$is_package_booking = ($event_id > 0);
$is_venue_booking = ($venue_id > 0 && $event_id == 0);

// -----------------------------
// PACKAGE / EVENT BOOKING
// -----------------------------
if ($is_package_booking) {

    $event_sql = "SELECT events.*, venues.name AS venue_name, venues.price AS venue_price
                  FROM events
                  JOIN venues ON events.venue_id = venues.id
                  WHERE events.id = $event_id";

    $event_result = $conn->query($event_sql);

    if ($event_result->num_rows == 0) {
        echo "<div class='alert alert-danger'>Event not found.</div>";
        include 'footer.php';
        exit;
    }

    $event = $event_result->fetch_assoc();
    $venue = $event;  
    $final_price = $event['price']; // Package price
}

// -----------------------------
// VENUE ONLY BOOKING
// -----------------------------
if ($is_venue_booking) {

    $venue_sql = "SELECT * FROM venues WHERE id = $venue_id";
    $venue_result = $conn->query($venue_sql);

    if ($venue_result->num_rows == 0) {
        echo "<div class='alert alert-danger'>Venue not found.</div>";
        include 'footer.php';
        exit;
    }

    $venue = $venue_result->fetch_assoc();
    $final_price = $venue['price']; // Venue price
}

$prefill_name  = $_SESSION['user_name']  ?? "";
$prefill_email = $_SESSION['user_email'] ?? "";


$step = 1;
$billData = [];
$successMsg = "";
$errorMsg = "";

if (isset($_POST['final_submit'])) {
    $name   = $conn->real_escape_string($_POST['name']);
    $email  = $conn->real_escape_string($_POST['email']);
    $event_date = $conn->real_escape_string($_POST['event_date']);
    $occasion   = $conn->real_escape_string($_POST['occasion']);
    $theme      = $conn->real_escape_string($_POST['theme']);
    $room_type  = $conn->real_escape_string($_POST['room_type']);
    $guests     = (int) $_POST['guests'];
    $food_menu_id = (int) $_POST['food_menu_id'];
    $payment_method = $conn->real_escape_string($_POST['payment_method']);
    $transaction_id = $conn->real_escape_string($_POST['transaction_id']);

    
    if ($payment_method === 'Cash at Venue' && trim($transaction_id) === '') {
        $transaction_id = 'Cash at venue';
    }

    // 2) Online payments ke liye Transaction ID compulsory
    if ($payment_method !== 'Cash at Venue' && trim($transaction_id) === '') {
        $errorMsg = "Please enter a transaction / reference ID for online payments.";
    } else {

        $food_q = $conn->query("SELECT * FROM food_menu WHERE id=$food_menu_id");
        if (!$food_q || $food_q->num_rows == 0) {
            $errorMsg = "Selected food menu not found.";
        } else {
            $food     = $food_q->fetch_assoc();
            $food_menu_name = $food['name'];
            $food_cost_per  = (float) $food['price_per_person'];

            $price_per_person = (float) $event['price'];
            $food_total       = $food_cost_per * $guests;
            $total_amount     = ($price_per_person * $guests) + $food_total;

        
            $status = ($payment_method === 'Cash at Venue') ? 'Unpaid' : 'Paid';

            $sql = "INSERT INTO bookings(
                        event_id, customer_name, customer_email, tickets,
                        event_date, occasion, theme, food_menu, food_cost, room_type,
                        guests, total_amount, payment_method, transaction_id, status
                    )
                    VALUES (
                        $event_id, '$name', '$email', $guests,
                        '$event_date', '$occasion', '$theme', '$food_menu_name', $food_cost_per, '$room_type',
                        $guests, $total_amount, '$payment_method', '$transaction_id', '$status'
                    )";

            if ($conn->query($sql)) {
                $successMsg = "Booking confirmed! Your venue has been successfully reserved.";
                $step = 3;
                $billData = [
                    'name' => $name,
                    'email' => $email,
                    'event_date' => $event_date,
                    'occasion' => $occasion,
                    'theme' => $theme,
                    'room_type' => $room_type,
                    'guests' => $guests,
                    'price_per_person' => $price_per_person,
                    'food_menu' => $food_menu_name,
                    'food_cost_per' => $food_cost_per,
                    'food_total' => $food_total,
                    'total_amount' => $total_amount,
                    'payment_method' => $payment_method,
                    'transaction_id' => $transaction_id
                ];
            } else {
                $errorMsg = "Error saving booking: " . $conn->error;
            }
        }
    }
}

 elseif (isset($_POST['preview'])) {
    $name   = $_POST['name'];
    $email  = $_POST['email'];
    $event_date = $_POST['event_date'];
    $occasion   = $_POST['occasion'];
    $theme      = $_POST['theme'];
    $room_type  = $_POST['room_type'];
    $guests     = (int) $_POST['guests'];
    $food_menu_id = (int) $_POST['food_menu_id'];

    $food_q = $conn->query("SELECT * FROM food_menu WHERE id=$food_menu_id");
    if (!$food_q || $food_q->num_rows == 0) {
        $errorMsg = "Please select a valid food menu.";
    } else {
        $food     = $food_q->fetch_assoc();
        $food_menu_name = $food['name'];
        $food_cost_per  = (float) $food['price_per_person'];

        $price_per_person = (float) $event['price'];
        $food_total       = $food_cost_per * $guests;
        $total_amount     = ($price_per_person * $guests) + $food_total;

        $step = 2;
        $billData = [
            'name' => $name,
            'email' => $email,
            'event_date' => $event_date,
            'occasion' => $occasion,
            'theme' => $theme,
            'room_type' => $room_type,
            'guests' => $guests,
            'price_per_person' => $price_per_person,
            'food_menu_id' => $food_menu_id,
            'food_menu' => $food_menu_name,
            'food_cost_per' => $food_cost_per,
            'food_total' => $food_total,
            'total_amount' => $total_amount
        ];
    }
}
?>

<div class="row">
    <div class="col-md-7">
        <?php
        
// gallery images for this event
$imgRes = $conn->query(
    "SELECT image FROM event_images WHERE event_id=" . (int)$event_id
);
?>

<?php if ($imgRes && $imgRes->num_rows > 0): ?>
    <div id="eventCarousel-<?php echo $event_id; ?>" class="carousel slide mb-3" data-bs-ride="carousel">
      <div class="carousel-inner">
        <?php
        $active = true;
        while ($im = $imgRes->fetch_assoc()): ?>
            <div class="carousel-item <?php echo $active ? 'active' : ''; ?>">
                <img src="images/<?php echo htmlspecialchars($im['image']); ?>"
                     class="d-block w-100 img-fluid rounded"
                     style="max-height:1000px;object-fit:cover;"
                     alt="Venue Image">
            </div>
        <?php
          $active = false;
        endwhile; ?>
      </div>
      <button class="carousel-control-prev" type="button"
              data-bs-target="#eventCarousel-<?php echo $event_id; ?>" data-bs-slide="prev">
        <span class="carousel-control-prev-icon"></span>
      </button>
      <button class="carousel-control-next" type="button"
              data-bs-target="#eventCarousel-<?php echo $event_id; ?>" data-bs-slide="next">
        <span class="carousel-control-next-icon"></span>
      </button>
    </div>
<?php elseif (!empty($event['image'])): ?>
    <img src="images/<?php echo htmlspecialchars($event['image']); ?>"
         class="img-fluid rounded mb-3"
         style="max-height:1000px;object-fit:cover;"
         alt="Venue Image">
<?php endif; ?>
    </div>
    <div class="col-md-5">
        <h2><?php echo htmlspecialchars($event['title']); ?></h2>
        <p><strong>Venue:</strong> <?php echo htmlspecialchars($event['venue_name']); ?></p>
        <p><strong>Base Price (per guest):</strong> ₹<?php echo htmlspecialchars($event['price']); ?></p>
        <p><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>

        <?php if ($errorMsg): ?>
            <div class="alert alert-danger"><?php echo $errorMsg; ?></div>
        <?php endif; ?>

        <?php if ($step === 1): ?>
            <?php $food_items_res = $conn->query("SELECT * FROM food_menu WHERE is_active = 1 ORDER BY name ASC"); ?>
            <h4 class="mt-3">Booking Details</h4>
            <form method="post" class="mt-2">
                <div class="mb-2">
                    <label class="form-label">Your Name</label>
                    <input type="text" name="name" class="form-control"
                           value="<?php echo htmlspecialchars($prefill_name); ?>" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Your Email</label>
                    <input type="email" name="email" class="form-control"
                           value="<?php echo htmlspecialchars($prefill_email); ?>" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Date of Occasion</label>
                    <input type="date" name="event_date" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Occasion</label>
                    <select name="occasion" class="form-select" required>
                        <option value="">Select Occasion</option>
                        <option>Birthday Party</option>
                        <option>Wedding / Reception</option>
                        <option>Engagement</option>
                        <option>Corporate Meeting</option>
                        <option>Seminar / Workshop</option>
                        <option>Christmas</option>
                        <option>Cultural Events</option>
                        <option>Festival Celebration</option>
                        <option>Freshers/Farewell</option>
                        <option>Cocktail</option>
                        <option>Other</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label">Theme</label>
                    <select name="theme" class="form-select" required>
                        <option value="">Select Theme</option>
                        <option>Royal</option>
                        <option>Simple & Elegant</option>
                        <option>Floral</option>
                        <option>Vintage</option>
                        <option>Bollywood</option>
                        <option>Garden/Outdoor</option>
                        <option>Modern Chic</option>
                        <option>Fairytale</option>
                        <option>Corporate</option>
                        <option>Traditional</option>
                        <option>Custom</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label">Room / Hall Requirement</label>
                    <select name="room_type" class="form-select" required>
                        <option value="">Select Requirement</option>
                        <option>Main Hall Only</option>
                        <option>Hall + 5 Rooms</option>
                        <option>Hall + 10 Rooms</option>
                        <option>Outdoor Lawn + Hall</option>
                        <option>Conference Room</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label">Number of Guests</label>
                    <input type="number" name="guests" class="form-control" min="10" value="50" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Food Menu</label>
                    <select name="food_menu_id" class="form-select" required>
                        <option value="">Select Food Menu</option>
                        <?php if ($food_items_res && $food_items_res->num_rows > 0): ?>
                            <?php while ($fi = $food_items_res->fetch_assoc()): ?>
                                <option value="<?php echo $fi['id']; ?>">
                                    <?php echo htmlspecialchars($fi['name']); ?> – ₹<?php echo number_format($fi['price_per_person'], 2); ?> per person
                                </option>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <option disabled>No food menu configured</option>
                        <?php endif; ?>
                    </select>
                </div>

                <button type="submit" name="preview" class="btn btn-primary mt-2">
                    Generate Bill
                </button>
                <a href="index.php" class="btn btn-secondary mt-2">Back</a>
            </form>

        <?php elseif ($step === 2): ?>

            <h4>Bill Preview</h4>
            <div class="card mt-2 shadow-sm">
                <div class="card-body">
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($billData['name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($billData['email']); ?></p>
                    <p><strong>Date:</strong> <?php echo htmlspecialchars($billData['event_date']); ?></p>
                    <p><strong>Occasion:</strong> <?php echo htmlspecialchars($billData['occasion']); ?></p>
                    <p><strong>Theme:</strong> <?php echo htmlspecialchars($billData['theme']); ?></p>
                    <p><strong>Room Type:</strong> <?php echo htmlspecialchars($billData['room_type']); ?></p>
                    <p><strong>Guests:</strong> <?php echo htmlspecialchars($billData['guests']); ?></p>
                    <p><strong>Food Menu:</strong> <?php echo htmlspecialchars($billData['food_menu']); ?></p>
                    <p><strong>Food Cost per Guest:</strong> ₹<?php echo number_format($billData['food_cost_per'], 2); ?></p>
                    <p><strong>Food Total:</strong> ₹<?php echo number_format($billData['food_total'], 2); ?></p>
                    <hr>
                    <p><strong>Venue Price per Guest:</strong> ₹<?php echo number_format($billData['price_per_person'], 2); ?></p>
                    <h5><strong>Total Amount:</strong> ₹<?php echo number_format($billData['total_amount'], 2); ?></h5>
                </div>
            </div>

            <h5 class="mt-3">Payment Details</h5>
            <form method="post" class="mt-2">
                <input type="hidden" name="name" value="<?php echo htmlspecialchars($billData['name']); ?>">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($billData['email']); ?>">
                <input type="hidden" name="event_date" value="<?php echo htmlspecialchars($billData['event_date']); ?>">
                <input type="hidden" name="occasion" value="<?php echo htmlspecialchars($billData['occasion']); ?>">
                <input type="hidden" name="theme" value="<?php echo htmlspecialchars($billData['theme']); ?>">
                <input type="hidden" name="room_type" value="<?php echo htmlspecialchars($billData['room_type']); ?>">
                <input type="hidden" name="guests" value="<?php echo htmlspecialchars($billData['guests']); ?>">
                <input type="hidden" name="food_menu_id" value="<?php echo htmlspecialchars($billData['food_menu_id']); ?>">
<!-- ================= PAYMENT DETAILS ================= -->


<div class="mb-3">
    <label class="form-label">Payment Method</label>
    <select name="payment_method" id="payment_method" class="form-control" required>
        <option value="">-- Select Payment Method --</option>
        <option value="UPI">UPI</option>
        <option value="Card">Card</option>
        <option value="Net Banking">Net Banking</option>
        <option value="Cash at Venue">Cash at Venue</option>
    </select>
</div>

<!-- UPI QR CODE BOX -->
<div id="upi_qr_box" style="display:none; margin-bottom:15px; text-align:center;">
    <p><strong>Scan & Pay via UPI</strong></p>
    <img src="assets/upi_qr.png"
         alt="UPI QR Code"
         style="width:220px; background:#fff; padding:10px; border-radius:10px; border:1px solid #ccc;">
    <p style="font-size:13px; color:#555; margin-top:5px;">
        Scan the QR code and enter the UPI Transaction ID below
    </p>
</div>

<!-- TRANSACTION ID -->
<div class="mb-3" id="txn_box">
    <label class="form-label">Transaction ID / UPI Ref No.</label>
    <input type="text"
           name="transaction_id"
           id="transaction_id"
           class="form-control"
           placeholder="Enter transaction / reference ID">
</div>

<!-- BUTTONS -->
<div class="mt-3">
    <button type="submit" name="final_submit" class="btn btn-success">
        Confirm Booking
    </button>
    <a href="index.php" class="btn btn-secondary">
        Back
    </a>
</div>
<!-- =================================================== -->

        <?php elseif ($step === 3 && $successMsg): ?>

            <div class="alert alert-success"><?php echo $successMsg; ?></div>
            <div class="card mt-2 shadow-sm">
                <div class="card-body">
                    <h5>Booking Summary</h5>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($billData['name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($billData['email']); ?></p>
                    <p><strong>Date:</strong> <?php echo htmlspecialchars($billData['event_date']); ?></p>
                    <p><strong>Occasion:</strong> <?php echo htmlspecialchars($billData['occasion']); ?></p>
                    <p><strong>Theme:</strong> <?php echo htmlspecialchars($billData['theme']); ?></p>
                    <p><strong>Room Type:</strong> <?php echo htmlspecialchars($billData['room_type']); ?></p>
                    <p><strong>Guests:</strong> <?php echo htmlspecialchars($billData['guests']); ?></p>
                    <p><strong>Food Menu:</strong> <?php echo htmlspecialchars($billData['food_menu']); ?></p>
                    <p><strong>Food Cost per Guest:</strong> ₹<?php echo number_format($billData['food_cost_per'], 2); ?></p>
                    <p><strong>Food Total:</strong> ₹<?php echo number_format($billData['food_total'], 2); ?></p>
                    <hr>
                    <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($billData['payment_method']); ?></p>
                    <p><strong>Transaction ID:</strong> <?php echo htmlspecialchars($billData['transaction_id']); ?></p>
                    <h5><strong>Total Paid:</strong> ₹<?php echo number_format($billData['total_amount'], 2); ?></h5>
                </div>
            </div>
            <a href="index.php" class="btn btn-primary mt-3">Back to Home</a>

        <?php endif; ?>
    </div>
</div>
<script>
const paymentSelect = document.getElementById("payment_method");
const qrBox = document.getElementById("upi_qr_box");
const txnBox = document.getElementById("txn_box");
const txnInput = document.getElementById("transaction_id");

paymentSelect.addEventListener("change", function () {
    let method = this.value;

    if (method === "UPI") {
        qrBox.style.display = "block";
        txnBox.style.display = "block";
        txnInput.required = true;
        txnInput.placeholder = "Enter UPI Transaction ID";
    }
    else if (method === "Cash at Venue") {
        qrBox.style.display = "none";
        txnBox.style.display = "none";
        txnInput.required = false;
        txnInput.value = "";
    }
    else {
        qrBox.style.display = "none";
        txnBox.style.display = "block";
        txnInput.required = true;
        txnInput.placeholder = "Enter transaction / reference ID";
    }
});
</script>

<?php include 'footer.php'; ?>