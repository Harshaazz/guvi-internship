$(document).ready(function () {
    const token = localStorage.getItem("session_token");

    // If user is not logged in, redirect to login page
    if (!token) {
        window.location.href = "login.html";
        return;
    }

    // Load profile data
    $.ajax({
        url: "php/profile.php",
        type: "POST",
        contentType: "application/json",
        data: JSON.stringify({
            action: "get",
            token: token
        }),
        success: function (response) {
            if (response.status === "success") {
                // Fill read-only fields
                $("#username").val(response.user.username);
                $("#email").val(response.user.email);

                // Fill editable profile fields
                if (response.profile) {
                    $("#age").val(response.profile.age || "");
                    $("#dob").val(response.profile.dob || "");
                    $("#contact").val(response.profile.contact || "");
                    $("#address").val(response.profile.address || "");
                }
            } else {
                alert(response.message);
                localStorage.removeItem("session_token");
                window.location.href = "login.html";
            }
        },
        error: function () {
            alert("Failed to load profile.");
        }
    });

    // Update profile
    $("#profileForm").on("submit", function (e) {
        e.preventDefault();

        $.ajax({
            url: "php/profile.php",
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify({
                action: "update",
                token: token,
                age: $("#age").val(),
                dob: $("#dob").val(),
                contact: $("#contact").val(),
                address: $("#address").val()
            }),
            success: function (response) {
                if (response.status === "success") {
                    alert("Profile updated successfully!");
                } else {
                    alert(response.message);
                }
            },
            error: function () {
                alert("Failed to update profile.");
            }
        });
    });

    // Logout
    $("#logoutBtn").on("click", function () {
        localStorage.removeItem("session_token");
        window.location.href = "login.html";
    });
});