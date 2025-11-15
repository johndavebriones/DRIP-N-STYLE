document.querySelectorAll('.update-status-btn').forEach(btn => {
    btn.addEventListener('click', () => {

        const id = btn.dataset.orderId;

        const newStatus = prompt(
            "Enter new order status:\n\nPending, Confirmed, Ready for Pickup, Completed, Cancelled"
        );

        if (!newStatus) return;

        fetch("update_order_status.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `order_id=${id}&status=${encodeURIComponent(newStatus)}`
        })
        .then(res => res.text())
        .then(response => {
            if (response.trim() === "success") {
                alert("Order status updated!");
                location.reload();
            } else {
                alert("Failed to update status: " + response);
            }
        })
        .catch(err => {
            alert("Error occurred while updating: " + err);
        });
    });
});

// When clicking a row, open modal
document.addEventListener("click", function (e) {
    const row = e.target.closest(".order-row");
    if (!row) return;

    const orderId = row.dataset.orderId;

    fetch(`ajax/fetch_order_details.php?order_id=${orderId}`)
        .then(res => res.text())
        .then(html => {
            document.getElementById("orderDetailsBody").innerHTML = html;
            new bootstrap.Modal(document.getElementById("orderModal")).show();
        });
});
