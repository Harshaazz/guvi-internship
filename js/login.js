// js/login.js
// Replace your entire file with this code.

$(document).ready(function () {
    $('#loginForm').on('submit', function (e) {
        e.preventDefault();

        // Use name attributes instead of IDs to avoid "undefined" errors
        const email = $('input[name="email"]').val().trim();
        const password = $('input[name="password"]').val();

        if (!email || !password) {
            alert('Please enter email and password.');
            return;
        }

        const $btn = $('#loginBtn');
        $btn.prop('disabled', true).text('Logging in...');

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
                console.log('Login response:', response);

                if (response.status === 'success') {
                    // Save token in localStorage
                    localStorage.setItem('token', response.token);
                    localStorage.setItem('session_token', response.token);

                    // Save user safely
                    if (response.user) {
                        localStorage.setItem(
                            'user',
                            JSON.stringify(response.user)
                        );
                    }

                    // Redirect to profile page
                    window.location.href = 'profile.html';
                } else {
                    alert(response.message || 'Login failed. Please try again.');
                }
            },

            error: function (xhr) {
                console.error('AJAX Error:', xhr.responseText);
                alert('Server error occurred during login.');
            },

            complete: function () {
                $btn.prop('disabled', false).text('Login');
            }
        });
    });
});