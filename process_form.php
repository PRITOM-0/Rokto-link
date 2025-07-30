<?php
// process_form.php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $message = htmlspecialchars($_POST['message']);

    // In a real application, you would save this to a database, send an email, etc.
    // For this example, we'll just acknowledge receipt.

    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Message Sent</title>
        <link rel='stylesheet' href='style.css'>
        <link href='https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap' rel='stylesheet'>
    </head>
    <body class='font-inter message-body'>
        <div class='message-container'>
            <h2 class='message-title success-color'>Thank You!</h2>
            <p class='message-text'>Your message has been received, " . $name . ".</p>
            <p class='message-info'>We will get back to you shortly at " . $email . ".</p>
            <a href='index.php' class='btn btn-primary'>Go back to Home</a>
        </div>
    </body>
    </html>";
} else {
    // If accessed directly without POST data, redirect to home
    header("Location: index.php");
    exit;
}
?>
