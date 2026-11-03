<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Skor RIASEC - BK SMAN 1 Malang</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-2xl mx-auto bg-white p-8 rounded-2xl shadow-md border border-gray-200">
        <h1 class="text-2xl font-bold text-gray-800 mb-1">Hasil Rekapitulasi RIASEC</h1>
        <p class="text-sm text-gray-500 mb-6">Skor akumulasi berdasarkan 6 dimensi kepribadian Holland Code</p>

        <div id="container-hasil" class="space-y-3">
            <p class="text-gray-500 text-center py-6">Memuat hasil tes...</p>
        </div>

        <div class="mt-8">
            <a href="/riasec/tes-view" 
               class="block text-center bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 rounded-xl transition">
                ← Kembali ke Beranda
            </a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const pathParts = window.location.pathname.split('/');
            const idSesiTes = pathParts[pathParts.length - 1];

            try {
                const response = await fetch(`/riasec/hasil/${idSesiTes}`);
                const result = await response.json();

                if (result.status === 'success') {
                    const container = document.getElementById('container-hasil');
                    container.innerHTML = '';

                    result.data.forEach(item => {
                        const html = `
                            <div class="flex justify-between items-center p-4 bg-gray-50 rounded-xl border border-gray-200">
                                <div>
                                    <span class="inline-block bg-blue-100 text-blue-800 text-xs font-bold px-2.5 py-1 rounded-md mr-2">
                                        ${item.kode}
                                    </span>
                                    <span class="font-semibold text-gray-800">${item.nama}</span>
                                </div>
                                <span class="text-lg font-bold text-blue-700">${item.skor_mentah} Poin</span>
                            </div>
                        `;
                        container.innerHTML += html;
                    });
                }
            } catch (error) {
                alert('Gagal memuat hasil tes!');
            }
        });
    </script>
</body>
</html>