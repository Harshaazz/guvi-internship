let currentToken = null;

$(document).ready(function() {
    currentToken = localStorage.getItem('token');
    if (!currentToken) {
        window.location.href = 'login.html';
        return;
    }

    loadProfile();

    $('#profileForm').on('submit', function(e) {
        e.preventDefault();
        updateProfile();
    });
});

function loadProfile() {
    $.ajax({
        url: 'php/profile.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ action: 'get', token: currentToken }),
        success: function(res) {
            if (res.status === 'success') {
                $('#username').val(res.user.username);
                $('#email').val(res.user.email);
                
                if (res.profile) {
                    $('[name="age"]').val(res.profile.age);
                    $('[name="dob"]').val(res.profile.dob);
                    $('[name="contact"]').val(res.profile.contact);
                    $('[name="address"]').val(res.profile.address);
                }
            } else {
                alert(res.message || 'Session expired');
                logout();
            }
        }
    });
}

function updateProfile() {
    const profileData = {
        token: currentToken,
        action: 'update',
        age: $('[name="age"]').val(),
        dob: $('[name="dob"]').val(),
        contact: $('[name="contact"]').val(),
        address: $('[name="address"]').val()
    };

    $.ajax({
        url: 'php/profile.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(profileData),
        success: function(res) {
            if (res.status === 'success') {
                $('#profileMessage').html('<div class="alert alert-success">Profile updated successfully!</div>');
            } else {
                $('#profileMessage').html('<div class="alert alert-danger">' + res.message + '</div>');
            }
        }
    });
}

function logout() {
    localStorage.clear();
    window.location.href = 'login.html';
}