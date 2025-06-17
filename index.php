<?php
require_once __DIR__ . '/functions.php';
session_start();

$message = '';

if (isset($_POST['email'])) {
    $email = trim($_POST['email']);
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $code = generateVerificationCode();
        $_SESSION['register_email'] = $email;
        $_SESSION['register_code'] = $code;
        sendVerificationEmail($email, $code, 'register');
        $message = 'Verification code sent to your email.';
    } else {
        $message = 'Invalid email address.';
    }
}

if (isset($_POST['verification_code'])) {
    $input_code = trim($_POST['verification_code']);
    if (isset($_SESSION['register_code'], $_SESSION['register_email']) && $input_code === $_SESSION['register_code']) {
        registerEmail($_SESSION['register_email']);
        $message = 'Email verified and registered!';
        unset($_SESSION['register_code'], $_SESSION['register_email']);
    } else {
        $message = 'Invalid verification code.';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>GH-timeline Registration</title>
</head>
<body>
    <h2>Register for GitHub Timeline Updates</h2>
    <?php if ($message) echo '<p>' . htmlspecialchars($message) . '</p>'; ?>
    <form method="post">
        <input type="email" name="email" required>
        <button id="submit-email">Submit</button>
    </form>
    <form method="post">
        <input type="text" name="verification_code" maxlength="6" required>
        <button id="submit-verification">Verify</button>
    </form>
</body>
</html> 
