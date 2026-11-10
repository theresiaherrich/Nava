document.addEventListener('DOMContentLoaded', () => {
    // Filter tahun ajaran langsung dikirim saat nilai dropdown berubah
    document.querySelectorAll('[data-auto-submit]').forEach((el) => {
        el.addEventListener('change', () => {
            el.form?.submit();
        });
    });
});