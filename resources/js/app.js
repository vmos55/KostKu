import './bootstrap';

const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('sidebar-overlay');

function toggleSidebar() {
    sidebar?.classList.toggle('-translate-x-full');
    overlay?.classList.toggle('hidden');
}

function closeSidebar() {
    if (window.innerWidth < 1024) {
        sidebar?.classList.add('-translate-x-full');
        overlay?.classList.add('hidden');
    }
}

document.getElementById('sidebar-toggle')?.addEventListener('click', toggleSidebar);
overlay?.addEventListener('click', toggleSidebar);
sidebar?.querySelectorAll('a').forEach((link) => link.addEventListener('click', closeSidebar));
window.addEventListener('resize', () => {
    if (window.innerWidth >= 1024) overlay?.classList.add('hidden');
    else closeSidebar();
});
document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') closeSidebar();
});

document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const input = button.closest('.relative')?.querySelector('input');
        if (input) input.type = input.type === 'password' ? 'text' : 'password';
    });
});

document.querySelectorAll('[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (! window.confirm(form.dataset.confirm || 'Yakin ingin melanjutkan?')) event.preventDefault();
    });
});

document.querySelectorAll('[data-modal-open]').forEach((button) => {
    button.addEventListener('click', () => document.getElementById(button.dataset.modalOpen)?.classList.remove('hidden'));
});

document.querySelectorAll('[data-modal-close]').forEach((button) => {
    button.addEventListener('click', () => button.closest('[data-modal]')?.classList.add('hidden'));
});
