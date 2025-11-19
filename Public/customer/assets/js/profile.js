function showTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(tab => tab.style.display = 'none');
    document.getElementById(tabId).style.display = 'block';
    document.querySelectorAll('.tab-btns button').forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
}

document.addEventListener("DOMContentLoaded", () => {
    const urlParams = new URLSearchParams(window.location.search);

    // Password change alerts
    if (urlParams.has('pw_change')) {
        const status = urlParams.get('pw_change');

        if (status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Password Updated',
                text: 'Your password has been successfully changed.',
                confirmButtonColor: '#ffc107'
            });
        }

        if (status === 'error') {
            Swal.fire({
                icon: 'error',
                title: 'Change Password Failed',
                text: 'Your current password may be incorrect.',
                confirmButtonColor: '#dc3545'
            });
        }
    }
});
