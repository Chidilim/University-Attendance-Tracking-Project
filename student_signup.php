<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classroom Sign-Up</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat">
    <link rel="stylesheet" href="student_signup.css" />
</head>

<body>
    <div class="container">
        <img src="white_trackmate.png" alt="Robot Mascot" class="mascot">
        <div class="content">
            <h1>Student Attendance Sign-In</h1>
            <p>Please fill in the fields below within 60 seconds. If you correctly type the text below the QR code, your attendance will be counted as present.</p>
            <div class="signup-container">
                <form action="" method="post">
                    <div class="input-group">
                        <label for="firstname">First Name:</label>
                        <input type="text" id="firstname" name="firstname" required>
                    </div>
                    <div class="input-group">
                        <label for="lastname">Last Name:</label>
                        <input type="text" id="lastname" name="lastname" required>
                    </div>
                    <div class="input-group">
                        <label for="email">Student Number:</label>
                        <input id="email" name="email" required>
                    </div>
                    <div class="input-group">
                        <label for="answer">Type Text Below the QR Code:</label>
                        <input type="text" id="answer" name="answer" required>
                    </div>
                    <button type="submit">Submit My Answer</button>
                </form>

                <?php
                    include 'dbconnection.php';

                    if ($_SERVER["REQUEST_METHOD"] == "POST") {
                        $firstname = mysqli_real_escape_string($conn, $_POST['firstname']);
                        $lastname = mysqli_real_escape_string($conn, $_POST['lastname']);
                        $email = mysqli_real_escape_string($conn, $_POST['email']);
                        $answer = mysqli_real_escape_string($conn, $_POST['answer']);

                        // Get the most recent session
                        $sql = "SELECT * FROM sessionData ORDER BY sessionDate DESC LIMIT 1";
                        $result = mysqli_query($conn, $sql);

                        if ($result && mysqli_num_rows($result) > 0) {
                            $row = mysqli_fetch_assoc($result);
                            $wordID = $row['WordID'];

                            // Get the correct word from the Words table
                            $sql = "SELECT * FROM Words WHERE WordID = $wordID";
                            $result = mysqli_query($conn, $sql);

                            if ($result && mysqli_num_rows($result) > 0) {
                                $row = mysqli_fetch_assoc($result);
                                $correctWord = $row['Word'];

                                // Check if the typed answer matches the correct word
                                if ($answer == $correctWord) {
                                    // Check if the student exists in the CourseStudent table
                                    $sql = "SELECT * FROM CourseStudent WHERE StudentNumber = '$email'";
                                    $result = mysqli_query($conn, $sql);

                                    if ($result && mysqli_num_rows($result) == 0) {
                                        // If the student does not exist, insert a new record
                                        $sql = "INSERT INTO CourseStudent (StudentNumber, FirstName, LastName, AttendedClasses) VALUES ('$email', '$firstname', '$lastname', 1)";
                                        mysqli_query($conn, $sql);
                                    } else {
                                        // If the student exists, update the attended classes count
                                        $sql = "UPDATE CourseStudent SET AttendedClasses = AttendedClasses + 1 WHERE StudentNumber = '$email'";
                                        mysqli_query($conn, $sql);
                                    }
                                } else {
                                    // Incorrect answer handling (optional)
                                    //echo "Incorrect answer provided.";
                                }

                                //$attendedClasses = $row['AttendedClasses'];
                                //$_SESSION['attendedClasses'] = $attendedClasses;
                                //header('Location: candy.php?attendedClasses='.$attendedClasses);
                            } else {
                                // Error handling for fetching correct word
                                echo "Error fetching correct word.";
                            }
                        } else {
                            // Error handling for fetching session
                            echo "Error fetching session data.";
                        }

                        header('Location:'.'candy.php');
                    }

                    
                ?>

            </div>
        </div>
    </div>
</body>
</html>