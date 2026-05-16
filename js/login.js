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

        $('#loginBtn').prop('disabled', true).text('Logging in...');

        $.ajax({
            url: 'php/login.php',
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({
                email: email,
                password: password
            }),

            success: function (res) {
                console.log('Login response:', res);

                if (res.status === 'success') {
                    // Save ONLY one token
                    localStorage.setItem('token', res.token);

                    // Save user info
                    if (res.user) {
                        localStorage.setItem(
                            'user',
                            JSON.stringify(res.user)
                        );
                    }

                    // Remove old token key if present
                    localStorage.removeItem('session_token');

                    // Force redirect
                    window.location.replace('profile.html');
                } else {
                    alert(res.message || 'Login failed.');
                }
            },

            error: function (xhr) {
                console.log('Server response:', xhr.responseText);
                alert('Login failed. Check console for details.');
            },

            complete: function () {
                $('#loginBtn').prop('disabled', false).text('Login');
            }
        });
    });
});