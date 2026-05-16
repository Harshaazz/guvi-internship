$(document).ready(function () {
    $('#loginForm').on('submit', function (e) {
        e.preventDefault();

        $.ajax({
            url: 'php/login.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                email: $('#email').val().trim(),
                password: $('#password').val()
            }),
            success: function (res) {
                if (res.status === 'success') {
                    // Save ONLY one token
                    localStorage.setItem('token', res.token);
                    localStorage.setItem('user', JSON.stringify(res.user));

                    // Remove old incorrect key
                    localStorage.removeItem('session_token');

                    window.location.href = 'profile.html';
                } else {
                    alert(res.message);
                }
            },
            error: function () {
                alert('Login failed.');
            }
        });
    });
});