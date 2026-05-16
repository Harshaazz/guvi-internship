$(document).ready(function () {
    $('#loginForm').on('submit', function (e) {
        e.preventDefault();

        const formData = {
            email: $('[name="email"]').val().trim(),
            password: $('[name="password"]').val()
        };

        $('#loginBtn').prop('disabled', true).text('Logging in...');

        $.ajax({
            url: 'php/login.php',
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(formData),

            success: function (res) {
                if (res.status === 'success') {
                    // IMPORTANT: Must match profile.js
                    localStorage.setItem('session_token', res.token);

                    window.location.href = 'profile.html';
                } else {
                    $('#loginMessage').html(
                        `<div class="alert alert-danger">${res.message}</div>`
                    );
                }
            },

            error: function () {
                $('#loginMessage').html(
                    '<div class="alert alert-danger">Login failed. Try again.</div>'
                );
            },

            complete: function () {
                $('#loginBtn').prop('disabled', false).text('Login');
            }
        });
    });
});