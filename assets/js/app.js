(function () {
    'use strict';

    var sidebar = document.querySelector('[data-sidebar]');
    var overlay = document.querySelector('[data-sidebar-overlay]');
    var toggleButtons = document.querySelectorAll('[data-sidebar-toggle]');
    var closeButtons = document.querySelectorAll('[data-sidebar-close]');

    function openSidebar() {
        if (!sidebar || !overlay) {
            return;
        }

        sidebar.classList.remove('-translate-x-full');
        sidebar.classList.add('translate-x-0');
        overlay.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeSidebar() {
        if (!sidebar || !overlay) {
            return;
        }

        sidebar.classList.add('-translate-x-full');
        sidebar.classList.remove('translate-x-0');
        overlay.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    Array.prototype.forEach.call(toggleButtons, function (button) {
        button.addEventListener('click', openSidebar);
    });

    Array.prototype.forEach.call(closeButtons, function (button) {
        button.addEventListener('click', closeSidebar);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeSidebar();
        }
    });

    Array.prototype.forEach.call(document.querySelectorAll('form[data-confirm]'), function (form) {
        form.addEventListener('submit', function (event) {
            var message = form.getAttribute('data-confirm') || 'Are you sure?';

            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });
}());
