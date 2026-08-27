const submitForm = (element) => element.closest('form')?.submit();

document.addEventListener('change', (event) => {
    const element = event.target.closest('[data-auto-submit]');
    if (element) submitForm(element);
});

const closeSidebar = () => {
    document.getElementById('sidebar')?.classList.remove('open');
    document.getElementById('sidebarOverlay')?.classList.remove('open');
};

document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-sidebar-toggle]');
    if (toggle) {
        document.getElementById('sidebar')?.classList.toggle('open');
        document.getElementById('sidebarOverlay')?.classList.toggle('open');
        return;
    }

    if (event.target.closest('[data-sidebar-close]')) {
        closeSidebar();
        return;
    }

    // Tapping a nav link navigates away; close the drawer so it isn't left
    // open over the new page (same-page anchors included).
    if (event.target.closest('.sidebar .nav-item')) closeSidebar();
});

// Escape closes the drawer, matching the overlay tap.
document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        closeSidebar();
        document.querySelectorAll('.notification-menu[open]').forEach((menu) => menu.removeAttribute('open'));
    }
});

document.addEventListener('click', (event) => {
    if (!event.target.closest('.notification-menu')) {
        document.querySelectorAll('.notification-menu[open]').forEach((menu) => menu.removeAttribute('open'));
    }
});

// Resizing past the mobile breakpoint restores the docked sidebar; drop the
// open state so the overlay doesn't linger over the desktop layout.
window.addEventListener('resize', () => {
    if (window.innerWidth > 768) closeSidebar();
});

document.addEventListener('submit', (event) => {
    const form = event.target.closest('[data-confirm]');
    if (form && !window.confirm(form.dataset.confirm)) event.preventDefault();
});

document.addEventListener('click', (event) => {
    const row = event.target.closest('[data-row-href]');
    if (row && !event.target.closest('a, button, form, input, select, textarea')) {
        window.location = row.dataset.rowHref;
        return;
    }

    const node = event.target.closest('.tree [data-node-id]');
    if (node) {
        document.getElementById(`children-${node.dataset.nodeId}`)?.classList.toggle('open');
        document.getElementById(`toggle-${node.dataset.nodeId}`)?.classList.toggle('open');
    }
});
