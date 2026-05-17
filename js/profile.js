document.addEventListener('DOMContentLoaded', function () {
    loadProfile();

    document.getElementById('profileForm').addEventListener('submit', function (e) {
        e.preventDefault();
        updateProfile();
    });
});

function loadProfile() {
    const token = localStorage.getItem('token');

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

        // User data
        document.getElementById('username').value = data.user?.username || '';
        document.getElementById('email').value = data.user?.email || '';

        // Profile data
        if (data.profile) {
            document.getElementById('age').value = data.profile.age || '';
            document.getElementById('dob').value = data.profile.dob || '';
            document.getElementById('contact').value = data.profile.contact || '';
            document.getElementById('address').value = data.profile.address || '';
        }
    })
    .catch(error => {
        console.error('Load profile error:', error);
        alert('Failed to load profile.');
    });
}

function updateProfile() {
    const token = localStorage.getItem('token');

    fetch('php/profile.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            token: token,
            action: 'update',
            age: document.getElementById('age').value,
            dob: document.getElementById('dob').value,
            contact: document.getElementById('contact').value,
            address: document.getElementById('address').value
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Update response:', data);
        alert(data.message || 'Profile updated.');
    })
    .catch(error => {
        console.error('Update profile error:', error);
        alert('Failed to update profile.');
    });
}

function logout() {
    localStorage.removeItem('token');
    window.location.href = 'login.html';
}