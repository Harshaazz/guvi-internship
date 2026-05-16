$(document).ready(function () {
    $('#registerForm').on('submit', function (e) {
        e.preventDefault();

        $.ajax({
            url: 'php/register.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    alert('Registration successful!');
                    window.location.href = 'login.html';
                } else {
                    alert(response.message);
                }
            },
            error: function (xhr) {
                alert('Error: ' + xhr.responseText);
            }
        });
    });
});