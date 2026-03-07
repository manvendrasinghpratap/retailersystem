function showAlert(title, message, icon, options = {}) {
        // inject small custom CSS once for SweetAlert
        if (!document.getElementById('swal-custom-style')) {
            const style = document.createElement('style');
            style.id = 'swal-custom-style';
            style.innerHTML = `
                .swal2-popup.swal2-custom { max-width: 520px; border-radius: 12px; width:100%; }
                .swal2-popup.swal2-custom .swal2-title { font-size: 3rem; font-weight:600; }
                .swal2-popup.swal2-custom .swal2-html-container { font-size: 1.7rem; }
            `;
            document.head.appendChild(style);
        }

        if (window.Swal && typeof Swal.fire === 'function') {
            const cfg = Object.assign({
                title: title || '',
                html: message || '',
                icon: icon || undefined,
                showConfirmButton: true,
                confirmButtonText: options.confirmButtonText || 'OK',
                customClass: Object.assign({ popup: 'swal2-custom' }, options.customClass || {}),
                backdrop: options.backdrop !== undefined ? options.backdrop : true,
                timer: options.timer || undefined,
                allowOutsideClick: options.allowOutsideClick !== undefined ? options.allowOutsideClick : false
            }, options || {});

            // remove html/text clash
            delete cfg.text;
            return Swal.fire(cfg);
        }

        // basic fallback
        window.alert((title ? title + '\n\n' : '') + (message || ''));
    }