<?php
// Include your database connection script
require_once 'dbconnection.php';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $employeeNum = $_POST['employeeid'];
    $firstName = $_POST['firstname'];
    $lastName = $_POST['lastname'];
    $email = $_POST['email'];
    $password = $_POST['password']; 

    // Basic input validation (consider more robust validation)
    if (empty($employeeNum) ||empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
        die("Please fill all required fields!");
    }

    // Prepare an insert statement
    $sql = "INSERT INTO Professor (EmployeeNum, FirstName, LastName, Email, userPassword) VALUES (?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("issss", $param_employeeNum, $param_firstName, $param_lastName, $param_email, $param_password);

        // Set parameters and hash the password
        $param_employeeNum = $employeeNum;
        $param_firstName = $firstName;
        $param_lastName = $lastName;
        $param_email = $email;
        $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash

        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            echo "Professor registered successfully.";
            // Redirect to login page or somewhere else after registration
        } else {
            echo "Something went wrong. Please try again later.";
        }

        // Close statement
        $stmt->close();
    }
}

// Close connection
$conn->close();
?>
