/**
 * js/validation.js
 *
 * This file contains reusable client-side validation functions
 * for various forms in the Blood Donation System.
 * It aims to provide immediate feedback to users before form submission.
 */

/**
 * Validates if a given value is not empty (after trimming whitespace).
 * @param {string} value The string value to check.
 * @returns {boolean} True if the value is not empty, false otherwise.
 */
function isNotEmpty(value) {
    return value.trim() !== '';
}

/**
 * Validates if a given string is a valid email format.
 * @param {string} email The email string to validate.
 * @returns {boolean} True if the email is valid, false otherwise.
 */
function isValidEmail(email) {
    // Basic regex for email validation
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

/**
 * Validates if a given string meets a minimum length requirement.
 * @param {string} value The string value to check.
 * @param {number} minLength The minimum required length.
 * @returns {boolean} True if the length is at least minLength, false otherwise.
 */
function isMinLength(value, minLength) {
    return value.length >= minLength;
}

/**
 * Validates if a given value is a valid number.
 * @param {string} value The string value to check.
 * @returns {boolean} True if the value is a number, false otherwise.
 */
function isNumeric(value) {
    return !isNaN(parseFloat(value)) && isFinite(value);
}

/**
 * Validates if a given date string is a valid date.
 * Note: This is a basic check. For more robust date validation (e.g., future dates),
 * additional logic would be needed.
 * @param {string} dateString The date string to validate (e.g., "YYYY-MM-DD").
 * @returns {boolean} True if the date string represents a valid date, false otherwise.
 */
function isValidDate(dateString) {
    const date = new Date(dateString);
    return !isNaN(date.getTime());
}

/**
 * Compares two password values to ensure they match.
 * @param {string} password The first password string.
 * @param {string} confirmPassword The second password string (confirmation).
 * @returns {boolean} True if passwords match, false otherwise.
 */
function doPasswordsMatch(password, confirmPassword) {
    return password === confirmPassword;
}

/**
 * Validates a blood pressure string format (e.g., "120/80").
 * @param {string} bpValue The blood pressure string.
 * @returns {boolean} True if the format is valid, false otherwise.
 */
function isValidBloodPressure(bpValue) {
    return /^\d{2,3}\/\d{2,3}$/.test(bpValue);
}

/**
 * Helper function to display or hide an error message for a specific input field.
 * @param {HTMLElement} errorElement The HTML element (e.g., <p> or <span>) to display the error.
 * @param {boolean} show True to show the error, false to hide it.
 */
function toggleErrorMessage(errorElement, show) {
    if (errorElement) {
        errorElement.style.display = show ? 'block' : 'none';
    }
}

/**
 * Attaches a generic validation listener to a form.
 * This function can be expanded to handle specific validation rules for each form.
 * For now, it provides a basic structure.
 *
 * @param {HTMLFormElement} form The form element to attach the listener to.
 * @param {Function} validationRules A function that contains specific validation logic
 * for this form, returning true if all fields are valid.
 */
function attachFormValidation(form, validationRules) {
    if (form) {
        form.addEventListener('submit', function(event) {
            // Prevent default submission initially
            event.preventDefault();

            // Run specific validation rules for this form
            const isValid = validationRules();

            if (isValid) {
                // If all client-side validations pass, allow the form to submit
                form.submit();
            } else {
                // If validation fails, do nothing (prevent submission)
                // Error messages are already displayed by validationRules
            }
        });
    }
}

// --- Specific Form Validation Implementations ---

// Example: Login Form Validation (from login.html)
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        attachFormValidation(loginForm, function() {
            let isValid = true;

            const identifierInput = document.getElementById('identifier');
            const identifierError = document.getElementById('identifierError');
            if (!isNotEmpty(identifierInput.value)) {
                toggleErrorMessage(identifierError, true);
                isValid = false;
            } else {
                toggleErrorMessage(identifierError, false);
            }

            const passwordInput = document.getElementById('password');
            const passwordError = document.getElementById('passwordError');
            if (!isNotEmpty(passwordInput.value)) {
                toggleErrorMessage(passwordError, true);
                isValid = false;
            } else {
                toggleErrorMessage(passwordError, false);
            }

            return isValid;
        });
    }

    // Example: Register Form Validation (from register.html)
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        const userTypeSelect = document.getElementById('user_type');
        const medicalConditionGroup = document.getElementById('medicalConditionGroup');
        const medicalConditionInput = document.getElementById('medical_condition');

        // Function to toggle medical condition field visibility
        function toggleMedicalCondition() {
            if (userTypeSelect.value === 'recipient') {
                medicalConditionGroup.style.display = 'block';
                medicalConditionInput.required = true;
            } else {
                medicalConditionGroup.style.display = 'none';
                medicalConditionInput.required = false;
                medicalConditionInput.value = ''; // Clear value if hidden
            }
        }

        // Initial call and event listener for user type change
        toggleMedicalCondition();
        userTypeSelect.addEventListener('change', toggleMedicalCondition);

        attachFormValidation(registerForm, function() {
            let isValid = true;

            // Validate Username
            toggleErrorMessage(document.getElementById('usernameError'), !isNotEmpty(document.getElementById('username').value));
            if (!isNotEmpty(document.getElementById('username').value)) isValid = false;

            // Validate Email
            toggleErrorMessage(document.getElementById('emailError'), !isValidEmail(document.getElementById('email').value));
            if (!isValidEmail(document.getElementById('email').value)) isValid = false;

            // Validate Password
            const passwordInput = document.getElementById('password');
            toggleErrorMessage(document.getElementById('passwordError'), !isMinLength(passwordInput.value, 6));
            if (!isMinLength(passwordInput.value, 6)) isValid = false;

            // Validate Confirm Password
            const confirmPasswordInput = document.getElementById('confirm_password');
            const confirmPasswordError = document.getElementById('confirmPasswordError');
            if (!isNotEmpty(confirmPasswordInput.value) || !doPasswordsMatch(passwordInput.value, confirmPasswordInput.value)) {
                toggleErrorMessage(confirmPasswordError, true);
                isValid = false;
            } else {
                toggleErrorMessage(confirmPasswordError, false);
            }

            // Validate User Type
            toggleErrorMessage(document.getElementById('userTypeError'), !isNotEmpty(userTypeSelect.value));
            if (!isNotEmpty(userTypeSelect.value)) isValid = false;

            // Validate Full Name
            toggleErrorMessage(document.getElementById('fullNameError'), !isNotEmpty(document.getElementById('full_name').value));
            if (!isNotEmpty(document.getElementById('full_name').value)) isValid = false;

            // Validate Date of Birth
            toggleErrorMessage(document.getElementById('dobError'), !isValidDate(document.getElementById('date_of_birth').value));
            if (!isValidDate(document.getElementById('date_of_birth').value)) isValid = false;

            // Validate Gender
            toggleErrorMessage(document.getElementById('genderError'), !isNotEmpty(document.getElementById('gender').value));
            if (!isNotEmpty(document.getElementById('gender').value)) isValid = false;

            // Validate Blood Group
            toggleErrorMessage(document.getElementById('bloodGroupError'), !isNotEmpty(document.getElementById('blood_group').value));
            if (!isNotEmpty(document.getElementById('blood_group').value)) isValid = false;

            // Validate Contact Number
            toggleErrorMessage(document.getElementById('contactNumberError'), !isNotEmpty(document.getElementById('contact_number').value));
            if (!isNotEmpty(document.getElementById('contact_number').value)) isValid = false;

            // Validate Address
            toggleErrorMessage(document.getElementById('addressError'), !isNotEmpty(document.getElementById('address').value));
            if (!isNotEmpty(document.getElementById('address').value)) isValid = false;

            // Validate City
            toggleErrorMessage(document.getElementById('cityError'), !isNotEmpty(document.getElementById('city').value));
            if (!isNotEmpty(document.getElementById('city').value)) isValid = false;

            // Validate State
            toggleErrorMessage(document.getElementById('stateError'), !isNotEmpty(document.getElementById('state').value));
            if (!isNotEmpty(document.getElementById('state').value)) isValid = false;

            // Validate Medical Condition if Recipient
            if (userTypeSelect.value === 'recipient') {
                const medicalConditionError = medicalConditionGroup.querySelector('.error-message-inline');
                if (medicalConditionInput.required && !isNotEmpty(medicalConditionInput.value)) {
                    toggleErrorMessage(medicalConditionError, true);
                    isValid = false;
                } else {
                    toggleErrorMessage(medicalConditionError, false);
                }
            }

            return isValid;
        });
    }

    // Example: Request Blood Form Validation (from request_blood.php)
    const bloodRequestForm = document.getElementById('bloodRequestForm');
    if (bloodRequestForm) {
        attachFormValidation(bloodRequestForm, function() {
            let isValid = true;

            // Validate Blood Group
            toggleErrorMessage(document.getElementById('bloodGroupError'), !isNotEmpty(document.getElementById('blood_group').value));
            if (!isNotEmpty(document.getElementById('blood_group').value)) isValid = false;

            // Validate Quantity (simple non-empty and numeric check)
            const quantityInput = document.getElementById('quantity_units');
            toggleErrorMessage(document.getElementById('quantityError'), !isNotEmpty(quantityInput.value) || !isNumeric(quantityInput.value));
            if (!isNotEmpty(quantityInput.value) || !isNumeric(quantityInput.value)) isValid = false;

            // Validate Urgency
            toggleErrorMessage(document.getElementById('urgencyError'), !isNotEmpty(document.getElementById('urgency').value));
            if (!isNotEmpty(document.getElementById('urgency').value)) isValid = false;

            // Validate Hospital Name
            toggleErrorMessage(document.getElementById('hospitalNameError'), !isNotEmpty(document.getElementById('hospital_name').value));
            if (!isNotEmpty(document.getElementById('hospital_name').value)) isValid = false;

            // Validate Hospital Address
            toggleErrorMessage(document.getElementById('hospitalAddressError'), !isNotEmpty(document.getElementById('hospital_address').value));
            if (!isNotEmpty(document.getElementById('hospital_address').value)) isValid = false;

            // Validate Contact Person
            toggleErrorMessage(document.getElementById('contactPersonError'), !isNotEmpty(document.getElementById('contact_person').value));
            if (!isNotEmpty(document.getElementById('contact_person').value)) isValid = false;

            // Validate Contact Number
            toggleErrorMessage(document.getElementById('contactNumberError'), !isNotEmpty(document.getElementById('contact_number').value));
            if (!isNotEmpty(document.getElementById('contact_number').value)) isValid = false;

            return isValid;
        });
    }

    // Example: Add Event Form Validation (from manage_events.php)
    const addEventForm = document.querySelector('.event-form'); // Assuming this is the form for adding events
    if (addEventForm) {
        attachFormValidation(addEventForm, function() {
            let isValid = true;

            // For simplicity, using a generic alert for now if errors exist.
            // In a more complex setup, you'd add specific error spans for each field.
            if (!isNotEmpty(document.getElementById('event_name').value) ||
                !isNotEmpty(document.getElementById('event_date').value) ||
                !isNotEmpty(document.getElementById('location').value)) {
                alert('Please fill in all required fields for the event (Event Name, Date, Location).');
                isValid = false;
            }
            return isValid;
        });
    }

    // Example: Add Medical Record Form Validation (from medical_records.php)
    const addMedicalRecordForm = document.querySelector('.medical-record-form');
    if (addMedicalRecordForm) {
        attachFormValidation(addMedicalRecordForm, function() {
            let isValid = true;

            const donorId = document.getElementById('donor_id').value;
            const recordDate = document.getElementById('record_date').value;
            const hemoglobinLevel = document.getElementById('hemoglobin_level').value;
            const bloodPressure = document.getElementById('blood_pressure').value;
            const eligibilityStatus = document.getElementById('eligibility_status').value;

            if (!isNotEmpty(donorId) || !isNotEmpty(recordDate) || !isNumeric(hemoglobinLevel) ||
                !isValidBloodPressure(bloodPressure) || !isNotEmpty(eligibilityStatus)) {
                alert('Please fill in all required fields correctly for the medical record (Donor, Record Date, Hemoglobin, Blood Pressure, Eligibility Status).');
                isValid = false;
            }
            return isValid;
        });
    }

    // Example: Add Donation Form Validation (from donations.php)
    const addDonationForm = document.querySelector('.donation-form');
    if (addDonationForm) {
        attachFormValidation(addDonationForm, function() {
            let isValid = true;

            const donorId = document.getElementById('donor_id').value;
            const donationDate = document.getElementById('donation_date').value;
            const bloodGroup = document.getElementById('blood_group').value;
            const quantityMl = document.getElementById('quantity_ml').value;
            const status = document.getElementById('status').value;

            if (!isNotEmpty(donorId) || !isNotEmpty(donationDate) || !isNotEmpty(bloodGroup) ||
                !isNumeric(quantityMl) || !isNotEmpty(status)) {
                alert('Please fill in all required fields correctly for the donation (Donor, Date, Blood Group, Quantity, Status).');
                isValid = false;
            }
            return isValid;
        });
    }
});
