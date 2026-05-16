$(document).ready(function () {
    const token = localStorage.getItem('token');

    // If token does not exist, redirect to login
    if (!token) {
        alert('Please login first.');
        window.location.href = 'login.html';
        return;
    }

    // Load existing profile data
    $.ajax({
        url: 'php/profile.php',
        type: 'POST',
        dataType: 'json',
        data: {
            action: 'get',
            token: token
        },
        success: function (response) {
            console.log('Load Response:', response);

            if (response.status === 'success' && response.data) {
                $('#age').val(response.data.age || '');
                $('#dob').val(response.data.dob || '');
                $('#contact').val(response.data.contact || '');
                $('#address').val(response.data.address || '');
            }
        },
        error: function (xhr) {
            console.error('Load Error:', xhr.responseText);
        }
    });

    // Handle profile update
    $('#profileForm').on('submit', function (e) {
        e.preventDefault();

        $.ajax({
            url: 'php/profile.php',
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'update',
                token: token,
                age: $('#age').val(),
                dob: $('#dob').val(),
                contact: $('#contact').val(),
                address: $('#address').val()
            },
            success: function (response) {
                console.log('Update Response:', response);
                alert(response.message);
            },
            error: function (xhr) {
                console.error('Update Error:', xhr.responseText);
                alert('Error: ' + xhr.responseText);
            },
            success: function (response) {
                alert(response.message);
            }
        });
    });

    // Logout
    $('#logoutBtn').click(function () {
        localStorage.removeItem('token');
        window.location.href = 'login.html';
    });
    
});