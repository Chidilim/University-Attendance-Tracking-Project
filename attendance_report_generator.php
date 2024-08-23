<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Here top";
require_once 'dbconnection.php';
echo "Here top2";

// Function to generate attendance report
function generateAttendanceReport($courseCode) {
    global $conn;
    echo "Here top 3";
    
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
        $attendanceData[$sessionDate] = $sessionAttendance;
    }

    // Generate CSV content
    $csvContent = "Session Date,Student Number,First Name,Last Name,Word Typed\n";
    foreach ($attendanceData as $sessionDate => $sessionAttendance) {
        foreach ($sessionAttendance as $attendance) {
            $csvContent .= "$sessionDate,{$attendance['StudentNumber']},{$attendance['FirstName']},{$attendance['LastName']},{$attendance['WordTyped']}\n";
        }
    }

    // Output CSV content
    return $csvContent;
}

// Check if the generate attendance report button is pressed
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
}
?>
