<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; 
}
$db_file = "ElancoDatabase.db";
try {
    $conn = new PDO("sqlite:" . $db_file);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $user_id = $_SESSION['user_id'];

    $pet_query = $conn->prepare("SELECT PetID, Name FROM Pet WHERE Owner_ID = ?");
    $pet_query->execute([$user_id]);
    $pet_data = $pet_query->fetch(PDO::FETCH_ASSOC);

    if ($pet_data) {
        $pet_id = $pet_data['PetID'];
        $pet_name = htmlspecialchars($pet_data['Name'], ENT_QUOTES, 'UTF-8');

        $activity_query = $conn->prepare("
            SELECT pa.Behaviour_ID, b.Behaviour_Desc 
            FROM Pet_Activity pa
            JOIN Behaviour b ON pa.Behaviour_ID = b.Behaviour_ID
            WHERE pa.PetID = ?
            ORDER BY pa.Data_ID DESC 
            LIMIT 1
        ");
        $activity_query->execute([$pet_id]);
        $activity_data = $activity_query->fetch(PDO::FETCH_ASSOC);

        if ($activity_data) {
            $current_activity = htmlspecialchars($activity_data['Behaviour_Desc'], ENT_QUOTES, 'UTF-8');

            $activity_images = [
                'Normal' => 'ElancoPics/normal.avif',
                'Sleeping' => 'ElancoPics/sleeping.webp',
                'Eating' => 'ElancoPics/dog-eating-out-of-bowl.webp',
                'Walking' => 'ElancoPics/walking.webp',
                'Playing' => 'ElancoPics/playing.jpg'
            ];

            echo "<div class='activity-status'>";
            echo "<p>Your {$pet_name} is currently <strong>{$current_activity}</strong></p>";

            if (isset($activity_images[$current_activity])) {
                echo "<img src='{$activity_images[$current_activity]}' alt='{$current_activity}'>";
            } else {
                echo "<p>No image available for this activity.</p>";
            }

            echo "</div>";
        } else {
            echo "<p>No recent activity found for {$pet_name}</p>";
        }
    } else {
        echo "<p>No pet registered for this account</p>";
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Current Activity</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'header.php'?>

    </nav>
    <div>
        <H2>Current Activity</H2>
    </div>
    <div class="footer">
        <div class="social-icons">
            <img src="ElancoPics/emailicon.webp" alt="Email">
            <img src="ElancoPics/fblogo.png" alt="Facebook">
            <img src="ElancoPics/instalogo.png" alt="Instagram">
            <img src="ElancoPics/twitterlogo.png" alt="Twitter">
        </div>
        <div class="rights">&copy; 2025 Elanco. All rights reserved.</div>
    </div>

    <script>
        function toggleDropdown() {
            var dropdown = document.getElementById("profileDropdown");
            dropdown.style.display = (dropdown.style.display === "block") ? "none" : "block";
        }

        document.addEventListener("click", function(event) {
            var profileContainer = document.querySelector(".profile-container");
            var dropdown = document.getElementById("profileDropdown");

            if (!profileContainer.contains(event.target)) {
                dropdown.style.display = "none";
            }
        });
    </script>
</body>
</html>
