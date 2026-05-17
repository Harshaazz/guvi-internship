// js/login.js

$(document).ready(function () {
    // Handle form submission
    $('#loginForm').on('submit', function (e) {
        e.preventDefault();

        // Clear old messages
        $('#loginMessage').html('');

        // Get form values
        const email = $('#email').val().trim();
        const password = $('#password').val();

        // Validate inputs
        if (email === '' || password === '') {
            $('#loginMessage').html(
                '<div class="alert alert-danger">Email and password are required.</div>'
            );
            return;
        }

        // Disable button while processing
        $('#loginBtn')
            .prop('disabled', true)
            .text('Logging in...');

        // Send AJAX request
        $.ajax({
            url: 'php/login.php',
            type: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({
                email: email,
                password: password
            }),

            success: function (response) {
                console.log('Login Response:', response);

                // Successful login
                if (response.status === 'success' && response.token) {
                    // Store token using BOTH keys for compatibility
                    localStorage.setItem('token', response.token);
                    localStorage.setItem('session_token', response.token);

                    // Store user data
                    if (response.user) {
                        localStorage.setItem(
                            'user',
                            JSON.stringify(response.user)
                        );

                        localStorage.setItem(
                            'username',
                            response.user.username || ''
                        );

                        localStorage.setItem(
                            'email',
                            response.user.email || ''
                        );
                    } else {
                        // Fallback if API returns username/email directly
                        localStorage.setItem(
                            'username',
                            response.username || ''
                        );

                        localStorage.setItem(
                            'email',
                            response.email || ''
                        );
                    }

                    // Show success message
                    $('#loginMessage').html(
                        '<div class="alert alert-success">Login successful. Redirecting...</div>'
                    );

                    // Redirect after short delay
                    setTimeout(function () {
                        window.location.href = 'profile.html';
                    }, 500);
                } else {
                    // Show backend error
                    $('#loginMessage').html(
                        '<div class="alert alert-danger">' +
                        (response.message || 'Login failed.') +
                        '</div>'
                    );
                }
            },

            error: function (xhr) {
                console.error('Login Error:', xhr.responseText);

                let message = 'Server error occurred.';

                try {
                    const errorResponse = JSON.parse(xhr.responseText);
                    if (errorResponse.message) {
                        message = errorResponse.message;
                    }
                } catch (e) {
                    // Ignore parse errors
                }

                $('#loginMessage').html(
                    '<div class="alert alert-danger">' +
                    message +
                    '</div>'
                );
            },

            complete: function () {
                // Re-enable button
                $('#loginBtn')
                    .prop('disabled', false)
                    .text('Login');
            }
        });
    });
});