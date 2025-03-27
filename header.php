<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="header">
    <img src="ElancoPics/elancologo.png" alt="Elanco Logo">
    <div class="profile-container" onclick="toggleDropdown()">
        <img src="ElancoPics/profileicon.png" alt="Profile">
        <button class="profile-button">
            <?php
            if (isset($_SESSION['user_id'])) {
                try {
                    $conn = new PDO('sqlite:ElancoDatabase.db');
                    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    $stmt = $conn->prepare("SELECT F_Name, L_Name FROM Pet_Owners WHERE User_ID = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);

                    echo $user ? "{$user['F_Name']} {$user['L_Name']} ▼" : "John Doe ▼";
                } catch (PDOException $e) {
                    echo "John Doe ▼";
                }
            } else {
                echo "John Doe ▼";
            }
            ?>
        </button>
        <div class="dropdown" id="profileDropdown">
            <?php
            try {
                $stmt = $conn->query("SELECT User_ID, F_Name, L_Name FROM Pet_Owners");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $selected = (!isset($_SESSION['user_id']) && $row['F_Name'] === 'John' && $row['L_Name'] === 'Doe') ? 'selected' : '';
                    echo "<a href='#' onclick='loginUser(\"{$row['User_ID']}\")'>{$row['F_Name']} {$row['L_Name']}</a>";
                }
            } catch (PDOException $e) {
                echo "<a href='#'>Error loading users</a>";
            }
            ?>
        </div>
    </div>
</div>

<nav class="navbar">
    <div class="nav-links">
        <a href="current_activity.php" class="<?= ($current_page == 'current_activity.php') ? 'active' : '' ?>">Current Activity</a>
        <a href="activity_level.php" class="<?= ($current_page == 'activity_level.php') ? 'active' : '' ?>">Activity Level</a>
        <a href="heart_rate.php" class="<?= ($current_page == 'heart_rate.php') ? 'active' : '' ?>">Heart Rate</a>
        <a href="temperature.php" class="<?= ($current_page == 'temperature.php') ? 'active' : '' ?>">Temperature</a>
        <a href="weight.php" class="<?= ($current_page == 'weight.php') ? 'active' : '' ?>">Weight</a>
        <a href="food&water.php" class="<?= ($current_page == 'food&water.php') ? 'active' : '' ?>">Food and Water Intake</a>
        <a href="calorie_burn.php" class="<?= ($current_page == 'calorie_burn.php') ? 'active' : '' ?>">Calorie Burn</a>
        <a href="breathing_rate.php" class="<?= ($current_page == 'breathing_rate.php') ? 'active' : '' ?>">Breathing Rate</a>
        <a href="behaviour_pattern.php" class="<?= ($current_page == 'behaviour_pattern.php') ? 'active' : '' ?>">Behaviour Pattern</a>
        <a href="barking_frequency.php" class="<?= ($current_page == 'barking_frequency.php') ? 'active' : '' ?>">Barking Frequency</a>
    </div>
</nav>

<script>
    function toggleDropdown() {
        const dropdown = document.getElementById("profileDropdown");
        dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
    }

    function loginUser(userId) {
        if (userId) {
            window.location.href = `login_handler.php?user_id=${userId}`;
        }
    }

    document.addEventListener("click", function (event) {
        const profileContainer = document.querySelector(".profile-container");
        const dropdown = document.getElementById("profileDropdown");

        if (!profileContainer.contains(event.target)) {
            dropdown.style.display = "none";
        }
    });
</script>
