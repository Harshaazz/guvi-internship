$(document).ready(function() {
    $('#registerForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            username: $('[name="username"]').val().trim(),
            email: $('[name="email"]').val().trim(),
            password: $('[name="password"]').val()
        };

        $('#regBtn').prop('disabled', true).text('Registering...');

        $.ajax({
            url: 'php/register.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            success: function(res) {
                if(res.status === 'success') {
                    $('#regMessage').html(`<div class="alert alert-success">${res.message}</div>`);
                    setTimeout(() => window.location.href = 'login.html', 1800);
                } else {
                    $('#regMessage').html(`<div class="alert alert-danger">${res.message}</div>`);
                }
            },
            error: function() {
                $('#regMessage').html('<div class="alert alert-danger">Server error occurred</div>');
            },
            complete: function() {
                $('#regBtn').prop('disabled', false).text('Register');
            }
        });
    });
});