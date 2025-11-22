document.addEventListener("DOMContentLoaded", () => {
  const checkboxes = document.querySelectorAll(".select-item");
  const totalCell = document.querySelector("td.total");
  const checkoutBtn = document.getElementById("checkoutBtn");

  function parseSubtotal(text) {
    return parseFloat(text.replace("₱", "").replace(/,/g, "")) || 0;
  }

  function updateTotal() {
    let total = 0;
    checkboxes.forEach(cb => {
      if (cb.checked) {
        const row = cb.closest("tr");
        total += parseSubtotal(row.querySelector("td.subtotal").textContent);
      }
    });
    totalCell.textContent = "₱" + total.toLocaleString("en-PH", { minimumFractionDigits: 2 });
    checkoutBtn.disabled = !Array.from(checkboxes).some(cb => cb.checked);
  }

  // QUANTITY BUTTONS
  document.querySelectorAll(".quantity-btn").forEach(btn => {
    btn.addEventListener("click", async () => {
      const row = btn.closest("tr");
      const quantityEl = row.querySelector(".quantity-value");
      let quantity = parseInt(quantityEl.textContent);
      const itemId = btn.dataset.item;
      const action = btn.dataset.action;
      const stock = parseInt(row.dataset.stock) || Infinity;

      if (action === "increase") {
        if (quantity < stock) quantity++;
        else { alert(`Only ${stock} items available`); return; }
      } else if (action === "decrease" && quantity > 1) {
        quantity--;
      }

      quantityEl.textContent = quantity;

      const price = parseFloat(row.querySelector("td.price").textContent.replace("₱", "").replace(/,/g, ""));
      row.querySelector("td.subtotal").textContent = "₱" + (price * quantity).toLocaleString("en-PH", { minimumFractionDigits: 2 });

      updateTotal();

      try {
        const formData = new FormData();
        formData.append("action", "update");
        formData.append("item_id", itemId);
        formData.append("quantity", quantity);

        const res = await fetch("../../App/Controllers/CartController.php", {
          method: "POST",
          body: formData
        });
        const data = await res.json();
        if (!data.success) alert(data.message);
      } catch (err) {
        console.error("Failed to update quantity:", err);
      }
    });
  });

  // Uncheck all on load
  checkboxes.forEach(cb => { cb.checked = false; cb.addEventListener("change", updateTotal); });
  updateTotal();

  // CHECKOUT SUBMISSION
  const checkoutForm = document.getElementById("checkoutForm");
  checkoutForm.addEventListener("submit", (e) => {
    checkoutForm.querySelectorAll("input[name='item_ids[]']").forEach(i => i.remove());

    const selected = Array.from(checkboxes).filter(cb => cb.checked);
    if (selected.length === 0) {
      e.preventDefault();
      alert("Please select at least one item to checkout.");
      return;
    }

    selected.forEach(cb => {
      const input = document.createElement("input");
      input.type = "hidden";
      input.name = "item_ids[]";
      input.value = cb.value;
      checkoutForm.appendChild(input);
    });
  });
});