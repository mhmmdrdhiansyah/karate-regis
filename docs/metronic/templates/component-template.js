/**
 * ComponentName - Deskripsi singkat tentang komponen ini
 *
 * @author Nama Author
 * @version 1.0.0
 */

"use strict";

// Gunakan nama komponen dengan format PascalCase
var ComponentName = function() {
    // ========================================
    // PRIVATE VARIABLES
    // ========================================
    var element;
    var form;
    var table;
    var dataTable;
    var validator;

    // ========================================
    // PRIVATE FUNCTIONS
    // ========================================

    /**
     * Inisialisasi elemen DOM yang akan digunakan
     */
    var initElements = function() {
        element = document.querySelector('#kt_component_element');
        form = document.querySelector('#kt_form');
        table = document.querySelector('#kt_table');
    };

    /**
     * Inisialisasi DataTable
     */
    var initDataTable = function() {
        if (!table) return;

        dataTable = $(table).DataTable({
            info: false,
            order: [],
            pageLength: 10,
            lengthChange: false,
            autoWidth: false,
            responsive: true,
            columnDefs: [
                { orderable: false, targets: 0 },  // Checkbox column
                { orderable: false, targets: -1 }  // Actions column
            ],
            language: {
                emptyTable: "Tidak ada data tersedia",
                zeroRecords: "Tidak ada data yang ditemukan",
                paginate: {
                    next: "<span class='svg-icon svg-icon-2'><svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none'><path d='M8.59 16.59L13.17 12L8.59 7.41L10 6L16 12L10 18L8.59 16.59Z' fill='currentColor'/></svg></span>",
                    previous: "<span class='svg-icon svg-icon-2'><svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none'><path d='M15.41 7.41L10.83 12L15.41 16.59L14 18L8 12L14 6L15.41 7.41Z' fill='currentColor'/></svg></span>"
                }
            }
        });
    };

    /**
     * Handle search functionality
     */
    var handleSearch = function() {
        var filterSearch = document.querySelector('[data-kt-table-filter="search"]');
        if (!filterSearch) return;

        filterSearch.addEventListener('keyup', function(e) {
            dataTable.search(e.target.value).draw();
        });
    };

    /**
     * Handle delete rows
     */
    var handleDeleteRows = function() {
        if (!table) return;

        var deleteButtons = table.querySelectorAll('[data-kt-table-action="delete"]');

        deleteButtons.forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();

                var row = e.target.closest('tr');
                var id = e.target.closest('tr').dataset.id;

                Swal.fire({
                    text: "Apakah Anda yakin ingin menghapus data ini?",
                    icon: "warning",
                    showCancelButton: true,
                    buttonsStyling: false,
                    confirmButtonText: "Ya, hapus!",
                    cancelButtonText: "Tidak, batalkan",
                    customClass: {
                        confirmButton: "btn btn-primary",
                        cancelButton: "btn btn-active-light"
                    }
                }).then(function(result) {
                    if (result.isConfirmed) {
                        deleteItem(id, row);
                    }
                });
            });
        });
    };

    /**
     * Delete item via AJAX
     */
    var deleteItem = function(id, row) {
        // Show loading
        KTApp.blockPage({
            overlayColor: '#000000',
            state: 'primary',
            message: 'Memproses...'
        });

        // AJAX request
        fetch('/api/items/' + id, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            KTApp.unblockPage();

            if (data.success) {
                // Remove row from table
                dataTable.row($(row)).remove().draw();

                // Show success toast
                toastr.success(data.message || 'Data berhasil dihapus');
            } else {
                // Show error toast
                toastr.error(data.message || 'Terjadi kesalahan');
            }
        })
        .catch(function(error) {
            KTApp.unblockPage();
            toastr.error('Terjadi kesalahan koneksi');
            console.error('Error:', error);
        });
    };

    /**
     * Handle form validation
     */
    var handleFormValidation = function() {
        if (!form) return;

        // Init FormValidation
        validator = FormValidation.formValidation(
            form,
            {
                fields: {
                    'field1': {
                        validators: {
                            notEmpty: {
                                message: 'Field ini wajib diisi'
                            }
                        }
                    },
                    'field2': {
                        validators: {
                            notEmpty: {
                                message: 'Field ini wajib diisi'
                            },
                            emailAddress: {
                                message: 'Format email tidak valid'
                            }
                        }
                    }
                },
                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap: new FormValidation.plugins.Bootstrap5({
                        rowSelector: '.fv-row',
                        eleInvalidClass: '',
                        eleValidClass: ''
                    })
                }
            }
        );
    };

    /**
     * Handle form submission
     */
    var handleFormSubmit = function() {
        if (!form) return;

        var submitButton = form.querySelector('[data-kt-form-action="submit"]');
        if (!submitButton) return;

        submitButton.addEventListener('click', function(e) {
            e.preventDefault();

            // Validate form
            validator.validate().then(function(status) {
                if (status === 'Valid') {
                    submitForm(submitButton);
                }
            });
        });
    };

    /**
     * Submit form via AJAX
     */
    var submitForm = function(submitButton) {
        var formData = new FormData(form);
        var data = Object.fromEntries(formData.entries());

        // Show loading state
        submitButton.setAttribute('data-kt-indicator', 'on');
        submitButton.disabled = true;

        // AJAX request
        fetch('/api/items', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify(data)
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(result) {
            submitButton.removeAttribute('data-kt-indicator');
            submitButton.disabled = false;

            if (result.success) {
                // Show success message
                toastr.success(result.message || 'Data berhasil disimpan');

                // Close modal if form is in modal
                var modalElement = form.closest('.modal');
                if (modalElement) {
                    var modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) {
                        modal.hide();
                    }
                }

                // Reset form
                form.reset();
                validator.resetForm();

                // Refresh table
                if (dataTable) {
                    dataTable.ajax.reload();
                }
            } else {
                // Show error message
                toastr.error(result.message || 'Terjadi kesalahan');
            }
        })
        .catch(function(error) {
            submitButton.removeAttribute('data-kt-indicator');
            submitButton.disabled = false;
            toastr.error('Terjadi kesalahan koneksi');
            console.error('Error:', error);
        });
    };

    /**
     * Handle modal events
     */
    var handleModalEvents = function() {
        var modalElement = document.querySelector('#kt_modal_add');

        if (!modalElement) return;

        // Reset form when modal is hidden
        modalElement.addEventListener('hidden.bs.modal', function() {
            if (form) {
                form.reset();
                if (validator) {
                    validator.resetForm();
                }
            }
        });

        // Handle submit button in modal
        var submitButton = modalElement.querySelector('[data-kt-modal-action="submit"]');
        if (submitButton) {
            submitButton.addEventListener('click', function(e) {
                e.preventDefault();
                if (validator) {
                    validator.validate().then(function(status) {
                        if (status === 'Valid') {
                            submitForm(submitButton);
                        }
                    });
                }
            });
        }
    };

    /**
     * Handle export functionality
     */
    var handleExport = function() {
        var exportButton = document.querySelector('[data-kt-table-action="export"]');
        if (!exportButton) return;

        exportButton.addEventListener('click', function(e) {
            e.preventDefault();

            // Show loading
            KTApp.blockPage({
                overlayColor: '#000000',
                state: 'primary',
                message: 'Menyiapkan data...'
            });

            // Export to Excel/CSV
            // Implement export logic here

            setTimeout(function() {
                KTApp.unblockPage();
                toastr.success('Data berhasil di-export');
            }, 1000);
        });
    };

    /**
     * Handle custom events using KTUtil
     */
    var handleCustomEvents = function() {
        // Use KTUtil.on for event delegation
        KTUtil.on(document, '[data-kt-custom-action]', 'click', function(e) {
            e.preventDefault();
            var action = e.target.closest('[data-kt-custom-action]').dataset.ktCustomAction;
            var id = e.target.closest('[data-kt-custom-action]').dataset.id;

            switch(action) {
                case 'view':
                    viewItem(id);
                    break;
                case 'edit':
                    editItem(id);
                    break;
                case 'delete':
                    deleteItem(id);
                    break;
                default:
                    console.log('Unknown action:', action);
            }
        });
    };

    /**
     * View item detail
     */
    var viewItem = function(id) {
        // Implement view logic
        console.log('View item:', id);
    };

    /**
     * Edit item
     */
    var editItem = function(id) {
        // Implement edit logic
        console.log('Edit item:', id);
    };

    // ========================================
    // PUBLIC METHODS
    // ========================================
    return {
        /**
         * Initialize component
         */
        init: function() {
            initElements();
            initDataTable();
            handleSearch();
            handleDeleteRows();
            handleFormValidation();
            handleFormSubmit();
            handleModalEvents();
            handleExport();
            handleCustomEvents();
        },

        /**
         * Refresh data
         */
        refresh: function() {
            if (dataTable) {
                dataTable.ajax.reload();
            }
        },

        /**
         * Get current data
         */
        getData: function() {
            if (dataTable) {
                return dataTable.data();
            }
            return [];
        }
    };
}();

// Initialize on DOM ready
KTUtil.onDOMContentLoaded(function() {
    ComponentName.init();
});

// Export for global access (optional)
window.ComponentName = ComponentName;
