<?php
include 'connect.php';

$message = "";
$messageType = "error";

if (isset($_POST['resetPassword'])) {

    $email = trim($_POST['email']);
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    if ($newPassword !== $confirmPassword) {
        $message = "Passwords do not match!";

    } elseif (strlen($newPassword) < 8) {
        $message = "Password must be at least 8 characters!";

    } else {

        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {

            $stmt->close();

            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

            $update = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $update->bind_param("ss", $hashedPassword, $email);

            if ($update->execute()) {
                $messageType = "success";
                $message = "Password updated successfully! Redirecting to login...";
                header("refresh:2;url=../HTML/login.html");
            } else {
                $message = "Failed to update password. Please try again.";
            }

            $update->close();

        } else {
            $stmt->close();
            $message = "No account found with that email.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Forgot Password</title>

  <link
    href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css"
    rel="stylesheet"
  />

  <link rel="stylesheet" href="../CSS/login.css" />

  <style>

    body {
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
    }

    /* OVERRIDE container/form-box for forgot page only */
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

    .message.error {
      color: #fff;
      background-color: rgba(220, 53, 69, 0.75);
    }

    .message.success {
      color: #fff;
      background-color: rgba(40, 167, 69, 0.75);
    }

    .back-login {
      text-align: center;
      margin-top: 15px;
    }

    .back-login a {
      color: #dda15e;
      text-decoration: none;
      font-weight: 600;
      font-size: 14px;
    }

    .back-login a:hover {
      color: #c98d4f;
      text-decoration: underline;
    }

  </style>
</head>

<body>

<div class="container forgot-box">
  <div class="form-box login">

    <form method="POST" action="forgot-password.php">

      <h1>Forgot Password</h1>

      <?php if ($message !== ""): ?>
        <div class="message <?php echo $messageType; ?>">
          <?php echo htmlspecialchars($message); ?>
        </div>
      <?php endif; ?>

      <div class="input-box">
        <input
          type="email"
          name="email"
          placeholder="Enter Email"
          required
        />
        <i class="bx bxs-envelope"></i>
      </div>

      <div class="input-box">
        <input
          type="password"
          name="newPassword"
          id="newPassword"
          placeholder="New Password"
          minlength="8"
          required
        />
        <i
          class="bx bxs-lock-alt"
          onclick="togglePassword('newPassword', this)"
        ></i>
      </div>

      <div class="input-box">
        <input
          type="password"
          name="confirmPassword"
          id="confirmPassword"
          placeholder="Confirm Password"
          minlength="8"
          required
        />
        <i
          class="bx bxs-lock-alt"
          onclick="togglePassword('confirmPassword', this)"
        ></i>
      </div>

      <button type="submit" name="resetPassword" class="btn">
        Reset Password
      </button>

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
</script>

</body>
</html>