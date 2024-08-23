<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Trackmate</title>
    <link rel="stylesheet" href="candy.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat">
</head>
<body>
    <h1 class="title">Thanks for attending class, you earn a CANDY! Pick a trackmate to give candy to:</h1>
    <div id="trackmates-container">
        <button id="blue-trackmate" class="trackmate" onclick="redirectThankYou()">
            <img src="blue_trackmate.png" alt="bluey-pic">
            <p>bluey</p>
        </button>
        <button id="white-trackmate" class="trackmate" onclick="redirectThankYou()">
            <img src="white_trackmate.png" alt="white-pic">
            <p>white</p>
        </button>
        <button id="purple-trackmate" class="trackmate" onclick="redirectThankYou()">
            <img src="purple_trackmate.png" alt="purple-pic">
            <p>purple</p>
        </button>
        <button id="yellow-trackmate" class="trackmate" onclick="redirectThankYou()">
            <img src="yellow_trackmate.png" alt="yellow-pic">
            <p>yellow</p>
        </button>
        <button id="green-trackmate" class="trackmate" onclick="redirectThankYou()">
            <img src="green_trackmate.png" alt="green-pic">
            <p>green</p>
        </button>
        <button id="red-trackmate" class="trackmate" onclick="redirectThankYou()">
            <img src="red_trackmate.png" alt="red-pic">
            <p>red</p>
        </button>

        <canvas id="confetti-canvas"></canvas>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.0.1"></script>
    <script>
        // Function to create confetti effect
        function createConfetti() {
            var duration = 2.5 * 1000; // 10 seconds
            var animationEnd = Date.now() + duration;
            var defaults = { startVelocity: 20, spread: 360, ticks: 60, zIndex: 0 };

            function randomInRange(min, max) {
                return Math.random() * (max - min) + min;
            }

            var interval = setInterval(function() {
                var timeLeft = animationEnd - Date.now();

                if (timeLeft <= 0) {
                    return clearInterval(interval);
                }

                var particleCount = 100 * (timeLeft / duration);
                // Since particles fall down, start a bit higher than random
                confetti({
                    ...defaults,
                    particleCount,
                    origin: { x: randomInRange(0.1, 0.9), y: Math.random() - 0.2 }
                });
                confetti({
                    ...defaults,
                    particleCount,
                    origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 }
                });
            }, 250);
        }

        // Call the confetti function when the page loads
        window.onload = createConfetti;

        function redirectThankYou() {
            // Redirect to the thank you page
            window.location.href = "thankyou.html";
        }
    </script>
</body>
</html>
