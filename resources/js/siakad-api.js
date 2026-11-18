export async function api(path, method = 'GET', body) {
    const response = await fetch('/api' + path, {
        method,
        credentials: 'same-origin',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: body === undefined ? undefined : JSON.stringify(body),
    });
    if (response.status === 401 && path !== '/auth/login') {
        window.location.assign('/login');
    }
    const type = response.headers.get('content-type') ?? '';
    if (response.ok && !type.includes('application/json')) return response;
    const result = type.includes('application/json') ? await response.json() : {};
    if (!response.ok) {
        const detail = Object.values(result.errors ?? {}).flat()[0];
        throw new Error(detail ?? result.message ?? `Permintaan gagal (HTTP ${response.status}).`);
    }
    return result;
}

export function toast(message, type = 'success') {
    window.dispatchEvent(new CustomEvent('toast', { detail: { message, type } }));
}

export async function download(path, body, filename) {
    const response = await api(path, 'POST', body);
    const url = URL.createObjectURL(await response.blob());
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    link.click();
    setTimeout(() => URL.revokeObjectURL(url), 1000);
}

document.addEventListener('submit', async (event) => {
    const form = event.target;
    if (!form.matches('form[data-auth]')) return;
    event.preventDefault();
    const submit = event.submitter;
    const status = form.querySelector('[data-error]');
    status.textContent = '';
    if (submit) submit.disabled = true;
    try {
        await api('/auth/' + form.dataset.auth, 'POST', Object.fromEntries(new FormData(form)));
        window.location.assign(form.dataset.next ?? '/dashboard');
    } catch (error) {
        status.textContent = error.message;
    } finally {
        if (submit) submit.disabled = false;
    }
});
