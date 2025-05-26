// public/js/validation.js

/*function validateForm() {
    const fullName = document.getElementById('full_name').value.trim();
    const email = document.getElementById('email').value.trim();
    const gender = document.getElementById('gender').value;

    let isValid = true;

    if (fullName === '') {
        showError('full_name', 'Full name is required');
        isValid = false;
    } else {
        clearError('full_name');
    }

    if (email === '') {
        showError('email', 'Email is required');
        isValid = false;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        showError('email', 'Please enter a valid email address');
        isValid = false;
    } else {
        clearError('email');
    }


    if (gender === '') {
        showError('gender', 'Please select a gender');
        isValid = false;
    } else {
        clearError('gender');
    }

    return isValid;
}

function showError(fieldId, message) {
    const field = document.getElementById(fieldId);
    const errorElement = field.parentNode.querySelector('.error-message') || document.createElement('div');
    errorElement.className = 'error-message';
    errorElement.textContent = message;
    field.parentNode.appendChild(errorElement);
    field.classList.add('error');
}

function clearError(fieldId) {
    const field = document.getElementById(fieldId);
    const errorElement = field.parentNode.querySelector('.error-message');
    if (errorElement) {
        errorElement.remove();
    }
    field.classList.remove('error');
}*/

