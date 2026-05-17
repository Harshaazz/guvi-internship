document.addEventListener('DOMContentLoaded', function () {
    loadProfile();

    const form = document.getElementById('profileForm');
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            updateProfile();
        });
    }
});

function loadProfile() {
    const token = localStorage.getItem('token');
    const storedUser = JSON.parse(localStorage.getItem('user') || '{}');

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
            user: localStorage.getItem('user'),
            action: 'update',
            age: document.getElementById('age').value,
            dob: document.getElementById('dob').value,
            contact: document.getElementById('contact').value,
            address: document.getElementById('address').value
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
        const username = document.getElementById('username');
        const email = document.getElementById('email');

        if (username) username.value = data.user?.username || '';
        if (email) email.value = data.user?.email || '';

        // Profile data
        if (data.profile) {
            const age = document.getElementById('age');
            const dob = document.getElementById('dob');
            const contact = document.getElementById('contact');
            const address = document.getElementById('address');

            if (age) age.value = data.profile.age || '';
            if (dob) dob.value = data.profile.dob || '';
            if (contact) contact.value = data.profile.contact || '';
            if (address) address.value = data.profile.address || '';
        }
    })
    .catch(error => {
        console.error('Load profile error:', error);
        alert('Failed to load profile.');
    });
}

function updateProfile() {
    const token = localStorage.getItem('token');
    const storedUser = JSON.parse(localStorage.getItem('user') || '{}');

    fetch('php/profile.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            token: token,
            action: 'update',
            username: storedUser.username,
            email: storedUser.email,
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