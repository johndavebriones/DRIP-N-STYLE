// File: cart.js
document.addEventListener("DOMContentLoaded", () => {

  // Update quantity
  document.querySelectorAll(".cart-table form[action*='CartController.php']").forEach(form => {
    form.addEventListener("submit", async e => {
      e.preventDefault();

      const formData = new FormData(form);
      const action = formData.get("action");

      try {
        const res = await fetch("../../App/Controllers/CartController.php", {
          method: "POST",
          headers: {
            "X-Requested-With": "XMLHttpRequest"
          },
          body: formData
        });

        const data = await res.json();

        if (data.success) {
          Swal.fire({
            toast: true,
            position: "bottom-end",
            icon: "success",
            title: data.message,
            showConfirmButton: false,
            timer: 1400
          });

          // Refresh page after short delay
          setTimeout(() => {
            window.location.reload();
          }, 500);

        } else {
          Swal.fire({
            toast: true,
            position: "bottom-end",
            icon: "error",
            title: data.message,
            showConfirmButton: false,
            timer: 1500
          });
        }

      } catch (err) {
        Swal.fire({
          toast: true,
          position: "bottom-end",
          icon: "error",
          title: "Something went wrong",
          showConfirmButton: false,
          timer: 1500
        });
      }
    });
  });

  // Remove item
  document.querySelectorAll(".cart-table form[action*='CartController.php'] button.btn-outline-danger").forEach(btn => {
    btn.closest("form").addEventListener("submit", async e => {
      e.preventDefault();

      const form = e.target;
      const formData = new FormData(form);

      try {
        const res = await fetch("../../App/Controllers/CartController.php", {
          method: "POST",
          headers: {
            "X-Requested-With": "XMLHttpRequest"
          },
          body: formData
        });

        const data = await res.json();

        if (data.success) {
          Swal.fire({
            toast: true,
            position: "bottom-end",
            icon: "success",
            title: data.message,
            showConfirmButton: false,
            timer: 1400
          });

          setTimeout(() => window.location.reload(), 500);
        } else {
          Swal.fire({
            toast: true,
            position: "bottom-end",
            icon: "error",
            title: data.message,
            showConfirmButton: false,
            timer: 1500
          });
        }

      } catch (err) {
        Swal.fire({
          toast: true,
          position: "bottom-end",
          icon: "error",
          title: "Something went wrong",
          showConfirmButton: false,
          timer: 1500
        });
      }
    });
  });

});
