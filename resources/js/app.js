import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('select.searchable').forEach((select) => {
        if (select.dataset.searchableInitialized || select.disabled) {
            return;
        }

        select.dataset.searchableInitialized = 'true';

        const options = Array.from(select.options);
        const searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.className = 'form-control mb-2';
        searchInput.placeholder = 'Search...';

        searchInput.addEventListener('input', () => {
            const term = searchInput.value.toLowerCase();
            select.innerHTML = '';
            options.forEach((option) => {
                if (option.text.toLowerCase().includes(term)) {
                    select.appendChild(option);
                }
            });
        });

        select.parentNode.insertBefore(searchInput, select);
    });
});
