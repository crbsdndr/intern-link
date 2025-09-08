import './bootstrap';
import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.bootstrap5.css';

window.initTomSelect = () => {
    document.querySelectorAll('select.tom-select').forEach((el) => {
        if (el.tomselect) return;
        new TomSelect(el, {
            plugins: { dropdown_input: {} },
        });
    });
};

document.addEventListener('DOMContentLoaded', () => {
    window.initTomSelect();
});
