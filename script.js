// script.js for GCIMS Login

// 1. Function to handle the ROLE selection buttons
function setRole(roleName) {
    // A. Visual Feedback: Update the active button style
    // Clear 'active' class from all role buttons
    document.querySelectorAll('.role-btn').forEach(btn => {
        btn.classList.remove('active');
    });

    // Add 'active' class to the clicked button
    if (roleName === 'Staff') {
        document.getElementById('staff-role').classList.add('active');
    } else if (roleName === 'Admin') {
        document.getElementById('admin-role').classList.add('active');
    }

    // B. Logic: Store the selected role in the hidden input
    document.getElementById('selected-role').value = roleName;
}

// 2. Handle Form Submission and Redirection
document.getElementById('gcims-login-form').addEventListener('submit', function(event) {
    // Stop the form from submitting normally (which refreshes the page)
    event.preventDefault();

    // In a real application, you would send username/password/role to your server.
    // For this demonstration, we are just implementing the navigation logic.
    
    // Get the inputs
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value; // In practice, you check this!
    const selectedRole = document.getElementById('selected-role').value;

    // Check that we have values
    if (!username || !password) {
        alert("Username and password are required.");
        return;
    }

    // 3. Perform Redirection based on the selected role (the key workflow difference)
    if (selectedRole === 'Staff') {
        // Navigate Staff users to the simplified directory view
        // console.log(`Staff ${username} logged in.`);
        window.location.href = 'staff_dashboard.html';
    } else if (selectedRole === 'Admin') {
        // Navigate Admin users to the full system dashboard
        // console.log(`Admin ${username} logged in.`);
        window.location.href = 'admin_dashboard.html';
    } else {
        // If somehow no role was selected
        alert("An error occurred during login. Please try again.");
    }
});