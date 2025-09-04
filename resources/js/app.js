import './bootstrap';

function initSearchableSelect(select) {
    if (select.classList.contains('no-search') || select.dataset.searchable === 'false') {
        return;
    }
    if (select.dataset.searchableInit) {
        return;
    }
    select.dataset.searchableInit = '1';

    const wrapper = document.createElement('div');
    wrapper.className = 'searchable-select-wrapper';
    select.parentNode.insertBefore(wrapper, select);
    wrapper.appendChild(select);

    const input = document.createElement('input');
    input.type = 'text';
    input.className = 'form-control mb-2 searchable-select-input';
    input.placeholder = 'Search...';
    wrapper.insertBefore(input, select);

    input.addEventListener('input', () => {
        const term = input.value.toLowerCase();
        Array.from(select.options).forEach(opt => {
            opt.hidden = term && !opt.text.toLowerCase().includes(term);
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('select').forEach(initSearchableSelect);
});
