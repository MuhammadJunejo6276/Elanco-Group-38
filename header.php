<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="favicon.png" type="image/x-icon">
</head>
<header>
    <div class="logo">
    <?php
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $logoLink = isset($_SESSION['userId']) ? 'userhome.php' : 'index.php';
    ?>
        <a href="<?php echo $logoLink; ?>"><img src="logo.png" alt="Logo"></a>
    </div>
    <div class="headerstripe">
    <?php if (isset($buttonLink) && isset($buttonText) && isset($buttonClass)): ?>
        <a href="<?php echo $buttonLink; ?>" class="<?php echo $buttonClass; ?>"><?php echo $buttonText; ?></a>
    <?php endif; ?>
    </div>
</header>

