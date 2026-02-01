const amountSlider = document.getElementById("loanAmount");
const amountDisplay = document.getElementById("amountDisplay");
const monthlyPayment = document.getElementById("monthlyPayment");
const termButtons = document.querySelectorAll(".term-buttons button");
const applyNow = document.getElementById("applyNow");

let selectedTerm = 12;

function calculatePayment() {
    const amount = parseInt(amountSlider.value);
    const rate = 0.045 / 12;
    const months = selectedTerm;

    const payment = (amount * rate) / (1 - Math.pow(1 + rate, -months));
    monthlyPayment.innerText = `ZMW ${payment.toFixed(2)}`;
}

amountSlider.addEventListener("input", () => {
    amountDisplay.innerText = `ZMW ${amountSlider.value}`;
    calculatePayment();
});

termButtons.forEach(btn => {
    btn.addEventListener("click", () => {
        termButtons.forEach(b => b.classList.remove("active"));
        btn.classList.add("active");
        selectedTerm = btn.dataset.term;
        calculatePayment();
    });
});

applyNow.addEventListener("click", () => {
    localStorage.setItem("loanAmount", amountSlider.value);
    localStorage.setItem("loanTerm", selectedTerm);
    window.location.href = "apply.html";
});

calculatePayment();
