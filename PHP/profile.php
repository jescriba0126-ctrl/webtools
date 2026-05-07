<?php
session_start();
include("connect.php");

if(!isset($_SESSION['email'])){
    header("location: http://localhost/webtools-main/HTML/login.html");
    exit();
}

$email = $_SESSION['email'];

// Fetch user
$query = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
$row   = mysqli_fetch_array($query);

// ✅ Handle empty first/last name gracefully
$firstName = !empty($row['firstName']) ? $row['firstName'] : '';
$lastName  = !empty($row['lastName'])  ? $row['lastName']  : '';
$username  = trim($firstName . ' ' . $lastName);
if(empty($username)) $username = 'User';

$gender = !empty($row['gender']) ? $row['gender'] : '—';

$orderQuery = @mysqli_query($conn, "SELECT * FROM orders WHERE user_email='$email' ORDER BY order_date DESC");
if(!$orderQuery) $orderQuery = null;

// Fetch average rating
$ratingQuery  = mysqli_query($conn, "SELECT AVG(rating) as avg_rating, COUNT(*) as total FROM reviews WHERE user_email='$email'");
$ratingRow    = $ratingQuery ? mysqli_fetch_array($ratingQuery) : null;
$avgRating    = ($ratingRow && $ratingRow['avg_rating']) ? round($ratingRow['avg_rating'], 1) : null;
$totalReviews = $ratingRow ? $ratingRow['total'] : 0;

// Handle review submission
$reviewMsg = '';
if(isset($_POST['submitReview'])){
    $rating  = intval($_POST['rating']);
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);
    if($rating >= 1 && $rating <= 5){
        mysqli_query($conn, "INSERT INTO reviews(user_email, rating, comment) VALUES ('$email', $rating, '$comment')");
        $reviewMsg = "✅ Review submitted! Thank you.";
        $ratingQuery  = mysqli_query($conn, "SELECT AVG(rating) as avg_rating, COUNT(*) as total FROM reviews WHERE user_email='$email'");
        $ratingRow    = mysqli_fetch_array($ratingQuery);
        $avgRating    = round($ratingRow['avg_rating'], 1);
        $totalReviews = $ratingRow['total'];
    } else {
        $reviewMsg = "❌ Please select a valid rating.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Profile</title>
    <link rel="stylesheet" href="../CSS/profile.css">
</head>
<body>

    <header>
        <div class="logo">
            <img src="../IMAGES/logo.jpg" alt="Cubiertos Food Hub Logo" />
        </div>
        <nav>
            <a href="http://localhost/webtools-main/HTML/main.html">Home</a>
            <a href="about.html">About</a>
            <a href="Store/Store.html">Our Stores</a>
            <a href="contacts.html">Contacts</a>
            <a href="../PHP/logout.php">Logout</a>
        </nav>
    </header>

    <section class="container">

        <h1 class="title">Customer</h1>

        <div class="profile-header">
            <img src="../IMAGES/default.jpg" class="profile-pic" alt="Profile Picture">
            <div class="profile-info">
                <h2><?php echo htmlspecialchars($username); ?></h2>
                <p class="subtext">
                    <?php echo ($totalReviews > 0) ? 'VALUED CUSTOMER' : 'NEW CUSTOMER'; ?>
                </p>
            </div>
        </div>

        <div class="info-card">

            <!-- LEFT: User Info -->
            <div class="info-section">
                <h3>Information</h3>
                <p><strong>Name:</strong><br><?php echo htmlspecialchars($username); ?></p>
                <p><strong>Email:</strong><br><?php echo htmlspecialchars($email); ?></p>
                <p><strong>Gender:</strong><br><?php echo htmlspecialchars($gender); ?></p>

                <!-- REVIEW FORM -->
                <div class="review-form">
                    <h4>Leave a Review</h4>
                    <form method="POST">
                        <div class="star-select">
                            <label for="s1">★</label>
                            <label for="s2">★</label>
                            <label for="s3">★</label>
                            <label for="s4">★</label>
                            <label for="s5">★</label>
                        </div>
                        <div style="position:absolute; left:-9999px;">
                            <input type="radio" id="s1" name="rating" value="1">
                            <input type="radio" id="s2" name="rating" value="2">
                            <input type="radio" id="s3" name="rating" value="3">
                            <input type="radio" id="s4" name="rating" value="4">
                            <input type="radio" id="s5" name="rating" value="5">
                        </div>
                        <textarea name="comment" placeholder="Write your comment here (optional)..."></textarea>
                        <button type="submit" name="submitReview">Submit Review</button>
                    </form>
                    <?php if($reviewMsg): ?>
                        <p class="review-msg"><?php echo $reviewMsg; ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- RIGHT: Order History -->
            <div class="order-card">
                <h3>Order History</h3>
                <?php if($orderQuery && mysqli_num_rows($orderQuery) > 0): ?>
                    <table class="order-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while($order = mysqli_fetch_array($orderQuery)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['item_name']); ?></td>
                                <td><?php echo $order['quantity']; ?></td>
                                <td>₱<?php echo number_format($order['total_price'], 2); ?></td>
                                <td class="status-<?php echo strtolower($order['status']); ?>">
                                    <?php echo htmlspecialchars($order['status']); ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="empty">No orders yet...</p>
                <?php endif; ?>
            </div>

        </div>

    </section>

    <footer>
        <div class="footer-info">
            <nav>
                <a href="main.html">Home</a>
                <a href="about.html">About</a>
                <a href="Store/Store.html">Our Stores</a>
                <a href="contacts.html">Contacts</a>
                <a href="../PHP/logout.php">Logout</a>
            </nav>
            <p>Food & Drink · Virac, Philippines, 4800 | Contact Info: 0981 027 0704</p>
            <p>Copyright © 2025 Cubiertos.food.hub</p>
        </div>
        <div class="footer-socials">
            <a href="https://www.facebook.com/profile.php?id=61555258696901" target="_blank">
                <img src="../IMAGES/Facebook.png" alt="Facebook" />
            </a>
            <a href="https://www.instagram.com/cubiertos2024/" target="_blank">
                <img src="../IMAGES/Instagram.png" alt="Instagram" />
            </a>
            <a href="https://mail.google.com/mail/u/0/#sent" target="_blank">
                <img src="../IMAGES/Mail.png" alt="Mail" />
            </a>
        </div>
    </footer>

    <script>
        const labels = document.querySelectorAll('.star-select label');
        const inputs = document.querySelectorAll('input[name="rating"]');
        let selectedRating = -1;

        labels.forEach((label, index) => {
            label.addEventListener('click', () => {
                selectedRating = index;
                inputs[index].checked = true;
                updateStars(index);
            });
            label.addEventListener('mouseover', () => {
                labels.forEach((l, i) => {
                    l.style.color = i <= index ? '#f5c518' : '#333';
                });
            });
            label.addEventListener('mouseout', () => {
                updateStars(selectedRating);
            });
        });

        function updateStars(index) {
            labels.forEach((l, i) => {
                if (i <= index) {
                    l.style.color = '#f5c518';
                    l.style.filter = 'drop-shadow(0 0 4px rgba(245, 197, 24, 0.7))';
                } else {
                    l.style.color = '#333';
                    l.style.filter = 'none';
                }
            });
        }

        window.addEventListener('scroll', function () {
            const header = document.querySelector('header');
            header.classList.toggle('scrolled', window.scrollY > 50);
        });
    </script>

</body>
</html>