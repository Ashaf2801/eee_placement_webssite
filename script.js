// Switch between Staff and Student login
function switchLoginType(type) {
    const staffBtn = document.getElementById('staffBtn');
    const studentBtn = document.getElementById('studentBtn');
    const userTypeInput = document.getElementById('userType');
    const usernameInput = document.getElementById('username');
    
    // Clear any previous messages
    hideMessages();
    
    if (type === 'staff') {
        staffBtn.classList.add('active');
        studentBtn.classList.remove('active');
        userTypeInput.value = 'staff';
        usernameInput.placeholder = 'Staff Username';
    } else {
        studentBtn.classList.add('active');
        staffBtn.classList.remove('active');
        userTypeInput.value = 'student';
        usernameInput.placeholder = 'Student ID';
    }
    
    // Clear form fields
    document.getElementById('loginForm').reset();
    userTypeInput.value = type;
}

// Show error message
function showError(message) {
    const errorDiv = document.getElementById('error-message');
    const successDiv = document.getElementById('success-message');
    
    hideMessages();
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';
}

// Show success message
function showSuccess(message) {
    const errorDiv = document.getElementById('error-message');
    const successDiv = document.getElementById('success-message');
    
    hideMessages();
    successDiv.textContent = message;
    successDiv.style.display = 'block';
}

// Hide all messages
function hideMessages() {
    document.getElementById('error-message').style.display = 'none';
    document.getElementById('success-message').style.display = 'none';
}

// Validate form inputs
function validateForm(username, password) {
    if (!username || !password) {
        showError('Please fill in all fields');
        return false;
    }
    
    if (username.length < 3) {
        showError('Username must be at least 3 characters long');
        return false;
    }
    
    if (password.length < 6) {
        showError('Password must be at least 6 characters long');
        return false;
    }
    
    return true;
}

// Handle form submission
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;
    const userType = document.getElementById('userType').value;
    const submitBtn = document.querySelector('.submit-btn');
    
    // Validate form
    if (!validateForm(username, password)) {
        return;
    }
    
    // Show loading state
    submitBtn.classList.add('loading');
    submitBtn.disabled = true;
    hideMessages();
    
    // Create form data
    const formData = new FormData();
    formData.append('username', username);
    formData.append('password', password);
    formData.append('userType', userType);
    
    // Send AJAX request to PHP
    fetch('login.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // Remove loading state
        submitBtn.classList.remove('loading');
        submitBtn.disabled = false;
        
        if (data.success) {
            showSuccess(data.message);
            
            // Redirect to dashboard.html after successful login
            setTimeout(() => {
                window.location.href = 'dashboard.php';
            }, 1500);
        } else {
            showError(data.message);
        }
    })
    .catch(error => {
        // Remove loading state
        submitBtn.classList.remove('loading');
        submitBtn.disabled = false;
        
        console.error('Error:', error);
        showError('An error occurred. Please try again.');
    });
});

// Clear messages when user starts typing
document.getElementById('username').addEventListener('input', hideMessages);
document.getElementById('password').addEventListener('input', hideMessages);

// Enter key support for login buttons
document.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && e.target.classList.contains('login-btn')) {
        e.target.click();
    }
});

// Search functionality - Add this event listener
document.getElementById('userSearch').addEventListener('input', function() {
    if (window.allUsers) {
        renderUserTable(window.allUsers);
    }
});

// Initialize the form
document.addEventListener('DOMContentLoaded', function() {
    loadUsers();
    loadBatches();
    // Add this line to enable live search
    document.getElementById('userSearch').addEventListener('input', function() {
        renderUserTable(window.allUsers || []);
    });
});
