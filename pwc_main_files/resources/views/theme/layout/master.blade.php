<!DOCTYPE html>
<html lang="en">
<!--begin::Head-->

<head>
    <base href="" />
    <title>{{ App\Models\Setting::first()->title ?? '' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta charset="utf-8" />
    <meta name="description" content="" />
    <meta name="keywords" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta property="og:locale" content="en_US" />
    <meta property="og:type" content="article" />
    <meta property="og:title" content="" />
    <meta property="og:url" content="" />
    <meta property="og:site_name" content="" />
    <link rel="canonical" href="" />
    <link rel="shortcut icon" href="{{ asset('') }}{{ App\Models\Setting::first()->favicon ?? '' }}" />
    <!--begin::Fonts(mandatory for all pages)-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <!--end::Fonts-->

    <!--begin::Global Stylesheets Bundle(mandatory for all pages)-->
    <link href="{{ asset('website') }}/assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="{{ asset('website') }}/assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <!--end::Global Stylesheets Bundle-->
    <link href="{{ asset('dashboard') }}/css/dashboard.css" rel="stylesheet" />
    <link href="{{ asset('dashboard') }}/css/sidebar.css?v={{ @filemtime(public_path('dashboard/css/sidebar.css')) }}" rel="stylesheet" />
    <link href="{{ asset('dashboard') }}/css/mobile.css?v={{ @filemtime(public_path('dashboard/css/mobile.css')) }}" rel="stylesheet" />
    @stack('css')
</head>
<!--end::Head-->
<!--begin::Body-->

<body id="kt_app_body" data-kt-app-layout="dark-sidebar" data-kt-app-header-fixed="true" data-kt-app-sidebar-enabled="true" data-kt-app-sidebar-fixed="true" data-kt-app-sidebar-hoverable="true" data-kt-app-sidebar-push-header="true" data-kt-app-sidebar-push-toolbar="true" data-kt-app-sidebar-push-footer="true" data-kt-app-toolbar-enabled="true" class="app-default">
    <!--begin::Theme mode setup on page load-->
    <script>
        var defaultThemeMode = "light";
        var themeMode;
        if (document.documentElement) {
            if (document.documentElement.hasAttribute("data-bs-theme-mode")) {
                themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
            } else {
                if (localStorage.getItem("data-bs-theme") !== null) {
                    themeMode = localStorage.getItem("data-bs-theme");
                } else {
                    themeMode = defaultThemeMode;
                }
            }
            if (themeMode === "system") {
                themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
            }
            document.documentElement.setAttribute("data-bs-theme", themeMode);
        }
    </script>
    <!--end::Theme mode setup on page load-->
    <!--begin::App-->
    <div class="d-flex flex-column flex-root app-root custom_header" id="kt_app_root">
        <!--begin::Page-->
        <div class="preloader">
            <div class="cssload-speeding-wheel">
                <div class="loader_img">
                    <img src="{{ asset('website') }}/assets/images/header_logo.svg">
                </div>
                <div class="loading_icon">
                    <span><i class="fa-solid fa-circle"></i> </span>
                    <span><i class="fa-solid fa-circle"></i> </span>
                    <span><i class="fa-solid fa-circle"></i> </span>
                </div>
            </div>
        </div>
        <div class="app-page flex-column flex-column-fluid" id="kt_app_page">
            <!--begin::Header-->
            <div id="kt_app_header" class="app-header" data-kt-sticky="true" data-kt-sticky-activate="{default: true, lg: true}" data-kt-sticky-name="app-header-minimize" data-kt-sticky-offset="{default: '200px', lg: '0'}" data-kt-sticky-animation="false">
                <!--begin::Header container-->
                <div class="app-container container-fluid d-flex align-items-stretch justify-content-between" id="kt_app_header_container">
                    <!--begin::Sidebar mobile toggle-->
                    <div class="d-flex align-items-center d-lg-none ms-n3 me-1 me-md-2" title="Show sidebar menu">
                        <div class="btn btn-icon btn-active-color-primary w-35px h-35px" id="kt_app_sidebar_mobile_toggle">
                            <i class="ki-duotone ki-abstract-14 fs-2 fs-md-1">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                        </div>
                    </div>
                    <!--end::Sidebar mobile toggle-->
                    <!--begin::Mobile logo-->
{{--                    <div class="d-flex align-items-center flex-grow-1 flex-lg-grow-0">
                        <a href="#" class="d-lg-none">
                            <img alt="Logo" src="{{ asset('website') }}/assets/images/Frame.svg" class="h-30px" />
                        </a>
                    </div>--}}
                    <!--end::Mobile logo-->
                    <!--begin::Header wrapper-->
                    <div class="d-flex  justify-content-between flex-lg-grow-1" id="kt_app_header_wrapper">
                        <!--begin::Menu wrapper-->
                        @yield('navbar-title')
                        @include('theme.layout.navbar')
                        <!--end::Menu wrapper-->
                        <!--end::Menu wrapper-->
                        <!--begin::Navbar-->
                        @include('theme.layout.right_sidebar')
                        <!--end::Navbar-->
                    </div>
                    <!--end::Header wrapper-->
                </div>
                <!--end::Header container-->
            </div>
            <!--end::Header-->
            <!--begin::Wrapper-->
            <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
                @include('theme.layout.sidebar')
                <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
                    <div class="d-flex flex-column flex-column-fluid">
                        @yield('breadcrumb')
                        @yield('content')
                    </div>
                    {{--                    <div id="kt_app_footer" class="app-footer"> --}}
                    {{--                        <div --}}
                    {{--                            class="app-container container-fluid d-flex flex-column flex-md-row flex-center flex-md-stack py-3"> --}}
                    {{--                            <div class="text-dark order-2 order-md-1"> --}}
                    {{--                                <span class="text-muted fw-semibold me-1">{{ App\Models\Setting::first()->footer_text??'' }}&copy;</span> --}}
                    {{--                                <a href="https://keenthemes.com" target="_blank" --}}
                    {{--                                    class="text-gray-800 text-hover-primary">Admin</a> --}}
                    {{--                            </div> --}}

                    {{--                        </div> --}}
                    {{--                    </div> --}}
                </div>
            </div>
            <!--end::Wrapper-->
        </div>
        <!--end::Page-->
    </div>
    <div id="kt_scrolltop" class="scrolltop" data-kt-scrolltop="true">
        <i class="ki-duotone ki-arrow-up">
            <span class="path1"></span>
            <span class="path2"></span>
        </i>
    </div>
    <!--end::Scrolltop-->
    @include('theme.layout.modal')
    <!--begin::Javascript-->
    <script>
        var hostUrl = "{{ asset('website') }}/assets/";
    </script>
    <!--begin::Global Javascript Bundle(mandatory for all pages)-->
    <script src="{{ asset('website') }}/assets/plugins/global/plugins.bundle.js"></script>
    <script src="{{ asset('website') }}/assets/js/scripts.bundle.js"></script>
    <!--end::Global Javascript Bundle-->
    <script src="{{ asset('website') }}/assets/plugins/custom/datatables/datatables.bundle.js"></script>
    <!--end::Javascript-->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="text/javascript">
        @if (session()->has('message'))
            Swal.fire({
                title: "{{ session()->get('title') ?? 'success!' }}",
                html: "{{ @ucwords(preg_replace('/(?<!\ )[A-Z]/', ' $0', session()->get('message'))) }}",
                icon: "{{ session()->get('type') ?? 'success' }}",
                timer: 5000,
                buttons: false,
            });
        @endif
        @if (session()->has('flash_message'))
            Swal.fire({
                title: "{{ @ucwords(preg_replace('/(?<!\ )[A-Z]/', ' $0', session()->get('flash_message'))) }}",
                icon: "{{ session()->get('type') ?? 'success' }}",
                timer: 5000,
                buttons: false,
            });
        @endif
        //delete button confirm swal dynamic.
        function showDeleteConfirmation(button) {
            Swal.fire({
                title: 'Delete!!!',
                text: 'Are you sure you want to Delete?',
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: '#00ADEE',
                cancelButtonColor: '#E1E1E1',
                confirmButtonText: 'Yes <i class="fa-solid fa-check"></i> ',
                cancelButtonText: 'Cancel <i class="fa-solid fa-xmark"></i>'

            }).then((result) => {
                if (result.isConfirmed) {
                    button.closest('.delete-form').submit();
                }

            });
        }

        // function clientSuccessfullyCreated(button) {
        //     Swal.fire({
        //         iconHtml: '<i class="fa-regular fa-hourglass" style="color: #FFF;font-size: 27px;"></i>',
        //         title: "Client Successfully Created",
        //         text: 'Admin is currently reviewing the newly added client',
        //         showConfirmButton: false,
        //         timer: 1500
        //     });
        // }

        $(function() {
            $('[data-toggle="tooltip"]').tooltip()
        })
    </script>
    <script>
        $(document).ready(function() {
            var dataTable = $('.myTable').DataTable({
                "searching": true,
                "bLengthChange": false,
                "paging": true,
                "info": true,
                "ordering": false,
            });
            $(document).on("input", '.custom_search_box', function() {
                var searchValue = $(this).val();
                dataTable.search(searchValue).draw();
            });
        })
    </script>
    <script>
        $(window).on('load', function() {
            setTimeout(function() {
                $('.preloader').css('visibility', 'hidden');
                $('#kt_app_body').css('display', 'inline');
                $("#kt_app_body").css('overflow', 'visible');
            }, 200);
        });
    </script>
    @stack('js')


</body>
<!--end::Body-->

</html>
