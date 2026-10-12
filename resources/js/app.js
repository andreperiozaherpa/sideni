const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

// Disable Dropzone's automatic discovery before its own DOMContentLoaded handler
// runs, so dropzones are only created by initDropzones() with our configuration.
if (window.Dropzone) {
    window.Dropzone.autoDiscover = false;
}

const dataTableLanguage = {
    search: 'Cari:',
    lengthMenu: 'Tampilkan _MENU_ data',
    info: 'Menampilkan _START_–_END_ dari _TOTAL_ data',
    infoEmpty: 'Tidak ada data',
    zeroRecords: 'Data tidak ditemukan',
    processing: 'Memuat…',
    paginate: { first: 'Awal', last: 'Akhir', next: 'Berikutnya', previous: 'Sebelumnya' },
};

function initSelect2() {
    const $ = window.jQuery;
    if (!$ || !$.fn.select2) {
        return;
    }

    $('select.select2').each(function () {
        const $select = $(this);

        if ($select.data('select2')) {
            return;
        }

        const $placeholder = $select.find('option[value=""]').first();

        $select.select2({
            width: '100%',
            placeholder: $placeholder.text().trim() || 'Pilih…',
            allowClear: $placeholder.length > 0 && ! $select.prop('required'),
            language: {
                errorLoading: () => 'Hasil tidak dapat dimuat.',
                inputTooLong: (args) => `Hapus ${args.input.length - args.maximum} karakter`,
                inputTooShort: (args) => `Masukkan ${args.minimum - args.input.length} karakter lagi`,
                loadingMore: () => 'Memuat hasil lainnya…',
                maximumSelected: (args) => `Maksimal ${args.maximum} pilihan`,
                noResults: () => 'Tidak ada hasil ditemukan',
                searching: () => 'Mencari…',
                removeAllItems: () => 'Hapus semua',
                removeItem: () => 'Hapus',
            },
        });

        $select.on('select2:open', () => {
            const focusSearch = () => {
                const input = document.querySelector('.select2-search--dropdown .select2-search__field');
                if (input && document.activeElement !== input) {
                    input.focus();
                }
            };

            focusSearch();
            window.requestAnimationFrame(focusSearch);

            const keepFocus = window.setInterval(focusSearch, 100);
            $select.one('select2:close', () => window.clearInterval(keepFocus));
        });
    });
}

function initSelect2DropdownFocus() {
    if (!window.MutationObserver) {
        return;
    }

    const focusSearchOf = (node) => {
        if (!(node instanceof HTMLElement)) {
            return;
        }

        const dropdown = node.matches('.select2-dropdown') ? node : node.querySelector('.select2-dropdown');
        if (!dropdown) {
            return;
        }

        const field = dropdown.querySelector('.select2-search__field');
        if (field) {
            field.focus();
            window.requestAnimationFrame(() => field.focus());
        }
    };

    new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach(focusSearchOf);
        });
    }).observe(document.body, { childList: true, subtree: true });
}

function refreshSelect2(element) {
    const $ = window.jQuery;
    if ($ && $(element).data('select2')) {
        $(element).trigger('change');
    }
}

window.refreshSelect2 = refreshSelect2;

function initConfirmations() {
    document.addEventListener('submit', (event) => {
        const form = event.target.closest('form[data-confirm]');
        if (!form || form.dataset.confirmed === '1') {
            return;
        }

        event.preventDefault();
        window.Swal.fire({
            title: form.dataset.confirm,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, lanjutkan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#b91c1c',
        }).then((result) => {
            if (result.isConfirmed) {
                form.dataset.confirmed = '1';
                form.submit();
            }
        });
    });
}

function initDataTables() {
    if (!window.DataTable) {
        return;
    }

    document.querySelectorAll('table.datatable').forEach((table) => {
        const dataTable = new window.DataTable(table, {
            processing: true,
            serverSide: true,
            ajax: table.dataset.url,
            columns: JSON.parse(table.dataset.columns),
            order: [],
            pageLength: 15,
            language: dataTableLanguage,
        });

        table.querySelectorAll('tfoot [data-column-search]').forEach((input) => {
            const column = input.closest('th').cellIndex;
            input.addEventListener('input', () => dataTable.column(column).search(input.value).draw());
        });
    });

    document.querySelectorAll('table.datatable-client').forEach((table) => {
        new window.DataTable(table, { order: [], pageLength: 15, language: dataTableLanguage });
    });
}

function appendToken(container, token) {
    if (!container) {
        return;
    }
    const hidden = document.createElement('input');
    hidden.type = 'hidden';
    hidden.name = 'uploaded_files[]';
    hidden.value = token;
    hidden.dataset.token = token;
    container.appendChild(hidden);
}

function removeToken(container, token) {
    container?.querySelector(`input[data-token="${token}"]`)?.remove();
}

function setDropzoneStatus(file, type, text) {
    if (!file.previewElement) {
        return;
    }

    let status = file.previewElement.querySelector('[data-dz-status]');
    if (!status) {
        status = document.createElement('div');
        status.setAttribute('data-dz-status', '');
        file.previewElement.appendChild(status);
    }

    status.className = 'dz-status ' + (type === 'success' ? 'dz-status-ok' : 'dz-status-error');
    status.textContent = text;
}

function dropzoneErrorMessage(message, xhr) {
    try {
        const data = JSON.parse(xhr?.responseText ?? '');
        const firstError = data.errors ? Object.values(data.errors)[0] : null;

        if (firstError) {
            return Array.isArray(firstError) ? firstError[0] : firstError;
        }

        if (data.message) {
            return data.message;
        }
    } catch (error) {
        // Fall through to the message Dropzone provided.
    }

    return typeof message === 'string' && message !== '' ? message : 'Berkas gagal diunggah.';
}

function initDropzones() {
    if (!window.Dropzone) {
        return;
    }

    document.querySelectorAll('[data-dropzone]').forEach((element) => {
        if (element.dropzone) {
            return;
        }

        const tokenContainer = element.parentElement.querySelector('[data-dropzone-input]');

        new window.Dropzone(element, {
            url: element.dataset.dropzone,
            maxFilesize: 20,
            acceptedFiles: element.dataset.accept || 'image/jpeg,image/png,application/pdf',
            addRemoveLinks: true,
            dictDefaultMessage: 'Tarik berkas ke sini atau klik untuk mengunggah',
            dictRemoveFile: 'Hapus',
            dictCancelUpload: 'Batal',
            dictFileTooBig: 'Berkas terlalu besar (maks. {{maxFilesize}} MB).',
            dictInvalidFileType: 'Jenis berkas tidak diizinkan.',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: (file, response) => {
                file.serverToken = response.token;
                appendToken(tokenContainer, response.token);
                setDropzoneStatus(file, 'success', 'Berhasil diunggah');
            },
            error: (file, message, xhr) => {
                setDropzoneStatus(file, 'error', 'Gagal: ' + dropzoneErrorMessage(message, xhr));
            },
            removedfile: (file) => {
                if (file.serverToken) {
                    removeToken(tokenContainer, file.serverToken);
                    fetch(`/uploads/temporary/${file.serverToken}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
                    });
                }
                file.previewElement?.remove();
            },
        });
    });
}

function initSidebar() {
    const sidebar = document.querySelector('[data-sidebar]');
    if (!sidebar) {
        return;
    }

    const overlay = document.querySelector('[data-sidebar-overlay]');
    const toggle = () => {
        sidebar.classList.toggle('-translate-x-full');
        overlay?.classList.toggle('hidden');
    };

    document.querySelectorAll('[data-sidebar-toggle]').forEach((button) => button.addEventListener('click', toggle));
    overlay?.addEventListener('click', toggle);
}

function initSidebarMenus() {
    const groups = Array.from(document.querySelectorAll('[data-nav-group]'));
    if (groups.length === 0) {
        return;
    }

    const close = (group) => {
        group.querySelector('[data-submenu]')?.classList.add('hidden');
        group.querySelector('[data-chevron]')?.classList.remove('rotate-180');
        const toggle = group.querySelector('[data-submenu-toggle]');
        toggle?.classList.remove('nav-item-active');
        toggle?.classList.add('nav-item-idle');
    };

    groups.forEach((group) => {
        const toggle = group.querySelector('[data-submenu-toggle]');
        const submenu = group.querySelector('[data-submenu]');
        if (!toggle || !submenu) {
            return;
        }

        toggle.addEventListener('click', () => {
            const isOpen = !submenu.classList.contains('hidden');
            groups.forEach(close);

            if (!isOpen) {
                submenu.classList.remove('hidden');
                group.querySelector('[data-chevron]')?.classList.add('rotate-180');
                toggle.classList.add('nav-item-active');
                toggle.classList.remove('nav-item-idle');
            }
        });
    });
}

function initProfileMenu() {
    const menu = document.querySelector('[data-profile-menu]');
    if (!menu) {
        return;
    }

    const toggle = menu.querySelector('[data-profile-toggle]');
    const dropdown = menu.querySelector('[data-profile-dropdown]');
    const chevron = menu.querySelector('[data-profile-chevron]');

    toggle?.addEventListener('click', (event) => {
        event.stopPropagation();
        dropdown?.classList.toggle('hidden');
        chevron?.classList.toggle('rotate-180');
    });

    document.addEventListener('click', (event) => {
        if (!menu.contains(event.target)) {
            dropdown?.classList.add('hidden');
            chevron?.classList.remove('rotate-180');
        }
    });
}

function initFlash() {
    if (!window.Swal || !window.__flash) {
        return;
    }

    const escapeHtml = (value) => String(value).replace(/[&<>"']/g, (char) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;',
    }[char]));

    if (window.__flash.success) {
        window.Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: window.__flash.success,
            showConfirmButton: false,
            timer: 3500,
            timerProgressBar: true,
        });
    }

    if (Array.isArray(window.__flash.errors) && window.__flash.errors.length > 0) {
        const items = window.__flash.errors.map((error) => '<li>' + escapeHtml(error) + '</li>').join('');
        window.Swal.fire({
            icon: 'error',
            title: 'Periksa kembali data Anda',
            html: '<ul style="text-align:left;margin:0;padding-left:1rem">' + items + '</ul>',
        });
    }
}

function initDateTimes() {
    if (!window.flatpickr) {
        return;
    }

    const locale = window.flatpickr.l10ns?.id ?? 'default';

    document.querySelectorAll('[data-datetime]').forEach((input) => {
        window.flatpickr(input, {
            enableTime: true,
            time_24hr: true,
            dateFormat: 'Y-m-d H:i',
            allowInput: true,
            disableMobile: true,
            locale,
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initSidebar();
    initSidebarMenus();
    initProfileMenu();
    initSelect2();
    initSelect2DropdownFocus();
    initDateTimes();
    initConfirmations();
    initDataTables();
    initDropzones();
    initFlash();
});
