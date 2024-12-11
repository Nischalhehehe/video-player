<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "movie_booking";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle booking form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['movie_id'])) {
    $movie_id = intval($_POST['movie_id']);
    $customer_name = $_POST['customer_name'];
    $seats = intval($_POST['seats']);

    // Insert booking into the database
    $stmt = $conn->prepare("INSERT INTO bookings (movie_id, customer_name, seats) VALUES (?, ?, ?)");
    $stmt->bind_param("isi", $movie_id, $customer_name, $seats);

    if ($stmt->execute()) {
        $message = "Booking successful for $seats seats!";
    } else {
        $message = "Error booking seats: " . $conn->error;
    }

    $stmt->close();
}

// Fetch movies
$sql_movies = "SELECT * FROM movies";
$result_movies = $conn->query($sql_movies);

// Fetch bookings and count per movie
$sql_bookings = "SELECT movies.name, movies.showtime, COUNT(bookings.id) AS booking_count
                 FROM movies
                 LEFT JOIN bookings ON movies.id = bookings.movie_id
                 GROUP BY movies.id";
$result_bookings = $conn->query($sql_bookings);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Movie Booking System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }
        h1, h2 {
            color: #333;
        }
        .movie-item {
            margin-bottom: 20px;
        }
        .booking-form {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .message {
            margin-bottom: 20px;
            color: green;
        }
    </style>
</head>
<body>
    <h1>Movie Booking System</h1>
    <?php if (isset($message)) { echo "<p class='message'>$message</p>"; } ?>

    <h2>Available Movies</h2>
    <?php if ($result_movies->num_rows > 0) { ?>
        <form action="movie.php" method="post" class="booking-form">
            <label for="movie">Select a movie:</label><br>
            <select name="movie_id" required>
                <?php while ($row = $result_movies->fetch_assoc()) { ?>
                    <option value="<?php echo $row['id']; ?>">
                        <?php echo $row['name'] . " - " . date("F j, Y, g:i a", strtotime($row['showtime'])); ?>
                    </option>
                <?php } ?>
            </select><br><br>

            <label for="customer_name">Your Name:</label><br>
            <input type="text" name="customer_name" required><br><br>

            <label for="seats">Number of Seats:</label><br>
            <input type="number" name="seats" min="1" required><br><br>

            <input type="submit" value="Book Now">
        </form>
    <?php } else { ?>
        <p>No movies available.</p>
    <?php } ?>

    <h2>Booking Summary</h2>
    <?php if ($result_bookings->num_rows > 0) { ?>
        <ul>
            <?php while ($row = $result_bookings->fetch_assoc()) { ?>
                <li><?php echo $row['name'] . " - " . date("F j, Y, g:i a", strtotime($row['showtime'])) . ": " . $row['booking_count'] . " seats booked."; ?></li>
            <?php } ?>
        </ul>
    <?php } else { ?>
        <p>No bookings yet.</p>
    <?php } ?>

</body>
</html>

<?php
$conn->close();
?>
