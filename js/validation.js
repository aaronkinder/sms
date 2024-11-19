document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('#subscriptionForm');
    const formContainer = document.querySelector('#formContainer');
    const spinner = document.querySelector('#spinner');
    const successMessage = document.querySelector('#successMessage');
    const cooldownMessage = document.getElementById('cooldown-timer');
    let cooldownTimer;

    function startCooldownTimer(seconds) {
        let remainingTime = seconds;
        cooldownMessage.textContent = `Please wait ${remainingTime} seconds before submitting again.`;
        cooldownMessage.style.display = 'block';

        cooldownTimer = setInterval(function() {
            remainingTime--;
            cooldownMessage.textContent = `Please wait ${remainingTime} seconds before submitting again.`;

            if (remainingTime <= 0) {
                clearInterval(cooldownTimer);
                cooldownMessage.style.display = 'none';
            }
        }, 1000);
    }

    function validateForm() {
        let isValid = true;
        const inputs = form.querySelectorAll('input[required]');

        inputs.forEach(function(input) {
            if (input.value.trim() === '') {
                isValid = false;
                input.classList.add('is-invalid');
                input.nextElementSibling.textContent = 'This field is required.';
            } else {
                input.classList.remove('is-invalid');
                input.nextElementSibling.textContent = '';
            }
        });

        const phoneInput = form.querySelector('input[type="tel"]');
        if (phoneInput && !/^\d{10}$/.test(phoneInput.value.trim())) {
            isValid = false;
            phoneInput.classList.add('is-invalid');
            phoneInput.nextElementSibling.textContent = 'Please enter a valid 10-digit US phone number.';
        }

        const emailInput = form.querySelector('input[type="email"]');
        if (emailInput && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value.trim())) {
            isValid = false;
            emailInput.classList.add('is-invalid');
            emailInput.nextElementSibling.textContent = 'Please enter a valid email address.';
        }

        return isValid;
    }

    if (form) {
        form.addEventListener('submit', function(event) {
            event.preventDefault();

            if (validateForm()) {
                formContainer.style.display = 'none';
                spinner.style.display = 'block';

                const formData = new FormData(form);

                fetch('process.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    spinner.style.display = 'none';
                    
                    if (data.success) {
                        successMessage.style.display = 'block';
                    } else {
                        formContainer.style.display = 'block';
                        alert(data.message); // You might want to replace this with a more user-friendly error display
                    }

                    if (data.cooldown > 0) {
                        startCooldownTimer(data.cooldown);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    spinner.style.display = 'none';
                    formContainer.style.display = 'block';
                    alert('An error occurred. Please try again later.');
                });
            }
        });
    }

    // New function to toggle password visibility
    function togglePasswordVisibility(event) {
        const target = event.currentTarget.getAttribute('data-target');
        const passwordInput = document.getElementById(target);
        const icon = event.currentTarget.querySelector('i');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    // Add event listeners to password toggle icons
    const togglePasswordIcons = document.querySelectorAll('.toggle-password');
    togglePasswordIcons.forEach(icon => {
        icon.addEventListener('click', togglePasswordVisibility);
    });
});
