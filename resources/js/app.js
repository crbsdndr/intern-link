import './bootstrap';
import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.bootstrap5.css';

window.initTomSelect = () => {
    document.querySelectorAll('select.tom-select').forEach((el) => {
        if (el.tomselect) return;
        const create = el.dataset.tomCreate === 'true';
        const allowEmptyOption = el.dataset.tomAllowEmpty === 'true';
        new TomSelect(el, {
            plugins: { dropdown_input: {} },
            create,
            persist: false,
            allowEmptyOption,
        });
    });
};

document.addEventListener('DOMContentLoaded', () => {
    window.initTomSelect();
});
