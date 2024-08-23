<?php

session_start(); // Start a new session



// Include your database connection script

require_once 'dbconnection.php';



// Check if the form is submitted

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Retrieve form data

    $email = $_POST['email'];

    $password = $_POST['password'];



    // Prepare a select statement

    $sql = "SELECT EmployeeNum, FirstName, LastName, Email, userPassword FROM Professor WHERE Email = ?";



    if ($stmt = $conn->prepare($sql)) {

        // Bind variables to the prepared statement as parameters

        $stmt->bind_param("s", $param_email);



        // Set parameters

        $param_email = $email;



        // Attempt to execute the prepared statement

        if ($stmt->execute()) {

            // Store result

            $stmt->store_result();



            // Check if email exists, if yes then verify password

            if ($stmt->num_rows == 1) {

                // Bind result variables

                $stmt->bind_result($id, $firstName, $lastName, $email, $hashed_password);

                if ($stmt->fetch()) {

                    if (password_verify($password, $hashed_password)) {

                        // Password is correct, start a new session

                        session_regenerate_id();

                        $_SESSION['loggedin'] = true;

                        $id = (int)$id;

                        $_SESSION['id'] = $id;

                        $_SESSION['lname'] = $lastName;

                        $_SESSION['email'] = $email; // Store email in session variable



                        // Redirect professor to homepage

                        header("location: profHomepagetest.php");

                        exit;

                    } else {

                        // Display an error message if password is not valid

                        echo "The password you entered was not valid.";

                    }

                }

            } else {

                // Display an error message if email doesn't exist

                echo "No account found with that email.";

            }

        } else {

            echo "Oops! Something went wrong. Please try again later.";

        }



        // Close statement

        $stmt->close();

    }

}



// Close connection

// $conn->close();

?>