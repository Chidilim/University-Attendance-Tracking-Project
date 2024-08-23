<?php
session_start(); // Start session if not already started


// Database configuration
require_once 'dbconnection.php';

// Function to add a new class
function addNewClass($CourseCode, $CourseName, $YearClass, $ProfessorID) {
    // Assuming $conn is your database connection
    global $conn;
    $stmt = $conn->prepare("INSERT INTO Courses (CourseCode, CourseName, YearClass, ProfessorID) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssii", $CourseCode, $CourseName, $YearClass, $ProfessorID);
    $stmt->execute();
    $stmt->close();
}

// Check if the form is submitted
if(isset($_POST['add_class'])) {
    // Retrieve form data
    $CourseCode = $_POST['CourseCode'];
    $CourseName = $_POST['CourseName'];
    $YearClass = $_POST['YearClass'];

    // Retrieve ProfessorID from session
    $ProfessorID = $_SESSION['id'];

    // Call addNewClass function
    addNewClass($CourseCode, $CourseName, $YearClass, $ProfessorID);
}



function generateAttendanceReport($courseCode) {
    global $conn;

    // Fetch sessions for the given course code
    $stmt = $conn->prepare("SELECT SessionID, SessionDate FROM sessionData WHERE CourseCode = ?");
    $stmt->bind_param("s", $courseCode);
    $stmt->execute();
    $result = $stmt->get_result();

    // Initialize an array to store attendance data
    $attendanceData = [];

    // Loop through sessions
    while ($row = $result->fetch_assoc()) {
        $sessionID = $row['SessionID'];
        $sessionDate = $row['SessionDate'];

        // Fetch attendance data for each session
        $stmt_attendance = $conn->prepare("SELECT CourseStudent.StudentNumber, CourseStudent.FirstName, CourseStudent.LastName, Attendance.WordTyped
                                FROM Attendance
                                INNER JOIN CourseStudent ON Attendance.StudentNumber = CourseStudent.StudentNumber
                                WHERE SessionID = ?");
        $stmt_attendance->bind_param("i", $sessionID);
        $stmt_attendance->execute();
        $attendanceResult = $stmt_attendance->get_result();

        // Initialize an array to store attendance details for this session
        $sessionAttendance = [];

        // Loop through attendance data for this session
        while ($attendanceRow = $attendanceResult->fetch_assoc()) {
            $sessionAttendance[] = $attendanceRow;
        }

        // Store attendance data for this session in the main array
        $attendanceData[] = array(
            'sessionDate' => $sessionDate,
            'attendance' => $sessionAttendance
        );
    }

    // Generate CSV content
    $csvContent = "Session Date,Student Number,First Name,Last Name,Word Typed\n";
    foreach ($attendanceData as $sessionData) {
        $sessionDate = $sessionData['sessionDate'];
        $sessionAttendance = $sessionData['attendance'];
        foreach ($sessionAttendance as $attendance) {
            // Properly escape CSV values
            $csvContent .= "$sessionDate,{$attendance['StudentNumber']},{$attendance['FirstName']},{$attendance['LastName']},{$attendance['WordTyped']}\n";
        }
    }

    // Output CSV content
    return $csvContent;
}


// Check if the form is submitted to generate attendance report
if (isset($_POST['generate_report'])) {
    // Retrieve the course code from the form
    $courseCode = $_POST['course_code'];

    // Generate the attendance report
    $attendanceReport = generateAttendanceReport($courseCode);

    // Output CSV headers
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="attendance_report.csv"');

    // Output the attendance report content
    echo $attendanceReport;
    exit();
}

//Function to get all Professor classes 
function getClassesByProfessor($professorID) {
    global $conn;
    $stmt = $conn->prepare("SELECT CourseCode, CourseName, YearClass FROM Courses WHERE ProfessorID = ?");
    $stmt->bind_param("i", $professorID);
    $stmt->execute();
    $result = $stmt->get_result();
    $classes = [];
    while ($row = $result->fetch_assoc()) {
        $classes[] = $row;
    }
    return $classes;
}

// Verify login credentials
if(isset($_POST['login'])) {
    $employeeID = $_POST['employeeID'];
    $password = $_POST['password'];

    // Query to check if credentials are correct
    $stmt = $conn->prepare("SELECT * FROM Users WHERE UserID = ?");
    $stmt->bind_param("s", $employeeID);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        if(password_verify($password, $row['userPassword'])) {
            // Password is correct, store employeeID in session
            $_SESSION['employeeID'] = $employeeID;
        } else {
            // Password is incorrect, print error message
            echo "<script>alert('Incorrect password. Please try again.');</script>";
        }
    } else {
        // No user found with the given employeeID
        echo "<script>alert('Invalid employee ID. Please try again.');</script>";
    }
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professor Dashboard</title>
    <style>/* Reset some default styles */

* {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body, html {
            height: 100%;
            font-family: 'Arial', sans-serif;
            background-color: #f2f2f2; /* Light grey background for the page */
        }

        .dashboard-container {
            display: flex;
            height: 100%;
        }

        .sidebar {
            background-color: #010562; /* Dark blue for the sidebar */
            color: #c5f1ff; /* Light blue for sidebar text */
            padding: 20px;
            width: 250px;
        }

        .sidebar h1 {
            margin-bottom: 20px;
        }

        .navigation ul {
            list-style-type: none;
        }

        .navigation li a {
            color: #c5f1ff; /* Light blue for navigation links */
            text-decoration: none;
            padding: 10px 0;
            display: block;
        }

        .main-content {
            flex-grow: 1;
            padding: 20px;
            background-color: #f2f2f2; /* Light grey for main content background */
        }

        .courses {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            background: #d9d9d9; /* Slightly darker grey for courses background */
            padding: 40px 20px 20px; 
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .courses h2 {
            margin-bottom: 50px; 
          
            order: -1; 

        }

        .course-item {
            padding: 15px;
            margin-bottom: 20px;
            background-color: #f2f2f2; /* Light grey for course items */
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            flex: 1; 
            margin-right: 20px; 
            min-width: 280px; 
            max-width: calc(50% - 20px); 
        }

        .course-item:last-child {
            margin-right: 0;
        }

        button {
            background-color: #06b0f0; /* Bright blue for buttons */
            color: #fff;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%; 
            margin-bottom: 10px; 
        }

        button:hover {
            opacity: 0.8;
        }

        .add-course-form {
            background-color: #d9d9d9; /* Slightly darker grey for form background */
            padding: 20px;
            border-radius: 8px; 
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); 
            max-width: 500px; 
            margin: auto;
        }

        .add-course-form input[type="text"],
        .add-course-form input[type="number"] {
            width: calc(100% - 20px);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #06b0f0; /* Bright blue for input borders */
            border-radius: 5px;
            box-sizing: border-box;
        }

        .add-course-form input::placeholder {
            color: #7f8c8d;
        }

        .add-course-form button {
            width: 100%;
            padding: 10px 15px;
            border: none;
            background-color: #010562; /* Dark blue for form button */
            color: white;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }

        .add-course-form button:hover {
            background-color: #06b0f0; /* Bright blue for button hover */
            opacity: 0.9;
        }

 </style><!-- Assuming you have an external CSS file -->
</head>
<body>
<div class="dashboard-container">
    <aside class="sidebar">
        <h1>PROFESSOR DASHBOARD</h1>
        <nav class="navigation">
            <ul>
                <li><a href="homepage.html">Home</a></li>
                <li><a href="logout.php">Log out</a></li>
            </ul>
        </nav>
    </aside>
    <main class="main-content">
        <section class="courses">
            <h2>My Courses</h2>
            <?php
            if (isset($_SESSION['id'])) {
                $professorID = $_SESSION['id'];
                $classes = getClassesByProfessor($professorID);
                foreach ($classes as $class) {
                    echo "<div class='course-item'>";
                    echo "<div class='class-name'>{$class['CourseName']}</div>";
                    echo "<div class='class-time'>{$class['YearClass']}</div>";
                    echo "<div class='class-average-attendance'>Course Code: {$class['CourseCode']}</div>";
                    echo "<div class='class-item-actions'>";
                    echo "<button onclick='generateQRCode(\"{$class['CourseCode']}\", \"{$class['YearClass']}\")'>Generate QR Code</button>";
                    echo "<button onclick='generateattendancereport(\"{$class['CourseCode']}\")'>Generate Attendance Report</button>";
                    echo "</div>";
                    echo "</div>";
                }
            }
            ?>
            <!-- The "Add New Class" form -->
            <form action="" method="post" class="add-course-form">
                <input type="text" name="CourseCode" placeholder="Course Code">
                <input type="text" name="CourseName" placeholder="Course Name">
                <input type="number" name="YearClass" placeholder="Year The Class Is Being Taken">
                <button type="submit" name="add_class">Add New Class</button>
            </form>
        </section>
    </main>
</div>
<script>
        // Show the blurred background and verification pop-up on page load
        window.onload = function() {
            document.getElementById('blur-background').style.display = 'block';
            document.getElementById('verification-popup').style.display = 'block';
        };

        // Function to generate a QR code for the class
        function generateQRCode(courseCode, yearClass) {
            window.location.href = 'qrcodegeneration.php?courseCode=' + courseCode + '&yearClass=' + yearClass;
        }

    function generateattendancereport(courseCode) {
    // Call the PHP function directly using AJAX
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '<?php echo $_SERVER['PHP_SELF']; ?>', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    // Prepare the data to send
    var data = 'generate_report=true&course_code=' + encodeURIComponent(courseCode);

    // Set up callback functions
    xhr.onreadystatechange = function () {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                // Create a hidden link and trigger the download
                var hiddenElement = document.createElement('a');
                hiddenElement.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(xhr.responseText);
                hiddenElement.target = '_blank';
                hiddenElement.download = 'attendance_report.csv';
                hiddenElement.click();
            } else {
                // Handle error
                console.error('Failed to generate attendance report. Status:', xhr.status);
            }
        }
    };

    // Send the request
    xhr.send(data);
}


    </script>
</body>
</html>


<!-- To Do Now

- See if you can make the page a bit pretteier
