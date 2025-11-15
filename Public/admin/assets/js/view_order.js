document.addEventListener("DOMContentLoaded", () => {
    const swalData = window.swalData;
    if (swalData) {
        Swal.fire({
            icon: swalData.icon,
            title: swalData.title,
            text: swalData.text,
            confirmButtonColor: '#3085d6'
        });
    }
});
