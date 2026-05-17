// js/login.js

$(document).ready(function () {
    $('#loginBtn').on('click', function (e) {
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
                console.log('Login response:', res);

                if (res.status === 'success') {
                    // Save token
                    localStorage.setItem('token', res.token);

                    // Save user data
                    if (res.user) {
                        localStorage.setItem(
                            'user',
                            JSON.stringify(res.user)
                        );
                    }

                    // Remove old key
                    localStorage.removeItem('session_token');

                    // Redirect to profile page
                    setTimeout(function () {
                        window.location.href = 'profile.html';
                    }, 100);
                } else {
                    alert(res.message || 'Login failed.');
                }
            },

            error: function (xhr) {
                console.log(xhr.responseText);
                alert('Login failed. Check console for details.');
            },

            complete: function () {
                $('#loginBtn').prop('disabled', false).text('Login');
            }
        });
    });
});