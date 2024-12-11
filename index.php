<?php
// Connect to the database
$host = 'localhost';
$user = 'root'; // Default XAMPP user
$pass = '';
$dbname = 'video_db';

$conn = new mysqli($host, $user, $pass, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle file upload when form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get video details
    $title = $_POST['title'];
    $target_dir = "videos/";
    $target_file = $target_dir . basename($_FILES["videoFile"]["name"]);

    // Base URL for your server (adjust based on your server settings)
    $base_url = "http://localhost/my%20folder/video-website/";

    // Move the uploaded file to the server
    if (move_uploaded_file($_FILES["videoFile"]["tmp_name"], $target_file)) {
        // Construct the full file path URL
        $file_url = $base_url . $target_file;

        // Insert video details into the database (store the full URL)
        $sql = "INSERT INTO videos (title, file_path) VALUES ('$title', '$file_url')";
        
        if ($conn->query($sql) === TRUE) {
            // Redirect to index.php and pass the video file name
            header("Location: index.php?file=" . basename($_FILES["videoFile"]["name"]));
            exit();
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        echo "Error uploading the video.";
    }
}

// Fetch videos from the database
$sql = "SELECT * FROM videos";
$result = $conn->query($sql);

$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Website</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="video-container">

<br>
<!-- Upload Form -->
<form action="index.php" method="post" enctype="multipart/form-data">
<fieldset><legend><h1>Upload a New Video</h1></legend>
    Video Title: <input type="text" name="title" required><br><br>
    Select video to upload:
    <input type="file" name="videoFile" accept="video/*" required><br><br>
    <input type="submit" value="Upload Video">
	</fieldset>
</form>




<?php
if ($result->num_rows > 0) {
    // Output video list
    while ($row = $result->fetch_assoc()) {
        echo '<div class="video-item"><h1>Video Library</h1>';
        echo '<h3>' . $row['title'] . '</h3>';
        echo '<video controls>';
        $p = $row['file_path'];
        echo '<source src="'.$p.'" type="video/mp4">';
        echo 'Your browser does not support the video tag.';
        echo '</video>';
        echo '</div>';
		}
	}	
 else {
    echo 'No videos found!';
	}
?>
</div>

<!-- Enlarged video display -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const videoItems = document.querySelectorAll('.video-item');
    const enlargedVideo = document.createElement('div');
    const closeButton = document.createElement('button');
    const videoElement = document.createElement('video');

    // Set up the enlarged video container
    enlargedVideo.className = 'enlarged-video';
    closeButton.className = 'close';
    closeButton.textContent = 'X';
    enlargedVideo.appendChild(closeButton);
    enlargedVideo.appendChild(videoElement);
    document.body.appendChild(enlargedVideo);

    // Add click event to video items
    videoItems.forEach(item => {
        item.addEventListener('click', function() {
            const videoSource = item.querySelector('source').getAttribute('src');
            videoElement.src = videoSource;
            videoElement.controls = true;
            videoElement.play();  // Play the video when enlarged
            enlargedVideo.classList.add('show');
        });
    });

    // Add click event to close button
    closeButton.addEventListener('click', function() {
        enlargedVideo.classList.remove('show');
        videoElement.pause();  // Pause video when closed
        videoElement.src = ''; // Clear the video source
    });
});
</script>


</body>
</html>
