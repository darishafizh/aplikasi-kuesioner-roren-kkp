<!DOCTYPE html>
<html lang="en">

<head>
    @include('layouts.head')

    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    @stack('styles')

    <style>
        /* Overlay */
        .alert-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(2px);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 99999;
            opacity: 0;
            pointer-events: none;
            transition: opacity .2s ease;
        }

        .alert-overlay.show {
            opacity: 1;
            pointer-events: auto;
        }

        /* Card */
        .alert-card {
            width: 320px;
            background: #fff;
            border-radius: 14px;
            padding: 28px 24px 22px;
            text-align: center;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            transform: scale(.9) translateY(16px);
            animation: alertPopUp .28s ease forwards;
        }

        @keyframes alertPopUp {
            to {
                transform: scale(1) translateY(0);
            }
        }

        .alert-icon-circle {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
        }

        .alert-icon {
            font-size: 22px;
            color: #fff;
            font-weight: 700;
            line-height: 1;
        }

        .alert-card.success .alert-icon-circle {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            box-shadow: 0 4px 14px rgba(22, 163, 74, 0.3);
        }

        .alert-card.error .alert-icon-circle {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            box-shadow: 0 4px 14px rgba(220, 38, 38, 0.3);
        }

        .alert-title {
            font-size: 0.95rem;
            font-weight: 700;
            margin-bottom: 4px;
            font-family: 'Poppins', sans-serif;
        }

        .alert-card.success .alert-title {
            color: #16a34a;
        }

        .alert-card.error .alert-title {
            color: #dc2626;
        }

        .alert-subtitle {
            color: #64748b;
            font-size: 0.72rem;
            margin-bottom: 14px;
            line-height: 1.5;
            font-family: 'Poppins', sans-serif;
        }

        .alert-progress {
            width: 100%;
            height: 3px;
            background: #f1f5f9;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 14px;
        }

        .alert-progress-bar {
            height: 100%;
            width: 0%;
            animation: alertLoadBar 1.5s linear forwards;
        }

        .alert-card.success .alert-progress-bar {
            background: #16a34a;
        }

        .alert-card.error .alert-progress-bar {
            background: #dc2626;
        }

        @keyframes alertLoadBar {
            to {
                width: 100%;
            }
        }

        .alert-btn {
            width: 100%;
            padding: 8px 0;
            border-radius: 8px;
            border: none;
            font-size: 0.72rem;
            font-weight: 600;
            cursor: pointer;
            transition: .15s;
            font-family: 'Poppins', sans-serif;
            letter-spacing: 0.3px;
        }

        .alert-card.success .alert-btn {
            background: #f0fdf4;
            color: #16a34a;
        }

        .alert-card.success .alert-btn:hover {
            background: #dcfce7;
        }

        .alert-card.error .alert-btn {
            background: #fef2f2;
            color: #dc2626;
        }

        .alert-card.error .alert-btn:hover {
            background: #fee2e2;
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='rgba%28255,255,255,1%29)' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/ %3E%3C/svg%3E");
        }

        /* Fix DataTables Show Entries Select Overlap */
        .dataTables_length select.form-select {
            padding-right: 2.5rem !important;
            background-position: right 0.75rem center;
            width: auto;
            display: inline-block;
        }

        /* =============================== */
        /* FIX BOOTSTRAP MODAL Z-INDEX     */
        /* =============================== */
        .modal {
            z-index: 1050 !important;
        }

        .modal-backdrop {
            z-index: 1040 !important;
        }

        .modal.show .modal-dialog {
            pointer-events: auto;
        }

        .modal-dialog-centered {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: calc(100% - 1rem);
            margin: auto;
        }

        @media (min-width: 576px) {
            .modal-dialog-centered {
                min-height: calc(100% - 3.5rem);
            }
        }
        /* =============================== */
        /* GLOBAL PAGE LOADER              */
        /* =============================== */
        .page-loader {
            position: fixed;
            inset: 0;
            z-index: 999999;
            background: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: opacity 0.35s ease, visibility 0.35s ease;
        }

        .page-loader.hidden {
            opacity: 0;
            visibility: hidden;
        }

        .page-loader-spinner {
            width: 38px;
            height: 38px;
            border: 3px solid #e2e8f0;
            border-top-color: #0054A6;
            border-radius: 50%;
            animation: loaderSpin 0.7s linear infinite;
        }

        .page-loader-text {
            margin-top: 12px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.75rem;
            font-weight: 500;
            color: #64748b;
            letter-spacing: 0.3px;
        }

        @keyframes loaderSpin {
            to { transform: rotate(360deg); }
        }
    </style>

</head>

<body class="loading" data-layout-color="light" data-layout="topnav" data-leftbar-theme="dark" data-layout-mode="fluid"
    data-rightbar-onstart="true">

    {{-- 🌀 GLOBAL PAGE LOADER --}}
    {{-- Always start visible — hides on window.load after all resources are ready --}}
    <div class="page-loader" id="pageLoader">
        <div class="page-loader-spinner"></div>
        <div class="page-loader-text">Memuat...</div>
    </div>

    {{-- 🔔 GLOBAL ALERT --}}
    @if(session('success') || session('error'))
        @php
            $alertMessage = session('success') ?? session('error');
            $isImportAlert = stripos($alertMessage, 'import') !== false;
        @endphp
        <div id="customAlert" class="alert-overlay" data-auto-close="{{ $isImportAlert ? 'false' : 'true' }}">
            <div class="alert-card {{ session('success') ? 'success' : 'error' }}">
                <div class="alert-icon-circle">
                    @if(session('success'))
                        <span class="alert-icon">✓</span>
                    @else
                        <span class="alert-icon">✕</span>
                    @endif
                </div>
                <h3 class="alert-title">{{ session('success') ? 'Berhasil!' : 'Gagal!' }}</h3>
                <p class="alert-subtitle">{{ $alertMessage }}</p>

                {{-- Only show progress bar if auto-close is enabled --}}
                @if(!$isImportAlert)
                    <div class="alert-progress">
                        <div class="alert-progress-bar"></div>
                    </div>
                @endif

                <button class="alert-btn" id="alertCloseBtn">{{ session('success') ? 'OKE' : 'COBA LAGI' }}</button>
            </div>
        </div>
    @endif

    <script>
        // Alert will be shown by the page loader controller after loader hides
        (function() {
            var overlay = document.getElementById("customAlert");
            if (!overlay) return;

            var closeBtn = document.getElementById("alertCloseBtn");
            var shouldAutoClose = overlay.getAttribute('data-auto-close') === 'true';

            function closeAlert() {
                overlay.classList.remove("show");
                setTimeout(function() { overlay.remove(); }, 200);
            }

            if (closeBtn) closeBtn.addEventListener("click", closeAlert);

            // Expose showAlert globally so the loader controller can trigger it
            window.__showGlobalAlert = function() {
                overlay.classList.add("show");
                if (shouldAutoClose) {
                    setTimeout(closeAlert, 1500);
                }
            };
        })();
    </script>

    <div class="wrapper">
        <div class="content-page">
            <div class="content">
                @include('layouts.header')

                <div class="container-fluid">
                    @yield('content')
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>

    @include('layouts.foot')

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    {{-- ⚡ FIXED: Jangan inisialisasi tooltip/popover/offcanvas manual --}}
    {{-- Semua sudah di-handle oleh app.min.js / vendor.min.js --}}
    {{-- Ini mencegah "Option 'container' provided type 'null'" --}}

    {{-- Flatpickr datepicker with Indonesian locale --}}
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            flatpickr.localize(flatpickr.l10ns.id);
            document.querySelectorAll('input[type="date"]').forEach(function (el) {
                el.type = 'text';
                el.setAttribute('autocomplete', 'off');
                var fp = flatpickr(el, {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd / m / Y',
                    allowInput: true,
                    defaultDate: el.value || null,
                    onChange: function(selectedDates, dateStr, instance) {
                        var evt = new Event('change', { bubbles: true });
                        instance.element.dispatchEvent(evt);
                    }
                });
                // Wrap the visible alt-input with calendar icon
                var altInput = fp.altInput;
                if (altInput) {
                    altInput.placeholder = 'dd / mm / yyyy';
                    var wrapper = document.createElement('div');
                    wrapper.className = 'input-group';
                    var iconSpan = document.createElement('span');
                    iconSpan.className = 'input-group-text';
                    iconSpan.innerHTML = '<i class="mdi mdi-calendar"></i>';
                    iconSpan.style.cursor = 'pointer';
                    iconSpan.addEventListener('click', function() { fp.open(); });
                    altInput.parentNode.insertBefore(wrapper, altInput);
                    wrapper.appendChild(iconSpan);
                    wrapper.appendChild(altInput);
                }
            });
        });
    </script>

    {{-- Override browser HTML5 validation messages to Indonesian --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('input, select, textarea').forEach(function (el) {
                el.addEventListener('invalid', function () {
                    var v = this.validity;
                    if (v.valueMissing) {
                        this.setCustomValidity('Kolom ini wajib diisi.');
                    } else if (v.typeMismatch) {
                        if (this.type === 'email') {
                            this.setCustomValidity('Masukkan alamat email yang valid.');
                        } else if (this.type === 'url') {
                            this.setCustomValidity('Masukkan URL yang valid.');
                        } else if (this.type === 'number') {
                            this.setCustomValidity('Masukkan angka yang valid.');
                        } else {
                            this.setCustomValidity('Format input tidak sesuai.');
                        }
                    } else if (v.badInput) {
                        this.setCustomValidity('Masukkan angka yang valid.');
                    } else if (v.rangeUnderflow) {
                        this.setCustomValidity('Nilai minimal adalah ' + this.min + '.');
                    } else if (v.rangeOverflow) {
                        this.setCustomValidity('Nilai maksimal adalah ' + this.max + '.');
                    } else if (v.tooShort) {
                        this.setCustomValidity('Minimal ' + this.minLength + ' karakter. Anda memasukkan ' + this.value.length + ' karakter.');
                    } else if (v.tooLong) {
                        this.setCustomValidity('Maksimal ' + this.maxLength + ' karakter.');
                    } else if (v.stepMismatch) {
                        this.setCustomValidity('Masukkan nilai yang valid.');
                    } else if (v.patternMismatch) {
                        this.setCustomValidity(this.title || 'Format input tidak sesuai.');
                    }
                });
                el.addEventListener('input', function () {
                    this.setCustomValidity('');
                });
            });
        });
    </script>

    {{-- 🌀 PAGE LOADER CONTROLLER --}}
    <script>
        (function() {
            var loader = document.getElementById('pageLoader');
            if (!loader) return;

            function hideLoader() {
                loader.classList.add('hidden');
            }

            function showLoader() {
                loader.classList.remove('hidden');
            }

            // Always wait for window.load (all resources including CDN icons)
            // Then hide loader and show alert simultaneously
            function onPageReady() {
                hideLoader();
                // If there's a global alert, show it immediately after loader hides
                if (typeof window.__showGlobalAlert === 'function') {
                    window.__showGlobalAlert();
                }
            }

            if (document.readyState === 'complete') {
                onPageReady();
            } else {
                window.addEventListener('load', onPageReady);
            }

            // Show loader on regular link navigation
            document.addEventListener('click', function(e) {
                var link = e.target.closest('a[href]');
                if (!link) return;
                var href = link.getAttribute('href');
                if (!href || href === '#' || href.startsWith('#') || href.startsWith('javascript:')
                    || link.getAttribute('target') === '_blank'
                    || link.getAttribute('data-bs-toggle')
                    || link.classList.contains('dropdown-toggle')
                    || link.classList.contains('no-loader')
                    || link.closest('.header-dropdown')) return;
                showLoader();
            });

            // Show loader on form submissions
            document.addEventListener('submit', function(e) {
                var form = e.target;
                if (form.getAttribute('target') === '_blank' || form.classList.contains('no-loader')) return;
                showLoader();
            });

            // Show loader on page refresh (beforeunload)
            window.addEventListener('beforeunload', function() {
                showLoader();
            });

            // Handle browser back/forward (bfcache)
            window.addEventListener('pageshow', function(e) {
                if (e.persisted) hideLoader();
            });

            // 🌀 DOWNLOAD LOADER FIX:
            // Poll for a cookie that indicates the file is ready
            function getCookie(name) {
                var parts = ("; " + document.cookie).split("; " + name + "=");
                if (parts.length === 2) return parts.pop().split(";").shift();
            }

            function deleteCookie(name) {
                document.cookie = name + '=; Max-Age=-99999999; path=/;';
            }

            var downloadTimer;
            document.addEventListener('click', function(e) {
                var link = e.target.closest('a[href]');
                if (!link) return;
                // If it's a download link (no-loader or explicit download), start polling
                if (link.classList.contains('no-loader') || link.hasAttribute('download')) {
                    startDownloadPolling();
                }
            });

            document.addEventListener('submit', function(e) {
                var form = e.target;
                if (form.classList.contains('no-loader')) {
                    startDownloadPolling();
                }
            });

            function startDownloadPolling() {
                // Manually show the loader because we're starting a download that might take time
                showLoader();
                
                if (downloadTimer) clearInterval(downloadTimer);
                downloadTimer = setInterval(function() {
                    var token = getCookie('fileDownload');
                    if (token) {
                        hideLoader();
                        deleteCookie('fileDownload');
                        clearInterval(downloadTimer);
                    }
                }, 500);
            }
        })();
    </script>

    @stack('scripts')
</body>

</html>