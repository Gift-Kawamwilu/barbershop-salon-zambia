<?php
// contact-us.php - Contact page with AJAX-powered form submission
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Barbershop & Salon</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .contact-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 480px;
        }
        h1 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
            font-size: 24px;
        }
        .form-group {
            margin-bottom: 18px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #555;
            font-size: 14px;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            font-family: inherit;
            transition: border-color 0.3s;
        }
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        .btn-send {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, opacity 0.2s;
        }
        .btn-send:hover:not(:disabled) {
            transform: scale(1.02);
        }
        .btn-send:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .form-message {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 18px;
            text-align: center;
            font-size: 14px;
            display: none;
        }
        .form-message.success {
            background: #d4edda;
            color: #155724;
            display: block;
        }
        .form-message.error {
            background: #f8d7da;
            color: #721c24;
            display: block;
        }
        .field-error {
            color: #c0392b;
            font-size: 12px;
            margin-top: 4px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="contact-container">
        <h1>Get in Touch</h1>

        <div id="formMessage" class="form-message"></div>

        <form id="contactForm" novalidate>
            <div class="form-group">
                <label for="contact_name">Name</label>
                <input type="text" id="contact_name" name="contact_name" required>
                <div class="field-error" id="err_name">Please enter your name.</div>
            </div>
            <div class="form-group">
                <label for="contact_email">Email</label>
                <input type="email" id="contact_email" name="contact_email" required>
                <div class="field-error" id="err_email">Please enter a valid email address.</div>
            </div>
            <div class="form-group">
                <label for="contact_subject">Subject</label>
                <input type="text" id="contact_subject" name="contact_subject" required>
                <div class="field-error" id="err_subject">Please enter a subject.</div>
            </div>
            <div class="form-group">
                <label for="contact_message">Message</label>
                <textarea id="contact_message" name="contact_message" required></textarea>
                <div class="field-error" id="err_message">Please enter a message.</div>
            </div>
            <button type="submit" class="btn-send" id="submitBtn">Send Message</button>
        </form>
    </div>

    <script>
        const form = document.getElementById('contactForm');
        const submitBtn = document.getElementById('submitBtn');
        const formMessage = document.getElementById('formMessage');

        function showFieldError(id, show) {
            document.getElementById(id).style.display = show ? 'block' : 'none';
        }

        function validateForm() {
            let valid = true;

            const name = document.getElementById('contact_name').value.trim();
            const email = document.getElementById('contact_email').value.trim();
            const subject = document.getElementById('contact_subject').value.trim();
            const message = document.getElementById('contact_message').value.trim();

            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            showFieldError('err_name', name === '');
            if (name === '') valid = false;

            showFieldError('err_email', email === '' || !emailPattern.test(email));
            if (email === '' || !emailPattern.test(email)) valid = false;

            showFieldError('err_subject', subject === '');
            if (subject === '') valid = false;

            showFieldError('err_message', message === '');
            if (message === '') valid = false;

            return valid;
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            formMessage.className = 'form-message';
            formMessage.textContent = '';

            if (!validateForm()) {
                return;
            }

            submitBtn.disabled = true;
            submitBtn.textContent = 'Sending...';

            const formData = new FormData(form);

            fetch('php-files-ajax/contact.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    formMessage.textContent = data.message;
                    formMessage.className = 'form-message ' + (data.success ? 'success' : 'error');

                    if (data.success) {
                        form.reset();
                    }
                })
                .catch(() => {
                    formMessage.textContent = 'Something went wrong. Please try again.';
                    formMessage.className = 'form-message error';
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Send Message';
                });
        });
    </script>
</body>
</html>
