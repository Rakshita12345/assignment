<?php
// All core functions for email verification, registration, unsubscription, and GitHub timeline

function generateVerificationCode() {
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

function registerEmail($email) {
    $file = __DIR__ . '/registered_emails.txt';
    $email = strtolower(trim($email));
    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!in_array($email, $emails)) {
        file_put_contents($file, $email . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}

function unsubscribeEmail($email) {
    $file = __DIR__ . '/registered_emails.txt';
    $email = strtolower(trim($email));
    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $emails = array_filter($emails, function($e) use ($email) {
        return strtolower(trim($e)) !== $email;
    });
    file_put_contents($file, implode(PHP_EOL, $emails) . PHP_EOL, LOCK_EX);
}

function sendVerificationEmail($email, $code, $type = 'register') {
    $subject = $type === 'register' ? 'Your Verification Code' : 'Confirm Unsubscription';
    $from = 'no-reply@example.com';
    $headers = "From: $from\r\nContent-Type: text/html; charset=UTF-8";
    if ($type === 'register') {
        $body = "<p>Your verification code is: <strong>$code</strong></p>";
    } else {
        $body = "<p>To confirm unsubscription, use this code: <strong>$code</strong></p>";
    }
    mail($email, $subject, $body, $headers);
}

function fetchGitHubTimeline() {
    $url = 'https://www.github.com/timeline';
    $opts = [
        "http" => [
            "method" => "GET",
            "header" => "User-Agent: PHP-GH-timeline/1.0\r\n"
        ]
    ];
    $context = stream_context_create($opts);
    $data = @file_get_contents($url, false, $context);
    return $data;
}

function formatGitHubData($data) {
    // For demo, fake parse: show as a table with dummy event/user if not JSON
    // In real, parse JSON and build table
    $rows = '';
    $json = json_decode($data, true);
    if (is_array($json) && isset($json['events'])) {
        foreach ($json['events'] as $event) {
            $etype = htmlspecialchars($event['type'] ?? 'Unknown');
            $user = htmlspecialchars($event['actor']['login'] ?? 'Unknown');
            $rows .= "<tr><td>$etype</td><td>$user</td></tr>";
        }
    } else {
        // fallback
        $rows = '<tr><td>Push</td><td>testuser</td></tr>';
    }
    return "<h2>GitHub Timeline Updates</h2><table border=\"1\"><tr><th>Event</th><th>User</th></tr>$rows</table>";
}

function sendGitHubUpdatesToSubscribers() {
    $file = __DIR__ . '/registered_emails.txt';
    if (!file_exists($file)) return;
    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!$emails) return;
    $data = fetchGitHubTimeline();
    $html = formatGitHubData($data);
    $from = 'no-reply@example.com';
    $headers = "From: $from\r\nContent-Type: text/html; charset=UTF-8";
    foreach ($emails as $email) {
        $unsubscribe_url = getUnsubscribeUrl($email);
        $body = $html . '<p><a href="' . htmlspecialchars($unsubscribe_url) . '" id="unsubscribe-button">Unsubscribe</a></p>';
        mail($email, 'Latest GitHub Updates', $body, $headers);
    }
}

function getUnsubscribeUrl($email) {
    $base = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    $path = dirname($_SERVER['PHP_SELF'] ?? '/src/index.php');
    $url = rtrim($base . $path, '/') . '/unsubscribe.php?email=' . urlencode($email);
    return $url;
} 
