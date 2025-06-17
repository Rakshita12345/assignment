<?php
require_once __DIR__ . '/functions.php';
session_start();

$message = '';

if (isset($_POST['unsubscribe_email'])) {
    $email = trim($_POST['unsubscribe_email']);
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $code = generateVerificationCode();
        $_SESSION['unsubscribe_email'] = $email;
        $_SESSION['unsubscribe_code'] = $code;
        sendVerificationEmail($email, $code, 'unsubscribe');
        $message = 'Unsubscribe verification code sent to your email.';
    } else {
        $message = 'Invalid email address.';
    }
}

if (isset($_POST['unsubscribe_verification_code'])) {
    $input_code = trim($_POST['unsubscribe_verification_code']);
    if (isset($_SESSION['unsubscribe_code'], $_SESSION['unsubscribe_email']) && $input_code === $_SESSION['unsubscribe_code']) {
        unsubscribeEmail($_SESSION['unsubscribe_email']);
        $message = 'You have been unsubscribed.';
        unset($_SESSION['unsubscribe_code'], $_SESSION['unsubscribe_email']);
    } else {
        $message = 'Invalid verification code.';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Unsubscribe from GH-timeline</title>
</head>
<body>
    <h2>Unsubscribe from GitHub Timeline Updates</h2>
    <?php if ($message) echo '<p>' . htmlspecialchars($message) . '</p>'; ?>
    <form method="post">
        <input type="email" name="unsubscribe_email" required>
        <button id="submit-unsubscribe">Unsubscribe</button>
    </form>
    <form method="post">
        <input type="text" name="unsubscribe_verification_code">
        <button id="verify-unsubscribe">Verify</button>
    </form>
</body>
</html> 
