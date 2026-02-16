<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}
include 'connect.php';
$email = $_SESSION['email'];

$sql = "SELECT * FROM bookings WHERE user_email = ? ORDER BY booking_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings</title>
    <link rel="stylesheet" href="services.css">
    <style>
        .booking-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        h2 { text-align: center; margin-bottom: 20px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background-color: orangered; color: white; }
        tr:hover { background-color: #f1f1f1; }
        .cancel-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 6px 14px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background-color 0.2s;
        }
        .cancel-btn:hover { background-color: #a71d2a; }
        @media (max-width: 768px) {
            table, thead, tbody, th, td, tr { display: block; }
            thead tr { position: absolute; top: -9999px; left: -9999px; }
            tr { border: 1px solid #ccc; margin-bottom: 10px; border-radius: 8px; padding: 10px; }
            td { border: none; position: relative; padding-left: 50%; }
            td:before { position: absolute; top: 12px; left: 10px; width: 45%; padding-right: 10px; white-space: nowrap; font-weight: bold; content: attr(data-label); }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="Homepage.php" class="nav-link">Home</a>
    <img src="../Images/logo.png" alt="Company Logo" class="logo">
    <a href="services.php" class="logout-btn">Services</a>
</nav>

<div class="booking-container">
    <h2>My Booking History</h2>
    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Truck</th>
                    <th>Date</th>
                    <th>To Address</th>
                    <th>Distance</th>
                    <th>Cost</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td data-label="Truck"><?php echo htmlspecialchars($row['truck_name']); ?></td>
                        <td data-label="Date"><?php echo htmlspecialchars($row['booking_date']); ?></td>
                        <td data-label="Address"><?php echo htmlspecialchars($row['address']); ?></td>
                        <td data-label="Distance"><?php echo htmlspecialchars($row['distance_km']); ?> km</td>
                        <td data-label="Cost">₹<?php echo htmlspecialchars($row['total_cost']); ?></td>
                        <td data-label="Status" style="color: green; font-weight: bold;">Confirmed</td>
                        <td data-label="Action">
                            <form action="cancel_booking.php" method="POST" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="cancel-btn">Cancel</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="text-align: center; font-size: 1.2rem;">You have no bookings yet.</p>
    <?php endif; ?>
</div>

</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
