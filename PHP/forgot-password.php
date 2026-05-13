<?php
include 'connect.php';

$message = "";

if(isset($_POST['resetPassword'])){

    $email = mysqli_real_escape_string($conn, $_POST['email']);

    $newPassword = $_POST['newPassword'];

    $confirmPassword = $_POST['confirmPassword'];

    // CHECK PASSWORD MATCH
    if($newPassword !== $confirmPassword){

        $message = "Passwords do not match!";

    } else {

        // CHECK EMAIL
        $checkEmail = "
            SELECT * FROM users
            WHERE email='$email'
        ";

        $result = $conn->query($checkEmail);

        if($result->num_rows > 0){

            // ENCRYPT PASSWORD
            $hashedPassword = md5($newPassword);

            // UPDATE PASSWORD
            $updateQuery = "
                UPDATE users
                SET password='$hashedPassword'
                WHERE email='$email'
            ";

            if($conn->query($updateQuery)){

                $message = "Password updated successfully!";

            } else {

                $message = "Failed to update password!";
            }

        } else {

            $message = "Email not found!";
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

body{
  display:flex;
  justify-content:center;
  align-items:center;
  min-height:100vh;
}

.forgot-box{
  width:420px;
}

.message{
  text-align:center;
  margin-bottom:15px;
  color:#fff;
}

.back-login{
  text-align:center;
  margin-top:15px;
}

.back-login a{
  color:#fff;
  text-decoration:none;
}

.back-login a:hover{
  color:#d89b56;
}

</style>
</head>

<body>

<div class="container forgot-box">

  <div class="form-box login">

    <form method="POST">

      <h1>Forgot Password</h1>

      <?php if($message != ""){ ?>

        <div class="message">
          <?php echo $message; ?>
        </div>

      <?php } ?>

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
          required
        />

        <i
          class="bx bxs-lock-alt"
          onclick="togglePassword('confirmPassword', this)"
        ></i>
      </div>

      <button
        type="submit"
        name="resetPassword"
        class="btn"
      >
        Reset Password
      </button>

      <div class="back-login">
        <a href="../HTML/login.html">
          Back to Login
        </a>
      </div>

    </form>
  </div>
</div>

<script>
function togglePassword(inputId, icon){

    const inp = document.getElementById(inputId);

    const isHidden = inp.type === "password";

    inp.type = isHidden ? "text" : "password";

    icon.className = isHidden
      ? "bx bxs-lock-open-alt"
      : "bx bxs-lock-alt";
}
</script>

</body>
</html>