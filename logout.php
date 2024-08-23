<?php

session_start();

//Empty all of the session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Redirect to the sign-in page (replace 'signin.html' with the actual filename)
header("Location: signin.html");
exit;

