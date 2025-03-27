<?php
session_start();

$userId = $_GET['user_id'] ?? 1;

try {
    $conn = new PDO('sqlite:ElancoDatabase.db');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("SELECT User_ID, F_Name, L_Name FROM Pet_Owners WHERE User_ID = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['user_id'] = $user['User_ID'];
        $_SESSION['full_name'] = $user['F_Name'] . " " . $user['L_Name'];

        $redirectUrl = $_SERVER['HTTP_REFERER'] ?? 'current_activity.php';
        header("Location: $redirectUrl");
        exit();
    } else {
        echo "Invalid user ID.";
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
