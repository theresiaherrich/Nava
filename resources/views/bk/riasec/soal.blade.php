<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lembar Tes RIASEC - BK SMAN 1 Malang</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-3xl mx-auto bg-white p-8 rounded-2xl shadow-md border border-gray-200">
        <div class="flex justify-between items-center pb-4 mb-6 border-b">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Soal Tes RIASEC</h1>
                <p class="text-sm text-gray-500">Pilihlah skala 1 (Sangat Tidak Suka) s/d 5 (Sangat Suka)</p>
            </div>
            <a href="/riasec/tes-view" class="text-sm text-gray-500 hover:underline">← Batal</a>
        </div>

        <form id="formRiasec">
            <!-- Hidden input id_sesi_tes (diisi ID Sesi 1 dari seeder demo) -->
            <input type="hidden" id="id_sesi_tes" value="1">

            <div id="container-soal" class="space-y-6">
                <div class="text-center py-10">
                    <p class="text-gray-500 font-medium">Memuat data soal...</p>
                </div>
            </div>

            <button type="submit" 
                    class="mt-8 w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 rounded-xl shadow-lg transition duration-200">
                Kirim Jawaban
            </button>
        </form>
    </div>

    <script>
        // 1. Fetch soal saat halaman dibuka
        document.addEventListener('DOMContentLoaded', async () => {
            try {
                const response = await fetch('/riasec/soal');
                const result = await response.json();

                if (result.status === 'success') {
                    const container = document.getElementById('container-soal');
                    container.innerHTML = '';

                    result.data.forEach((soal, index) => {
                        const html = `
                            <div class="p-5 bg-gray-50 border border-gray-200 rounded-xl">
                                <p class="font-semibold text-gray-800 mb-4">${index + 1}. ${soal.teks}</p>
                                <div class="grid grid-cols-5 gap-2 text-center">
                                    ${[1, 2, 3, 4, 5].map(nilai => `
                                        <label class="cursor-pointer bg-white p-2.5 rounded-lg border border-gray-300 hover:border-blue-500 hover:bg-blue-50 transition block">
                                            <input type="radio" name="soal_${soal.id_soal_riasec}" value="${nilai}" data-id-soal="${soal.id_soal_riasec}" class="hidden peer" required>
                                            <span class="block text-sm font-semibold text-gray-700 peer-checked:text-blue-600">${nilai}</span>
                                        </label>
                                    `).join('')}
                                </div>
                            </div>
                        `;
                        container.innerHTML += html;
                    });
                }
            } catch (error) {
                alert('Gagal mengambil data soal!');
            }
        });

        // 2. Submit jawaban
        document.getElementById('formRiasec').addEventListener('submit', async (e) => {
            e.preventDefault();

            const idSesiTes = document.getElementById('id_sesi_tes').value;
            const inputs = document.querySelectorAll('#formRiasec input[type="radio"]:checked');
            
            const jawaban = Array.from(inputs).map(input => ({
                id_soal: parseInt(input.getAttribute('data-id-soal')),
                nilai: parseInt(input.value)
            }));

            try {
                const response = await fetch('/riasec/submit', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        id_sesi_tes: idSesiTes,
                        jawaban: jawaban
                    })
                });

                const result = await response.json();
                if (result.status === 'success') {
                    alert('Tes berhasil disimpan!');
                    window.location.href = `/riasec/hasil-view/${idSesiTes}`;
                }
            } catch (error) {
                alert('Gagal menyimpan jawaban!');
            }
        });
    </script>
</body>
</html>