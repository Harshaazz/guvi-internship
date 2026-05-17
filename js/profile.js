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

    if (!token) {
        alert('Please login again.');
        window.location.href = 'login.html';
        return;
    }

    // Safely read field values
    const age = document.getElementById('age')?.value || '';
    const dob = document.getElementById('dob')?.value || '';
    const contact = document.getElementById('contact')?.value || '';
    const address = document.getElementById('address')?.value || '';

    fetch('php/profile.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            token: token,
            action: 'update',
            age: age,
            dob: dob,
            contact: contact,
            address: address
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
    window.location.href = 'login.html';
}