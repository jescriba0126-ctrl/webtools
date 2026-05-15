<?php
ob_start();
session_start();
include 'connect.php';

$message     = "";
$messageType = "error";

if (isset($_POST['resetPassword'])) {

    $email           = $_POST['email'];
    $currentPassword = $_POST['currentPassword'];
    $newPassword     = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    // CHECK ALL FIELDS FILLED
    if (empty($email) || empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $message = "Please fill in all fields.";

    // CHECK NEW PASSWORDS MATCH
    } elseif ($newPassword !== $confirmPassword) {
        $message = "New passwords do not match!";

    // CHECK NEW PASSWORD LENGTH
    } elseif (strlen($newPassword) < 8) {
        $message = "New password must be at least 8 characters!";

    // CHECK NEW PASSWORD NOT SAME AS CURRENT
    } elseif ($currentPassword === $newPassword) {
        $message = "New password must be different from your current password!";

    } else {

        // HASH CURRENT PASSWORD TO VERIFY AGAINST DB (MD5)
        $hashedCurrent = md5($currentPassword);

        // VERIFY EMAIL + CURRENT PASSWORD MATCH
        $checkResult = mysqli_query($conn, "SELECT Id FROM users WHERE email = '$email' AND password = '$hashedCurrent'");

        if (mysqli_num_rows($checkResult) > 0) {

            // HASH NEW PASSWORD
            $hashedNew = md5($newPassword);

            $updateQuery = "UPDATE users SET password = '$hashedNew' WHERE email = '$email'";

            if (mysqli_query($conn, $updateQuery)) {
                $messageType = "success";
                $message     = "Password updated successfully! Redirecting to login...";
                header("refresh:2;url=../HTML/login.html");
            } else {
                $message = "Failed to update password. Please try again.";
            }

        } else {
            // Either email not found OR current password is wrong
            $message = "Incorrect email or current password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password</title>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="../CSS/login.css" />
  <style>
    body {
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
    }

    .forgot-box {
      overflow: visible !important;
      height: auto !important;
      width: 420px;
      border-radius: 30px !important;
    }

    .forgot-box .form-box.login {
      position: relative !important;
      width: 100% !important;
      height: auto !important;
      overflow: visible !important;
      padding: 40px 30px 35px 30px;
      border-radius: 30px !important;
    }

    .message {
      text-align: center;
      margin-bottom: 15px;
      padding: 8px 12px;
      border-radius: 6px;
      font-size: 0.9rem;
    }
    .message.error   { color: #fff; background-color: rgba(220,53,69,0.75); }
    .message.success { color: #fff; background-color: rgba(40,167,69,0.75); }

    .back-login { text-align: center; margin-top: 15px; }
    .back-login a {
      color: #dda15e;
      text-decoration: none;
      font-weight: 600;
      font-size: 14px;
    }
    .back-login a:hover { color: #c98d4f; text-decoration: underline; }

    .section-label {
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 1px;
      text-transform: uppercase;
      opacity: 0.5;
      margin: 14px 0 6px 2px;
    }

    #matchMsg { font-size: 13px; font-weight: 500; margin-bottom: 8px; }
  </style>
</head>
<body>

<div class="container forgot-box">
  <div class="form-box login">

    <form method="POST" action="forgot-password.php">

      <h1>Reset Password</h1>

      <?php if ($message !== ""): ?>
        <div class="message <?php echo $messageType; ?>">
          <?php echo htmlspecialchars($message); ?>
        </div>
      <?php endif; ?>

      <!-- EMAIL -->
      <div class="input-box">
        <input type="email" name="email" placeholder="Registered Email" required />
        <i class="bx bxs-envelope"></i>
      </div>

      <!-- CURRENT PASSWORD -->
      <div class="input-box">
        <input
          type="password"
          name="currentPassword"
          id="currentPassword"
          placeholder="Current Password"
          required
        />
        <i class="bx bxs-lock-alt" style="cursor:pointer"
           onclick="togglePassword('currentPassword', this)"></i>
      </div>

      <!-- NEW PASSWORD -->
      <div class="input-box">
        <input
          type="password"
          name="newPassword"
          id="newPassword"
          placeholder="New Password"
          minlength="8"
          required
        />
        <i class="bx bxs-lock-alt" style="cursor:pointer"
           onclick="togglePassword('newPassword', this)"></i>
      </div>

      <!-- CONFIRM NEW PASSWORD -->
      <div class="input-box">
        <input
          type="password"
          name="confirmPassword"
          id="confirmPassword"
          placeholder="Confirm New Password"
          minlength="8"
          required
        />
        <i class="bx bxs-lock-alt" style="cursor:pointer"
           onclick="togglePassword('confirmPassword', this)"></i>
      </div>

      <!-- LIVE MATCH FEEDBACK -->
      <div id="matchMsg"></div>

      <button type="submit" name="resetPassword" class="btn">Reset Password</button>

      <div class="back-login">
        <a href="../HTML/login.html">&#8592; Back to Login</a>
      </div>

    </form>
  </div>
</div>

<script>
  function togglePassword(inputId, icon) {
    const inp = document.getElementById(inputId);
    const isHidden = inp.type === "password";
    inp.type = isHidden ? "text" : "password";
    icon.className = isHidden ? "bx bxs-lock-open-alt" : "bx bxs-lock-alt";
  }

  const np  = document.getElementById('newPassword');
  const cp  = document.getElementById('confirmPassword');
  const msg = document.getElementById('matchMsg');

  function checkMatch() {
    if (!cp.value) { msg.textContent = ''; return; }
    if (np.value === cp.value) {
      msg.textContent = '✔ Passwords match';
      msg.style.color = 'green';
    } else {
      msg.textContent = '✖ Passwords do not match';
      msg.style.color = 'red';
    }
  }

  np.addEventListener('input', checkMatch);
  cp.addEventListener('input', checkMatch);
</script>

</body>
</html>