<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <script src="{{ asset('js/app.js') }}" defer></script>
    
  
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Inter:400,500,600,700,800|Nunito:400,600,700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/userform.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <link href="{{ asset('css/side.css') }}?v={{ filemtime(public_path('css/side.css')) }}" rel="stylesheet">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>

  <!--DATA TABLES-->
  
  
  
  @stack('css')

    




</head>
@php
    $layoutRoleName = optional(optional(Auth::user())->role)->name;
    $layoutBodyClasses = trim('emr-shell sidebar-collapsed ' . (in_array($layoutRoleName, ['Student', 'Patient']) ? 'student-portal-shell' : ''));
@endphp
<body class="{{ $layoutBodyClasses }}">
    <div id="app" class="emr-app">
        <div class="emr-layout-frame app-layout">
            @include('includes.sidebar')
            <div class="emr-workspace main-wrapper">
                @include('includes.topbar')
                <div class="container-fluid emr-page-container page-content">
                    <main class="emr-main">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                {!! implode('', $errors->all('<div>:message</div>')) !!}
                            </div>
                        @endif

                        @yield('content')
                    </main>
                </div>
            </div>
        </div>
    </div>

    @include('includes.confirmation-modal')

    <script>
    $("#menu-toggle").click(function(e) {
        e.preventDefault();
        $("#wrapper").toggleClass("toggled");
    });
        /*Upload  Image*/
        function triggerClick(e) {
        document.querySelector('#profileImage').click();
        }
        function displayImage(e) {
            if (e.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e){
            document.querySelector('#profileDisplay').setAttribute('src', e.target.result);
            }
            reader.readAsDataURL(e.files[0]);
        }
         }

        (function () {
            var pendingAction = null;
            var confirmButton = document.getElementById('confirmationModalConfirm');

            document.addEventListener('click', function (event) {
                var trigger = event.target.closest('[data-confirm]');
                if (!trigger) {
                    return;
                }

                event.preventDefault();
                pendingAction = trigger;
                document.getElementById('confirmationModalTitle').textContent =
                    trigger.getAttribute('data-confirm-title') || 'Confirm action';
                document.getElementById('confirmationModalMessage').textContent =
                    trigger.getAttribute('data-confirm') || 'Are you sure you want to continue?';
                $('#confirmationModal').modal('show');
            });

            if (confirmButton) {
                confirmButton.addEventListener('click', function () {
                    if (!pendingAction) {
                        return;
                    }

                    var formTarget = pendingAction.getAttribute('data-confirm-form');
                    var form = formTarget
                        ? document.getElementById(formTarget)
                        : pendingAction.closest('form');
                    var href = pendingAction.getAttribute('href');
                    pendingAction = null;
                    $('#confirmationModal').modal('hide');

                    if (form) {
                        form.submit();
                    } else if (href) {
                        window.location.href = href;
                    }
                });
            }

            $(function () {
                $('.sidebar-tooltip-trigger').tooltip({
                    boundary: 'viewport',
                    container: 'body',
                    offset: '0, 12',
                    placement: 'right',
                    template: '<div class="tooltip emr-sidebar-tooltip" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>',
                    trigger: 'hover focus'
                });
                syncSidebarTooltips();

                $('[data-toggle="tooltip"]').not('.sidebar-tooltip-trigger').tooltip();
            });
        })();

        (function () {
            function matchingToggle(panelId) {
                return Array.prototype.slice.call(document.querySelectorAll('[data-prescription-toggle]'))
                    .filter(function (button) { return button.getAttribute('data-prescription-toggle') === panelId; });
            }

            function setPanelOpen(panel, open) {
                var panelRow = document.querySelector('[data-prescription-panel-row="' + panel.id + '"]');
                if (panelRow) panelRow.hidden = !open;
                panel.hidden = !open;
                panel.classList.toggle('is-open', open);
                matchingToggle(panel.id).forEach(function (button) {
                    button.setAttribute('aria-expanded', String(open));
                    button.classList.toggle('is-active', open);
                });
            }

            function printPrescription(panelId) {
                var panel = document.getElementById(panelId);
                if (!panel) return;

                setPanelOpen(panel, true);
                var printArea = panel.querySelector('[data-prescription-print-area]');
                if (!printArea) return;

                var printRoot = document.getElementById('inlinePrescriptionPrintRoot');
                if (!printRoot) {
                    printRoot = document.createElement('div');
                    printRoot.id = 'inlinePrescriptionPrintRoot';
                    document.body.appendChild(printRoot);
                }

                printRoot.innerHTML = '';
                printRoot.appendChild(printArea.cloneNode(true));
                document.body.classList.add('is-printing-prescription');

                var cleanup = function () {
                    document.body.classList.remove('is-printing-prescription');
                    printRoot.innerHTML = '';
                    window.removeEventListener('afterprint', cleanup);
                };
                window.addEventListener('afterprint', cleanup);
                window.setTimeout(function () { window.print(); }, 100);
            }

            document.addEventListener('click', function (event) {
                var modalButton = event.target.closest('[data-prescription-modal]');
                if (modalButton) {
                    var template = document.getElementById(modalButton.getAttribute('data-prescription-modal'));
                    var modal = document.getElementById('medicalRecordPrescriptionModal');
                    var modalBody = document.getElementById('medicalRecordPrescriptionModalBody');
                    var modalTitle = document.getElementById('medicalRecordPrescriptionModalTitle');
                    if (!template || !modal || !modalBody) return;

                    if (window.jQuery && jQuery.fn.tooltip) {
                        jQuery(modalButton).tooltip('hide');
                    }

                    document.querySelectorAll('[data-prescription-modal].is-active').forEach(function (button) {
                        button.classList.remove('is-active');
                    });
                    modalButton.classList.add('is-active');
                    modalBody.innerHTML = template.innerHTML;
                    if (modalTitle) modalTitle.textContent = modalButton.getAttribute('data-prescription-title') || 'Prescription';
                    if (window.jQuery && jQuery.fn.modal) {
                        jQuery(modal).modal('show');
                    }
                    return;
                }

                var toggle = event.target.closest('[data-prescription-toggle]');
                if (toggle) {
                    var panel = document.getElementById(toggle.getAttribute('data-prescription-toggle'));
                    if (!panel) return;
                    var isOpen = toggle.getAttribute('aria-expanded') === 'true';
                    setPanelOpen(panel, !isOpen);
                    if (!isOpen) panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    return;
                }

                var printButton = event.target.closest('[data-prescription-print]');
                if (printButton) {
                    printPrescription(printButton.getAttribute('data-prescription-print'));
                    return;
                }

                var closeButton = event.target.closest('[data-prescription-close]');
                if (closeButton) {
                    var closePanel = document.getElementById(closeButton.getAttribute('data-prescription-close'));
                    if (closePanel) setPanelOpen(closePanel, false);
                }
            });

            window.addEventListener('load', function () {
                document.querySelectorAll('[data-prescription-panel][data-auto-print="true"]').forEach(function (panel) {
                    setPanelOpen(panel, true);
                    window.setTimeout(function () { printPrescription(panel.id); }, 350);
                });

                var prescriptionModal = document.getElementById('medicalRecordPrescriptionModal');
                var prescriptionModalBody = document.getElementById('medicalRecordPrescriptionModalBody');
                if (prescriptionModal && window.jQuery) {
                    jQuery(prescriptionModal).on('hidden.bs.modal', function () {
                        if (prescriptionModalBody) prescriptionModalBody.innerHTML = '';
                        document.querySelectorAll('[data-prescription-modal].is-active').forEach(function (button) {
                            button.classList.remove('is-active');
                        });
                    });
                }
            });
        })();

        (function () {
            var toggle = document.getElementById('mobileMenuToggle');
            var drawer = document.getElementById('studentMobileDrawer');
            var overlay = document.getElementById('studentMobileDrawerOverlay');
            var close = document.getElementById('studentMobileDrawerClose');

            if (!toggle || !drawer || !overlay) {
                return;
            }

            function setDrawerOpen(open) {
                drawer.classList.toggle('open', open);
                overlay.classList.toggle('open', open);
                document.body.classList.toggle('mobile-drawer-open', open);
                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                drawer.setAttribute('aria-hidden', open ? 'false' : 'true');
                overlay.setAttribute('aria-hidden', open ? 'false' : 'true');
            }

            // Clear mobile UI state restored by the browser's back-forward cache.
            setDrawerOpen(false);

            toggle.addEventListener('click', function () { setDrawerOpen(true); });
            overlay.addEventListener('click', function () { setDrawerOpen(false); });
            if (close) close.addEventListener('click', function () { setDrawerOpen(false); });

            drawer.addEventListener('click', function (event) {
                if (event.target.closest('a')) {
                    setDrawerOpen(false);
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    setDrawerOpen(false);
                }
            });

            window.addEventListener('resize', function () {
                if (window.innerWidth > 767) {
                    setDrawerOpen(false);
                }
            });

            window.addEventListener('pagehide', function () { setDrawerOpen(false); });
            window.addEventListener('pageshow', function () { setDrawerOpen(false); });
            window.addEventListener('orientationchange', function () { setDrawerOpen(false); });
            document.addEventListener('visibilitychange', function () {
                if (document.hidden) setDrawerOpen(false);
            });

            $('#studentConcernModal').on('show.bs.modal', function () { setDrawerOpen(false); });
        })();

        document.addEventListener('submit', function (event) {
            var button = event.submitter || event.target.querySelector('[type="submit"], button:not([type])');
            if (!button) return;
            var loading = button.getAttribute('data-submit-loading');
            if (!loading) return;
            window.setTimeout(function () {
                button.disabled = true;
                button.textContent = loading;
                button.setAttribute('aria-busy', 'true');
            }, 0);
        });
    </script>
    @stack('js')
</body>
</html>
