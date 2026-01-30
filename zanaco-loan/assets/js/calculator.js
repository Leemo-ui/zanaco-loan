// Loan calculator logic
// Add your calculator functions here
const amount = document.getElementById("amount");
const amountValue = document.getElementById("amountValue");

amount.addEventListener("input", () => {
  amountValue.textContent = amount.value;
});
