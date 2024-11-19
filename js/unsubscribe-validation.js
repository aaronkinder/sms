document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('unsubscribeForm');
    const phoneInput = document.getElementById('phone');
    const emailInput = document.getElementById('email');
    const captchaInput = document.getElementById('captcha');
    const selectedTypeInput = document.getElementById('selectedType');

    // Tab switching functionality
    document.querySelectorAll('.unsubscribe-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            const tabType = this.getAttribute('data-tab');
            
            // Update tabs
            document.querySelectorAll('.unsubscribe-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            // Update content
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            document.getElementById(tabType + 'Input').classList.add('active');
            
            // Update form
            selectedTypeInput.value = tabType;
            
            // Clear and update required fields
            if (tabType === 'phone') {
                emailInput.value = '';
                emailInput.removeAttribute('required');
                phoneInput.setAttribute('required', 'required');
            } else {
                phoneInput.value = '';
                phoneInput.removeAttribute('required');
                emailInput.setAttribute('required', 'required');
            }

            // Clear any previous validation states
            clearValidation();
        });
    });

    function clearValidation() {
        phoneInput.classList.remove('is-invalid');
        emailInput.classList.remove('is-invalid');
        captchaInput.classList.remove('is-invalid');
        // Remove any existing error messages
        document.querySelectorAll('.alert').forEach(alert => {
            if (!alert.classList.contains('alert-warning')) {
                alert.remove();
            }
        });
    }

    function validatePhoneNumber(phone) {
        // Remove any spaces or special characters except plus
        const cleaned = phone.replace(/[^0-9+]/g, '');
        
        // Check if it's already in +1XXXXXXXXXX format
        if (/^\+1[0-9]{10}$/.test(cleaned)) {
            return true;
        }
        
        // Check if it's a 10-digit number
        if (/^[0-9]{10}$/.test(cleaned)) {
            return true;
        }
        
        // Check if it starts with 1 followed by 10 digits
        if (/^1[0-9]{10}$/.test(cleaned)) {
            return true;
        }
        
        return false;
    }

    function formatPhoneNumber(phone) {
        // Remove any spaces or special characters except plus
        let cleaned = phone.replace(/[^0-9+]/g, '');
        
        // If already in correct format, return as is
        if (/^\+1[0-9]{10}$/.test(cleaned)) {
            return cleaned;
        }
        
        // If starts with 1, remove it
        if (cleaned.startsWith('1')) {
            cleaned = cleaned.substring(1);
        }
        
        // If 10 digits, add +1 prefix
        if (/^[0-9]{10}$/.test(cleaned)) {
            return '+1' + cleaned;
        }
        
        return cleaned;
    }

    function validateEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    // Form validation
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Always prevent default submission initially
            clearValidation();

            let isValid = true;
            const type = selectedTypeInput.value;
            
            if (type === 'phone') {
                const phoneValue = phoneInput.value;
                if (!validatePhoneNumber(phoneValue)) {
                    isValid = false;
                    phoneInput.classList.add('is-invalid');
                    document.getElementById('phoneError').style.display = 'block';
                } else {
                    // Format the phone number before submission
                    const formattedNumber = formatPhoneNumber(phoneValue);
                    phoneInput.value = formattedNumber;
                    console.log('Submitting phone number:', formattedNumber); // Debug log
                }
            } else {
                if (!validateEmail(emailInput.value)) {
                    isValid = false;
                    emailInput.classList.add('is-invalid');
                    document.getElementById('emailError').style.display = 'block';
                }
            }

            if (!captchaInput.value) {
                isValid = false;
                captchaInput.classList.add('is-invalid');
                document.getElementById('captchaError').style.display = 'block';
            }

            if (isValid) {
                // Create a hidden input to store the formatted phone number
                if (type === 'phone') {
                    const formattedNumber = formatPhoneNumber(phoneInput.value);
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'identifier';
                    hiddenInput.value = formattedNumber;
                    form.appendChild(hiddenInput);
                    console.log('Submitting with hidden input:', formattedNumber); // Debug log
                }
                
                // If validation passes, submit the form
                form.submit();
            }
        });
    }

    // Real-time validation and formatting for phone number
    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            // Allow numeric input and + sign
            let value = this.value.replace(/[^0-9+]/g, '');
            
            // Ensure + sign is only at the start
            if (value.includes('+') && !value.startsWith('+')) {
                value = value.replace(/\+/g, '');
            }
            
            // Update input value
            this.value = value;
            
            // Validate format
            if (validatePhoneNumber(value)) {
                this.classList.remove('is-invalid');
                document.getElementById('phoneError').style.display = 'none';
                // Format the number as user types
                this.value = formatPhoneNumber(value);
            } else {
                this.classList.add('is-invalid');
                document.getElementById('phoneError').style.display = 'block';
            }
        });

        // Format on blur
        phoneInput.addEventListener('blur', function() {
            if (validatePhoneNumber(this.value)) {
                this.value = formatPhoneNumber(this.value);
            }
        });
    }

    // Real-time validation for email
    if (emailInput) {
        emailInput.addEventListener('input', function() {
            if (validateEmail(this.value)) {
                this.classList.remove('is-invalid');
                document.getElementById('emailError').style.display = 'none';
            } else {
                this.classList.add('is-invalid');
                document.getElementById('emailError').style.display = 'block';
            }
        });
    }

    // Real-time validation for captcha
    if (captchaInput) {
        captchaInput.addEventListener('input', function() {
            if (this.value) {
                this.classList.remove('is-invalid');
                document.getElementById('captchaError').style.display = 'none';
            } else {
                this.classList.add('is-invalid');
                document.getElementById('captchaError').style.display = 'block';
            }
        });
    }
});
