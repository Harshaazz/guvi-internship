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
            type: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({
                email: email,
                password: password
            }),

            success: function (res) {
                // Handle response if returned as string
                if (typeof res === 'string') {
                    try {
                        res = JSON.parse(res);
                    } catch (e) {
                        alert('Invalid server response.');
                        return;
                    }
                }

                if (res.status === 'success') {
                    // Save session token
                    localStorage.setItem('token', res.token);

                    // Save user object
                    if (res.user) {
                        localStorage.setItem('user', JSON.stringify(res.user));
                    }

                    // Remove old token key if it exists
                    localStorage.removeItem('session_token');

                    // Redirect to profile page
                    window.location.href = 'profile.html';
                } else {
                    alert(res.message || 'Login failed.');
                }
            },

            error: function (xhr) {
                console.log(xhr.responseText);
                alert('Login failed. Please try again.');
            },

            complete: function () {
                $('#loginBtn').prop('disabled', false).text('Login');
            }
        });
    });
});