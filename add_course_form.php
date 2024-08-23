<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $courseName = $_POST["courseName"];
    $attendance = $_POST["attendance"];

    // Process the data (you can perform database operations here)
    // Here update echo state to say that Course Name ELEC X has been successful added
    // Here we also need to check and amke sure that course does not already exist, if it does then we will say this course already exsist. 
    //Couldn't add new course. Then Automatically close the form. 

    echo "Course Name: " . $courseName . "<br>";
    echo "Attendance: " . $attendance . "%";
}
?>
