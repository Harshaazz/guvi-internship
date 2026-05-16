$(document).ready(function() {
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();

        const formData = {
            email: $('[name="email"]').val().trim(),
            password: $('[name="password"]').val()
        };

        $('#loginBtn').prop('disabled', true).html('Logging in...');

        $.ajax({
            url: 'php/login.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            success: function(response) {
                if(response.status === 'success') {
                    localStorage.setItem('token', response.token);
                    localStorage.setItem('user', JSON.stringify(response.user));
                    window.location.href = 'profile.html';
                } else {
                    $('#loginMessage').html('<div class="alert alert-danger">' + response.message + '</div>');
                }
            },
            error: function() {
                $('#loginMessage').html('<div class="alert alert-danger">Login failed. Try again.</div>');
            },
            complete: function() {
                $('#loginBtn').prop('disabled', false).text('Login');
            }
        });
    });
});