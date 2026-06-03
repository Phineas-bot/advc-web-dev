document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form[data-validate="true"]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    document.querySelectorAll('[data-confirm]').forEach((button) => {
        button.addEventListener('click', (event) => {
            const message = button.getAttribute('data-confirm') || 'Are you sure?';
            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });

    document.querySelectorAll('[data-bar-chart]').forEach((chart) => {
        const rows = JSON.parse(chart.getAttribute('data-bar-chart') || '[]');
        const max = Math.max(...rows.map((row) => Number(row.value) || 0), 1);

        chart.innerHTML = rows.map((row) => {
            const width = Math.max(((Number(row.value) || 0) / max) * 100, 6);
            return `
                <div class="bar-row">
                    <div class="fw-semibold">${row.label}</div>
                    <div class="bar-track"><div class="bar-fill" style="width:${width}%"></div></div>
                    <div class="text-end text-muted">${row.value}</div>
                </div>
            `;
        }).join('');
    });
});
