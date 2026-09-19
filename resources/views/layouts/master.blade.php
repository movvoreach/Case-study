<!DOCTYPE html>
<html lang="km">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard | LMS')</title>
    <link rel="icon" type="image/png" href="{{ asset('backend/dist/img/spilogo.png') }}">
    <link rel="stylesheet" href="{{ asset('backend/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/dist/css/admin-business.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/dist/css/loading.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    @stack('styles')
    <style>
        :root {
            --sidebar-width: 300px;
            --app-font: "Khmer OS Siemreap", "Khmer OS", Arial, Helvetica, sans-serif;
            --sidebar-font: 17px;
            --sidebar-sub-font: 15px;
            --sidebar-icon: 17px;
            --sidebar-sub-icon: 13px;
            --sidebar-padding-y: 10px;
            --sidebar-padding-x: 12px;
        }

        body {
            font-family: var(--app-font);
        }

        .form-control,
        .custom-select,
        .select2-container--bootstrap4 .select2-selection {
            border: 1px solid #ced4da;
            border-radius: 4px;
            box-shadow: none;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .form-control:hover,
        .custom-select:hover,
        .select2-container--bootstrap4 .select2-selection:hover {
            border-color: #80bdff;
        }

        .form-control:focus,
        .custom-select:focus,
        .select2-container--bootstrap4.select2-container--focus .select2-selection,
        .select2-container--bootstrap4.select2-container--open .select2-selection {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            outline: 0;
        }

        .form-control[readonly] {
            background-color: #ffffff;
            cursor: default;
        }

        /* DataTables' processing indicator is styled in dist/css/loading.css (three dots). */

        .main-sidebar {
            width: var(--sidebar-width) !important;
            font-family: var(--app-font) !important;
        }

        .content-wrapper,
        .main-header,
        .main-footer {
            margin-left: var(--sidebar-width) !important;
        }

        .sidebar-collapse .main-sidebar {
            width: 4.6rem !important;
        }

        .sidebar-collapse .content-wrapper,
        .sidebar-collapse .main-header,
        .sidebar-collapse .main-footer {
            margin-left: 4.6rem !important;
        }

        /* Below 992px AdminLTE turns the sidebar into an off-canvas drawer: no content offset, full-width when open. */
        @media (max-width: 991.98px) {
            .content-wrapper,
            .main-header,
            .main-footer,
            .sidebar-collapse .content-wrapper,
            .sidebar-collapse .main-header,
            .sidebar-collapse .main-footer {
                margin-left: 0 !important;
            }

            body.sidebar-open .main-sidebar {
                width: var(--sidebar-width) !important;
            }
        }

        .main-sidebar .sidebar,
        .main-sidebar .nav-sidebar,
        .main-sidebar .nav-sidebar .nav-item {
            width: 100% !important;
        }

        .main-sidebar .brand-link {
            padding: 10px 12px !important;
        }

        .main-sidebar .brand-text {
            font-size: 18px !important;
            font-weight: 500;
        }

        .main-sidebar .nav-sidebar .nav-link {
            display: flex !important;
            align-items: center !important;
            width: 100% !important;
            padding: var(--sidebar-padding-y) var(--sidebar-padding-x) !important;
            transition: all 0.2s ease;
        }

        .main-sidebar .nav-sidebar .nav-icon {
            width: 30px;
            text-align: center;
            font-size: var(--sidebar-icon) !important;
            margin-right: 14px;
        }

        .main-sidebar .nav-sidebar .nav-link p {
            flex: 1 !important;
            margin: 0 !important;
            font-size: var(--sidebar-font) !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        .main-sidebar .nav-treeview .nav-link {
            padding-left: 55px !important;
            padding-top: 8px !important;
            padding-bottom: 8px !important;
        }

        .main-sidebar .nav-treeview .nav-icon {
            font-size: var(--sidebar-sub-icon) !important;
        }

        .main-sidebar .nav-treeview .nav-link p {
            font-size: var(--sidebar-sub-font) !important;
        }

        .main-sidebar .nav-header {
            padding: 12px 12px 6px !important;
            font-size: 13px !important;
            letter-spacing: 0;
            opacity: .75;
        }

        .main-sidebar .nav-sidebar .nav-link .right {
            margin-left: auto !important;
            font-size: 14px !important;
            opacity: .8;
        }

        .main-sidebar .nav-sidebar .nav-link:hover {
            background: rgba(255, 255, 255, 0.08);
        }

        .main-sidebar .user-panel .info a {
            font-size: 15px;
            color: #cfd8e3;
        }

        @media (max-width: 992px) {
            :root {
                --sidebar-font: 15px;
                --sidebar-sub-font: 14px;
            }
        }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        @include('partials.header')
        @include('partials.sidebar')

        <div class="content-wrapper">
            <section class="content mt-5">
                <div class="container-fluid pt-3">
                    @yield('content')
                </div>
            </section>
        </div>

        @include('partials.footer')
    </div>

    <script src="{{ asset('backend/plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('backend/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('backend/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('backend/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('backend/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('backend/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('backend/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('backend/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('backend/plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('backend/dist/js/adminlte.min.js') }}"></script>
    <script src="{{ asset('backend/dist/js/admin-business.js') }}"></script>
    <script src="{{ asset('backend/dist/js/loading.js') }}"></script>
    <script>
        $(function() {
            $('.select2bs4').select2({
                theme: 'bootstrap4'
            });

            $('.datatable').DataTable({
                responsive: true,
                lengthChange: true,
                autoWidth: false,
                pageLength: 10,
                processing: true,
                language: {
                    processing: '<span class="app-dots" aria-hidden="true"><span></span><span></span><span></span><span></span></span><span class="app-sr">កំពុងដំណើរការ...</span>',
                    search: 'ស្វែងរក:',
                    lengthMenu: 'បង្ហាញ _MENU_ ជួរ',
                    info: 'បង្ហាញ _START_ ដល់ _END_ នៃ _TOTAL_ ជួរ',
                    infoEmpty: 'មិនមានទិន្នន័យ',
                    infoFiltered: '(ចម្រាញ់ពី _MAX_ ជួរ)',
                    zeroRecords: 'រកមិនឃើញទិន្នន័យ',
                    emptyTable: 'មិនមានទិន្នន័យក្នុងតារាង',
                    paginate: {
                        first: 'ដំបូង',
                        previous: 'ថយក្រោយ',
                        next: 'បន្ទាប់',
                        last: 'ចុងក្រោយ'
                    }
                }
            });
            // Search / sort / paging feedback is handled globally by loading.js (mask over the table area).
        });
    </script>

    @stack('scripts')
</body>

</html>
