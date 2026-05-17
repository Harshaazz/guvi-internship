// js/login.js
// Fix for: Cannot read properties of undefined (reading 'trim')

$(document).ready(function () {
    $('#loginForm').on('submit', function (e) {
        e.preventDefault();

        // Use name selectors (works even if id attributes are missing)
        const email = $('input[name="email"]').val();
        const password = $('input[name="password"]').val();

        // Safe trim
        const cleanEmail = email ? email.trim() : '';
        const cleanPassword = password ? password : '';

        if (cleanEmail === '' || cleanPassword === '') {
            alert('Email and password are required.');
            return;
        }

        $('#loginBtn')
            .prop('disabled', true)
            .text('Logging in...');

        $.ajax({
            url: 'php/login.php',
            type: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({
                email: cleanEmail,
                password: cleanPassword
            }),

            success: function (response) {
                console.log('Login Response:', response);

                if (response.status === 'success' && response.token) {
                    // Store token
                    localStorage.setItem('token', response.token);
                    localStorage.setItem('session_token', response.token);

                    // Store user info
                    if (response.user) {
                        localStorage.setItem(
                            'user',
                            JSON.stringify(response.user)
                        );
                    }

                    // Redirect to profile page
                    window.location.href = 'profile.html';
                } else {
                    alert(response.message || 'Login failed.');
                }
            },

            error: function (xhr) {
                console.log('Server Response:', xhr.responseText);
                alert('Login failed. Please try again.');
            },

            complete: function () {
                $('#loginBtn')
                    .prop('disabled', false)
                    .text('Login');
            }
        });
    });
});