<?php
session_start();  
require_once 'dbconnection.php'; 
$courseCode = isset($_GET['courseCode']) ? $_GET['courseCode'] : 'DefaultCode';
$yearClass = isset($_GET['yearClass']) ? $_GET['yearClass'] : date('Y');
$sessionWord = '';
$sessionTimer = 0;

if (isset($_POST['generateQR'])) {
    $courseCode = $_POST['courseCode'];
    $yearSession = $_POST['yearSession'];
    $sessionTimer = $_POST['sessionTimer']; // in minutes
    $sessionDate = date('Y-m-d H:i:s');

    // Get a random word from the `words` table
    $wordQuery = "SELECT * FROM words ORDER BY RAND() LIMIT 1";
    $wordResult = $conn->query($wordQuery);
    $wordRow = $wordResult->fetch_assoc();
    $wordID = $wordRow['WordID'];
    $sessionWord = $wordRow['Word'];

    // Insert the new session into the `session` table
    $sessionInsert = $conn->prepare("INSERT INTO session (CourseCode, YearSession, SessionDate, WordID) VALUES (?, ?, ?, ?)");
    $sessionInsert->bind_param("sssi", $courseCode, $yearSession, $sessionDate, $wordID);
    $sessionInsert->execute();
    $sessionInsert->close();

    // Now, we need to generate the QR code with this data...
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate QR Code</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat:400,500&display=swap">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            width: 100%;
            font-family: 'Montserrat', sans-serif;
            background-color: #c5f1ff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
            border: 10px solid #003366;
        }
        .container {
            text-align: center;
            padding: 20px;
            border-radius: 5px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
        }
        .input-group {
            margin: 20px 0;
            display: flex;
            justify-content: space-between;
        }
        .input-group > div{
            width: 48%;
        }
        .input-group > div:first-child input {
            max-width: 90%; /* Adjust width of course code input */
        }
        .input-group.single {
            flex-direction: column;
            align-items: center; /* Centers the session timer input */
        }
        label {
            display: block;
            text-align: left;
            margin-bottom: 5px;
        }
        input, select, button {
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #666;
            font-size: 16px;
            width: 100%;
        }
       
        button {
            background-color: #010562;
            color: white;
            cursor: pointer;
            margin-top: 20px;
            width: auto;
        }
        button:hover {
            background-color: #ff8e97;
        }
        #qrCode {
            margin-top: 20px;
            display: flex;
            justify-content: center;
        }
        .back-link a {
            color: #010562;
            text-decoration: none;
            font-size: 16px;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>QR Code Generator</h1>
        <form method="POST" action="qrcodegeneration.php">
            <div class="input-group">
                <div>
                    <label for="courseCode">Course Code:</label>
                    <input type="text" id="courseCode" name="courseCode" value="<?php echo htmlspecialchars($courseCode); ?>" readonly>
                </div>
                <div>
                    <label for="yearSession">Year Session:</label>
                    <select id="yearSession" name="yearSession" readonly>
                        <option value="<?php echo htmlspecialchars($yearClass); ?>"><?php echo htmlspecialchars($yearClass); ?></option>
                    </select>
                </div>
            </div>
            <div class="input-group single" style="flex-direction: column;">
                <label for="sessionTimer">Session Timer (minutes):</label>
                <input type="number" id="sessionTimer" name="sessionTimer" min="1" max="120">
            </div>
            <button type="submit" name="generateQR">Generate QR Code</button>
        </form>
        <div id="qrCode"></div>
        <p id="wordDisplay"></p>
        <div id="timerDisplay"></div>
    </div>
    <div class="back-link">
        <a href="professor_homepage.html">Back To Home</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/qrcodejs/qrcode.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const yearSessionSelect = document.getElementById('yearSession');
            for (let year = 2020; year <= 2050; year++) {
                let option = new Option(year.toString(), year);
                yearSessionSelect.appendChild(option);
            }
            <?php if (isset($sessionWord) && $sessionWord !== ''): ?>
            displayQRCode('<?php echo $sessionWord; ?>', '<?php echo $courseCode; ?>', <?php echo $sessionTimer * 60; ?>);
            <?php endif; ?>
        });


        function displayQRCode(word, courseCode, sessionTimer) {
            const qrData = `Course: ${courseCode}, Word: ${word}, Timer: ${sessionTimer / 60} minutes`;
            const qrCodeContainer = document.getElementById('qrCode');
            qrCodeContainer.innerHTML = ''; // Clear previous QR code
            new QRCode(qrCodeContainer, {
                text: qrData,
                width: 128,
                height: 128,
                colorDark : "#000000",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.H
            });

            const wordDisplay = document.getElementById('wordDisplay');
            wordDisplay.textContent = `Session Word: ${word}`;
            startTimer(sessionTimer);
        }

        function startTimer(duration) {
            let timer = duration, minutes, seconds;
            const timerDisplay = document.getElementById('timerDisplay');
            timerDisplay.textContent = ''; // Clear previous timer

            const countdown = setInterval(function() {
                minutes = parseInt(timer / 60, 10);
                seconds = parseInt(timer % 60, 10);

                timerDisplay.textContent = `Time Remaining: ${minutes}m ${seconds.toString().padStart(2, '0')}s`;

                if (--timer < 0) {
                    clearInterval(countdown);
                    timerDisplay.textContent = 'Session expired';
                    document.getElementById('qrCode').innerHTML = ''; // Clear the QR code
                    document.getElementById('wordDisplay').textContent = ''; // Clear the session word
                }
            }, 1000);
        }
        

    </script>


</body>
</html>
