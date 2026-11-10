document.addEventListener('DOMContentLoaded', () => {
    const modal = document.querySelector('[data-modal]');
    const namaEl = modal?.querySelector('[data-modal-nama]');
    const linkEl = modal?.querySelector('[data-modal-link]');

    function bukaModal(url, nama) {
        if (!modal) return;
        namaEl.textContent = nama;
        linkEl.href = url;
        linkEl.setAttribute('download', nama);
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function tutupModal() {
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    document.querySelectorAll('[data-buka-unduh]').forEach(btn => {
        btn.addEventListener('click', () => bukaModal(btn.dataset.url, btn.dataset.nama));
    });

    document.querySelectorAll('[data-tutup-modal]').forEach(btn => {
        btn.addEventListener('click', tutupModal);
    });

    modal?.addEventListener('click', (e) => {
        if (e.target === modal) tutupModal();
    });

    document.querySelectorAll('[data-cetak]').forEach(btn => {
        btn.addEventListener('click', () => {
            const iframe = document.querySelector('[data-pratinjau]');
            try {
                (iframe?.contentWindow ?? window).print();
            } catch {
                window.print();
            }
        });
    });
});