document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('form-tes');

    if (!form) {
        return;
    }

    const radios = Array.from(form.querySelectorAll('.soal-radio'));
    const questionIds = new Set(radios.map((radio) => radio.dataset.soalId));
    const answeredCount = document.getElementById('answered-count');
    const progressBar = document.getElementById('progress-bar');
    const timerDisplay = document.getElementById('timer-display');

    const updateProgress = () => {
        const answeredIds = new Set(
            radios
                .filter((radio) => radio.checked)
                .map((radio) => radio.dataset.soalId),
        );

        if (answeredCount) {
            answeredCount.textContent = answeredIds.size;
        }

        if (progressBar) {
            const progress = questionIds.size === 0
                ? 0
                : (answeredIds.size / questionIds.size) * 100;

            progressBar.style.width = `${progress}%`;
        }
    };

    form.addEventListener('change', (event) => {
        if (event.target.matches('.soal-radio')) {
            updateProgress();
        }
    });

    updateProgress();

    if (!timerDisplay) {
        return;
    }

    let remainingSeconds = 30 * 60;

    const renderTimer = () => {
        const minutes = Math.floor(remainingSeconds / 60).toString().padStart(2, '0');
        const seconds = (remainingSeconds % 60).toString().padStart(2, '0');

        timerDisplay.textContent = `${minutes}:${seconds}`;
    };

    renderTimer();

    const timerInterval = window.setInterval(() => {
        remainingSeconds -= 1;
        renderTimer();

        if (remainingSeconds <= 0) {
            window.clearInterval(timerInterval);
            form.submit();
        }
    }, 1000);
});
