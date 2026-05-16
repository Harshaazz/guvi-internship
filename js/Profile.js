$(document).ready(function () {
    const token = localStorage.getItem('token');

    if (!token) {
        window.location.href = 'login.html';
        return;
    }

    // Load profile data
    $.ajax({
        url: 'php/profile.php',
        type: 'POST',
        contentType: 'application/json',
        dataType: 'json',
        data: JSON.stringify({
            token: token,
            action: 'get'
        }),
        success: function (res) {
            if (res.status === 'success') {
                $('#username').val(res.user.username || '');
                $('#email').val(res.user.email || '');

                if (res.profile) {
                    $('#age').val(res.profile.age || '');
                    $('#dob').val(res.profile.dob || '');
                    $('#contact').val(res.profile.contact || '');
                    $('#address').val(res.profile.address || '');
                }
            } else {
                alert(res.message);
            }
        },
        error: function (xhr) {
            console.log(xhr.responseText);
            alert('Failed to load profile.');
        }
    });

    // Update profile
    $('#profileForm').on('submit', function (e) {
        e.preventDefault();

        $.ajax({
            url: 'php/profile.php',
            type: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({
                token: token,
                action: 'update',
                age: $('#age').val(),
                dob: $('#dob').val(),
                contact: $('#contact').val(),
                address: $('#address').val()
            }),
            success: function (res) {
                if (res.status === 'success') {
                    alert('Profile updated successfully!');
                } else {
                    alert(res.message);
                }
            },
            error: function (xhr) {
                console.log(xhr.responseText);
                alert('Failed to update profile.');
            }
        });
    });

    // Logout
    $('#logoutBtn').click(function () {
        localStorage.removeItem('token');
        window.location.href = 'login.html';
    });
});