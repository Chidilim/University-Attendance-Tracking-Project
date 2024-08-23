<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta viewport="width=device-width, initial-scale=1.0">
    <title>Classroom Sign-In</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat">
    <link rel="stylesheet" href="reset_pass_css.css">
</head>
<body>
    <div class="reset-container">
        <img src="./mascot_tp.png" alt="illustration" class="main-image">
        <form action="" method="post">
            <div class="input-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="input-group">
                <label for="password">New Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="input-group">
                <label for="confirmPassword">Confirm Password</label>
                <input type="password" id="confirmPassword" name="confirmPassword" required>
            </div>
            <button type="submit" name="submit">Save Changes</button>
        </form>

        <?php 
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            include_once "dbconnection.php";

            // Retrieve form data
            $email = $_POST["email"];
            $password = $_POST["password"];
            $confirmPassword = $_POST["confirmPassword"];

            // Perform validation
            if ($password != $confirmPassword) {
                echo "<div style='color: red;'>Passwords do not match.</div>";
                exit(); // Stop execution if passwords don't match
            }

            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT); //using bcrypt which is the default hash algorithm
           
            
            // Prepare and execute a query to update the password based on email
            $query = "UPDATE Professor SET userPassword = ? WHERE Email = ? ";

            $stmt = $conn->prepare($query);
            $stmt->bind_param("ss", $hashedPassword, $email);
            $stmt->execute();

            // Check if the update was successful
            if ($stmt->affected_rows> 0) {
                echo "<div style='color: green;'>Password updated successfully.</div>";
                
            } else {
            
                echo "<div style='color: red;'>Error updating password. Please try again.</div>";
            }

            // Close the prepared statement
            $stmt->close();
        }
        ?>

    </div>
    <a href="signin.html" class="home-link">Go Back To Login in</a>  

</body>
</html>
