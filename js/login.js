// js/login.js

$(document).ready(function () {
    $('#loginForm').on('submit', function (e) {
        e.preventDefault();

        const email = $('#email').val().trim();
        const password = $('#password').val();

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
                    // Save session token in localStorage
                    localStorage.setItem('token', response.token);
                    localStorage.setItem('session_token', response.token);
                    localStorage.setItem('user', JSON.stringify(response.user));

                    // Redirect to profile page
                    window.location.href = 'profile.html';
                } else {
                    alert(response.message || 'Login failed. Please try again.');
                }
            },

            error: function (xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                alert('Server error occurred during login.');
            },

            complete: function () {
                $btn.prop('disabled', false).text('Login');
            }
        });
    });
});