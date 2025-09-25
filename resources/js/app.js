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
            placeholder: el.dataset.placeholder || el.getAttribute('placeholder') || '',
        });
    });
};

document.addEventListener('DOMContentLoaded', () => {
    window.initTomSelect();

    const sidebarToggle = document.getElementById('sidebarToggle');
    const appShell = document.getElementById('appShell');
    const appBackdrop = document.getElementById('appBackdrop');
    const sidebar = document.getElementById('sidebar');

    if (!sidebarToggle || !appShell || !sidebar) {
        return;
    }

    const sidebarLinks = sidebar.querySelectorAll('a.list-group-item');

    const isMobile = () => window.matchMedia('(max-width: 991.98px)').matches;

    const setAriaExpanded = (expanded) => {
        sidebarToggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
    };

    const closeSidebar = () => {
        appShell.classList.remove('sidebar-open');
        document.body.classList.remove('sidebar-open');
        appBackdrop?.classList.remove('active');
        setAriaExpanded(false);
    };

    sidebarToggle.addEventListener('click', () => {
        if (isMobile()) {
            const shouldOpen = !appShell.classList.contains('sidebar-open');
            appShell.classList.toggle('sidebar-open', shouldOpen);
            document.body.classList.toggle('sidebar-open', shouldOpen);
            appBackdrop?.classList.toggle('active', shouldOpen);
            setAriaExpanded(shouldOpen);
            return;
        }

        const collapsed = appShell.classList.toggle('collapsed');
        setAriaExpanded(!collapsed);
    });

    appBackdrop?.addEventListener('click', closeSidebar);

    sidebarLinks.forEach((link) => {
        link.addEventListener('click', () => {
            if (isMobile()) {
                closeSidebar();
            }
        });
    });

    window.addEventListener('resize', () => {
        if (!isMobile()) {
            document.body.classList.remove('sidebar-open');
            appBackdrop?.classList.remove('active');
            appShell.classList.remove('sidebar-open');
            setAriaExpanded(!appShell.classList.contains('collapsed'));
            return;
        }

        setAriaExpanded(false);
    });

    setAriaExpanded(!isMobile());
});
