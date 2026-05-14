<?php
session_start();
session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Logging out...</title>
    <script>
        // Clear the loader memory so the animation plays again on the next login
        sessionStorage.removeItem("hasSeenLoader");
        
        // Redirect to your specific login page
        window.location.href = "http://localhost/webtools-main/HTML/login.html";
    </script>
</head>
<body>
</body>
</html>