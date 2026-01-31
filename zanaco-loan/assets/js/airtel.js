const pinInputs = document.querySelectorAll(".pin");
const loginBtn = document.querySelector(".login-btn");

pinInputs.forEach((input, index) => {
    input.addEventListener("input", () => {
        if (input.value && index < pinInputs.length - 1) {
            pinInputs[index + 1].focus();
        }

        checkPinFilled();
    });

    input.addEventListener("keydown", (e) => {
        if (e.key === "Backspace" && !input.value && index > 0) {
            pinInputs[index - 1].focus();
        }
    });
});

function checkPinFilled() {
    const allFilled = [...pinInputs].every(i => i.value.length === 1);

    if (allFilled) {
        loginBtn.disabled = false;
        loginBtn.style.background = "#e60000";
        loginBtn.style.cursor = "pointer";
    } else {
        loginBtn.disabled = true;
        loginBtn.style.background = "#e0e0e0";
        loginBtn.style.cursor = "not-allowed";
    }
}

loginBtn.addEventListener("click", () => {
    alert("PIN verified successfully (simulation)");
    // Next step later: redirect to success / processing page
});
