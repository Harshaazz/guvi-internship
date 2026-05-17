document.addEventListener('DOMContentLoaded', function () {
    loadProfile();

    document.getElementById('profileForm').addEventListener('submit', function (e) {
        e.preventDefault();
        updateProfile();
    });
});

function loadProfile() {
    const token = localStorage.getItem('token');
    const user = JSON.parse(localStorage.getItem('user') || '{}');

    if (!token) {
        alert('Please login first.');
        window.location.href = 'login.html';
        return;
    }

    fetch('php/profile.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            token: token,
            user: user,
            action: 'get'
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Profile response:', data);

        if (data.status !== 'success') {
            alert(data.message || 'Failed to load profile.');
            return;
        }

        // Fill readonly fields
        document.getElementById('username').value = data.user?.username || '';
        document.getElementById('email').value = data.user?.email || '';

        // Fill profile fields
        if (data.profile) {
            document.querySelector('[name="age"]').value = data.profile.age || '';
            document.querySelector('[name="dob"]').value = data.profile.dob || '';
            document.querySelector('[name="contact"]').value = data.profile.contact || '';
            document.querySelector('[name="address"]').value = data.profile.address || '';
        }
    })
    .catch(error => {
        console.error('Load profile error:', error);
        alert('Failed to load profile.');
    });
}

function updateProfile() {
    const token = localStorage.getItem('token');
    const user = JSON.parse(localStorage.getItem('user') || '{}');

    if (!token) {
        alert('Please login first.');
        return;
    }

    fetch('php/profile.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            token: token,
            user: user,
            action: 'update',
            age: document.querySelector('[name="age"]').value,
            dob: document.querySelector('[name="dob"]').value,
            contact: document.querySelector('[name="contact"]').value,
            address: document.querySelector('[name="address"]').value
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Update response:', data);

        if (data.status === 'success') {
            alert(data.message || 'Profile updated successfully.');
        } else {
            alert(data.message || 'Failed to update profile.');
        }
    })
    .catch(error => {
        console.error('Update profile error:', error);
        alert('Failed to update profile.');
    });
}

function logout() {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    window.location.href = 'login.html';
}