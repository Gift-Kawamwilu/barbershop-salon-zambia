<?php
// php-files-ajax/contact.php - AJAX endpoint for the contact form
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../Includes/functions/functions.php';

$response = ['success' => false, 'message' => ''];

if (
    isset($_POST['contact_name']) &&
    isset($_POST['contact_email']) &&
    isset($_POST['contact_subject']) &&
    isset($_POST['contact_message'])
) {
    $contact_name    = sanitizeInput($_POST['contact_name']);
    $contact_email   = sanitizeInput($_POST['contact_email']);
    $contact_subject = sanitizeInput($_POST['contact_subject']);
    $contact_message = sanitizeInput($_POST['contact_message']);

    // Basic validation
    if (empty($contact_name) || empty($contact_email) || empty($contact_subject) || empty($contact_message)) {
        $response['message'] = 'Please fill in all fields.';
        echo json_encode($response);
        exit;
    }

    if (!filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Please enter a valid email address.';
        echo json_encode($response);
        exit;
    }

    $to = 'giftkawamwilu@example.com'; // TODO: replace with your real address
    $subject = $contact_subject;
    $body = "Name: $contact_name\nEmail: $contact_email\n\n$contact_message";
    $headers = "From: $contact_email\r\nReply-To: $contact_email\r\n";

    // mail() returns a boolean, it does NOT throw exceptions, so check the return value directly
    $sent = @mail($to, $subject, $body, $headers);

    if ($sent) {
        $response['success'] = true;
        $response['message'] = 'Your message has been sent successfully.';
    } else {
        $response['message'] = 'A problem occurred while sending your message. Please try again.';
    }
} else {
    $response['message'] = 'Missing required fields.';
}

echo json_encode($response);
