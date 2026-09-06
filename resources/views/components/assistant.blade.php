@props(['page' => null])

<div id="assistant" class="no-print fixed bottom-4 right-4 z-40 flex flex-col items-end" data-page="{{ $page }}">

    <div id="assistant-panel" hidden
         class="mb-3 flex h-[32rem] w-[22rem] flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
            <div class="flex items-center gap-2">
                <span class="flex size-6 items-center justify-center rounded-md bg-indigo-600 text-xs font-bold text-white">?</span>
                <span class="text-sm font-semibold text-gray-900">Assistant</span>
            </div>
            <div class="flex items-center gap-3 text-xs">
                <button id="assistant-clear" class="text-gray-400 hover:text-gray-700">Clear</button>
                <button id="assistant-close" class="text-gray-400 hover:text-gray-700">&times;</button>
            </div>
        </div>

        <div id="assistant-log" class="flex-1 space-y-3 overflow-y-auto px-4 py-4 text-sm"></div>

        <div id="assistant-suggestions" class="flex flex-wrap gap-1.5 border-t border-gray-100 px-4 py-3">
            @foreach (['How do I create an invoice?', 'What does posting do?', 'How do I record a payment?', 'Who owes me money?'] as $s)
                <button class="assistant-chip rounded-full border border-gray-200 px-2.5 py-1 text-xs text-gray-600 hover:border-indigo-300 hover:text-indigo-700">
                    {{ $s }}
                </button>
            @endforeach
        </div>

        <form id="assistant-form" class="flex items-end gap-2 border-t border-gray-100 p-3">
            <textarea id="assistant-input" rows="1" placeholder="Ask how something works…"
                      class="max-h-24 flex-1 resize-none rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
            <button type="submit" id="assistant-send"
                    class="rounded-lg bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-500 disabled:opacity-50">
                Send
            </button>
        </form>
    </div>

    <button id="assistant-toggle"
            class="flex items-center gap-2 rounded-full bg-indigo-600 px-4 py-3 text-sm font-medium text-white shadow-lg hover:bg-indigo-500">
        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
        </svg>
        Ask AI
    </button>
</div>

@push('scripts')
<script>
(function () {
    const root = document.getElementById('assistant');
    const panel = document.getElementById('assistant-panel');
    const toggle = document.getElementById('assistant-toggle');
    const log = document.getElementById('assistant-log');
    const form = document.getElementById('assistant-form');
    const input = document.getElementById('assistant-input');
    const send = document.getElementById('assistant-send');
    const suggestions = document.getElementById('assistant-suggestions');
    const token = document.querySelector('meta[name=csrf-token]').content;
    let loaded = false;

    const esc = s => s.replace(/[&<>]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;' }[c]));
    function format(text) {
        return esc(text)
            .replace(/`([^`]+)`/g, '<code class="rounded bg-gray-100 px-1 text-[0.8em]">$1</code>')
            .replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>')
            .split('\n')
            .map(l => l.replace(/^\s*[-*]\s+/, '&bull; '))
            .join('<br>');
    }

    function bubble(role) {
        const el = document.createElement('div');
        el.className = role === 'user'
            ? 'ml-auto max-w-[85%] rounded-2xl rounded-br-sm bg-indigo-600 px-3 py-2 text-white'
            : 'mr-auto max-w-[90%] rounded-2xl rounded-bl-sm bg-gray-100 px-3 py-2 text-gray-800';
        log.appendChild(el);
        return el;
    }
    function scroll() { log.scrollTop = log.scrollHeight; }

    async function loadHistory() {
        if (loaded) return;
        loaded = true;
        try {
            const res = await fetch('{{ route('assistant.history') }}', { headers: { Accept: 'application/json' } });
            const data = await res.json();
            data.messages.forEach(m => { bubble(m.role).innerHTML = format(m.content); });
            suggestions.hidden = data.messages.length > 0;
            scroll();
        } catch (e) { /* ignore */ }
    }

    function open() { panel.hidden = false; loadHistory(); input.focus(); }
    function close() { panel.hidden = true; }
    toggle.addEventListener('click', () => (panel.hidden ? open() : close()));
    document.getElementById('assistant-close').addEventListener('click', close);

    document.getElementById('assistant-clear').addEventListener('click', () => {
        log.innerHTML = '';
        suggestions.hidden = false;
    });

    input.addEventListener('input', () => {
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 96) + 'px';
    });
    input.addEventListener('keydown', e => {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); form.requestSubmit(); }
    });
    suggestions.addEventListener('click', e => {
        if (e.target.classList.contains('assistant-chip')) {
            input.value = e.target.textContent.trim();
            form.requestSubmit();
        }
    });

    form.addEventListener('submit', async e => {
        e.preventDefault();
        const text = input.value.trim();
        if (!text) return;

        suggestions.hidden = true;
        input.value = '';
        input.style.height = 'auto';
        send.disabled = true;

        bubble('user').textContent = text;
        const reply = bubble('assistant');
        reply.textContent = '…';
        scroll();

        try {
            const res = await fetch('{{ route('assistant.message') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                body: JSON.stringify({ message: text, context: root.dataset.page || null }),
            });

            if (!res.ok) throw new Error(await res.text());

            const decoder = new TextDecoder();
            const streamReader = res.body.getReader();
            let acc = '';
            reply.textContent = '';
            while (true) {
                const { done, value } = await streamReader.read();
                if (done) break;
                acc += decoder.decode(value, { stream: true });
                reply.innerHTML = format(acc);
                scroll();
            }
        } catch (err) {
            reply.textContent = 'Sorry, something went wrong.';
        } finally {
            send.disabled = false;
            input.focus();
        }
    });
})();
</script>
@endpush
