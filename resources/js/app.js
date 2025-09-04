import './bootstrap';

function initTom(root = document) {
    let elements = [];
    if (root.tagName === 'SELECT') {
        if (!root.hasAttribute('data-no-search')) {
            elements = [root];
        }
    } else {
        elements = Array.from(root.querySelectorAll('select:not([data-no-search])'));
    }

    elements.forEach((el) => {
        if (el.tomselect) return;
        new TomSelect(el);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    if (typeof TomSelect === 'undefined') return;
    initTom();

    const observer = new MutationObserver((mutations) => {
        for (const mutation of mutations) {
            mutation.addedNodes.forEach((node) => {
                if (!(node instanceof HTMLElement)) return;
                initTom(node);
            });
        }
    });

    observer.observe(document.body, { childList: true, subtree: true });
});

