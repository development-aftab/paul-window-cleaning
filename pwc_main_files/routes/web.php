<?php

use Illuminate\Support\Facades\{File, Route, Artisan, Auth, Mail};
use App\Http\Controllers\{
    ThemeController,
    WebsiteController,
    InvoiceController,
    RoleController,
    StaffRoutesController,
    ClientsController,
    UserController,
    CrudGeneratorController,
    QuickBooksController,
    ReportController
};

Route::get('/', [WebsiteController::class, 'index']);
Route::get('/logout', function () {
    Auth::logout();
    return redirect('/login');
});

Route::get('/home', function () {
    if (auth()->user()) {
        return redirect('dashboard_index');
    } else {
        return view('auth.login');
    }
})->middleware('auth');


Route::post('/update-price-positions', [WebsiteController::class, 'updatePricePositions'])->name('update.price.positions');

Route::get('quickbooks/connect', [QuickBooksController::class, 'connect'])->name('quickbooks.connect');
Route::get('quickbooks/callback', [QuickBooksController::class, 'callback'])->name('quickbooks.callback');
Route::get('quickbooks/test', [QuickBooksController::class, 'testConnection'])->name('quickbooks.test');
Route::get('quickbooks/customers', [QuickBooksController::class, 'listCustomers'])->name('quickbooks.customers');
Route::get('quickbooks/import', [QuickBooksController::class, 'importCustomersPage'])->name('quickbooks.import');
Route::post('quickbooks/import', [QuickBooksController::class, 'importCustomers'])->name('quickbooks.import.process');
Route::post('quickbooks/webhook', [InvoiceController::class, 'handleQuickBooksWebhook'])->name('quickbooks.webhook');

Route::get('/send-notifications', [websiteController::class, 'sendScheduleNotification'])->name('send-notifications');
Route::get('/send-weekly-deposit-report', [websiteController::class, 'sendWeeklyDepositReport'])->name('send-weekly-deposit-report');
Route::get('crud_generator', [CrudGeneratorController::class, 'crudGenerator'])->name('crud_generator');
Route::post('crud_generator_process', [CrudGeneratorController::class, 'crudGeneratorProcess'])->name('crud_generator_process');
Route::get('permissions', [ThemeController::class, 'permissions'])->name('permissions')->middleware('auth');
Auth::routes();
Route::resource("settings", "\App\Http\Controllers\SettingsController")->middleware("auth");
Route::group(['middleware' => ['auth']], function () {
    Route::resource('roles', RoleController::class);
    Route::resource('users', UserController::class);
    Route::controller(WebsiteController::class)->group(function () {
        Route::get('notifications/fetch', 'fetchNotifications')->name('notifications.fetch');
        Route::post('notifications/read', 'markAsRead')->name('notifications.markRead');
        Route::delete('notifications/{id}', 'deleteNotification')->name('notifications.delete');
        Route::get('notification', 'notification')->name('notification');
        Route::get('dashboard_index', 'dashboardIndex')->name('dashboard_index');
        Route::post('dashboard/sales-data', 'getSalesData')->name('dashboard.sales_data');
        Route::get('profile_settings', 'profileSettings')->name('profile_settings');
        Route::get('client_management', 'clientManagement')->name('client_management');
        Route::get('create_client', 'createClient')->name('create_client');
        Route::get('client-details', 'clientDetails')->name('client-details');
        Route::get('client-schedule/{id}', 'clientSchedule')->name('client-schedule');
        Route::post('client_schedule_save/{id}', 'clientScheduleSave')->name('client_schedule_save');
        Route::get('client_cash/{id}', 'clientCash')->name('client_cash');
        Route::get('view_client_cash/{id}', 'viewClientCash')->name('view_client_cash');
        Route::get('client_invoice/{id}', 'clientInvoice')->name('client_invoice');
        Route::get('view_client_invoice/{id}', 'viewClientInvoice')->name('view_client_invoice');
        Route::get('check_client_name', 'checkClientName')->name('check_client_name');
        Route::get('complete-jobs', 'completeJobs')->name('complete-jobs');
        Route::get('staff_management', 'staffManagement')->name('staff_management');
        Route::get('create_staff_member', 'createStaffMember')->name('create_staff_member');
        Route::get('create_staff_member_two', 'createStaffMemberTwo')->name('create_staff_member_two');
        Route::get('staff-request', 'staffRequest')->name('staff-request');
        Route::get('staff-testimonials', 'staffTestimonials')->name('staff-testimonials');
        Route::post('staff_accept_status/{staff_id}', 'staffAcceptStatus')->name('staff_accept_status');
        Route::get('routes', 'routes')->name('routes');
        Route::get('new_route', 'newRoute')->name('new_route');
        Route::get('route-details/{id}', 'routeDetails')->name('route-details');
        Route::get('route-details-pdf', 'routeDetailsPdf')->name('route-details-pdf');
        Route::get('quote', 'quote')->name('quote');
        Route::get('quote-details', 'quoteDetails')->name('quote-details');
        Route::post('save_payment', 'savePayment')->name('save_payment');
        Route::post('update_payment', 'updatePayment')->name('update_payment');
        Route::post('update_client_schedule', 'updateClientSchedule')->name('update_client_schedule');
        Route::get('cms', 'cms')->name('cms');
        Route::get('invoice-template-one', 'invoiceTemplateOne')->name('invoice-template-one');
        Route::get('check_email', 'checkEmail')->name('check_email');
        Route::get('route_report', 'routeReport')->name('route.report');
        Route::get('route_report_ajax', 'routeReportAjax')->name('route.report.ajax');
        Route::get('route_report_export', 'routeReportExport')->name('route.report.export');
        Route::post('route_report_review/toggle', 'toggleRouteReportReview')->name('route.report.review.toggle');
    });
    Route::controller(UserController::class)->group(function () {
        Route::get('check_password', 'checkPassword')->name('check_password');
    });
    Route::controller(\App\Http\Controllers\ContactsController::class)->group(function () {
        Route::post('contacts/bulk-delete', 'destroyBulk')->name('contacts.bulkDelete');
    });
    Route::controller(InvoiceController::class)->group(function () {
        Route::get('invoices', 'index')->name('invoices');
        Route::get('schedule/route/invoices/{route_id?}', 'scheduleRouteInvoices')->name('schedule.route.invoices');
        Route::get('client/invoice/{invoice_id?}', 'clientInvoices')->name('client.invoices');
        Route::post('create-quickbooks-invoice', 'createQuickBooksInvoice')->name('create.quickbooks.invoice');
    });

    Route::controller(\App\Http\Controllers\PayrollController::class)->group(function () {
        Route::get('payroll', 'index')->name('payroll.index');
        Route::get('payroll/{id}', 'show')->name('payroll.show');
        Route::post('payroll/{id}/bonus', 'saveBonus')->name('payroll.bonus.save');
        Route::post('payroll/{id}/extra-hours', 'saveExtraHours')->name('payroll.extra-hours.save');
        Route::post('payroll/{id}/email', 'sendEmail')->name('payroll.email');
    });

    Route::controller(\App\Http\Controllers\ReportController::class)->group(function () {
        Route::get('reports/unpaid-accounts', 'unpaidAccounts')->name('reports.unpaid');
        Route::post('reports/unpaid-accounts/mark-paid', 'markPaymentPaid')->name('reports.unpaid.mark-paid');
        Route::post('reports/unpaid-accounts/pay-with-zelle', 'payWithZelle')->name('reports.unpaid.pay-zelle');
    });
});

Route::controller(WebsiteController::class)->group(function () {
    Route::get('about_us', 'aboutUs')->name('about_us');
    Route::get('services', 'services')->name('services');
    Route::get('blogs/{id?}', 'blogs')->name('blogs');
    Route::get('contact_us', 'contactUs')->name('contact_us');
    Route::post('save_contact_us', 'saveContactUs')->name('save_contact_us');
    Route::get('check_email_quote', 'checkEmailQuote')->name('check_email_quote');
    Route::get('route_name_check', 'routeNameCheck')->name('route_name_check');
    Route::post('save_testimonial', 'saveTestimonial')->name('save_testimonial');
    Route::post('testimonial_status', 'testimonialStatus')->name('testimonial_status');
    Route::post('cms_home', 'cmsHome')->name('cms_home');
    Route::post('cms_about', 'cmsAbout')->name('cms_about');
    Route::post('cms_service', 'cmsService')->name('cms_service');
    Route::post('cms_contact', 'cmsContact')->name('cms_contact');
    Route::post('cms_blog', 'cmsBlog')->name('cms_blog');
    Route::post('sorted_schedule', 'sortedSchedule')->name('sorted_schedule');
    Route::post('requirement_status', 'requirementStatus')->name('requirement_status');
});

Route::controller(UserController::class)->group(function () {
    Route::post('profile_setting', 'profileSetting')->name('profile_setting');
    Route::post('update_password', 'updatePassword')->name('update_password');
});

Route::group(['middleware' => ['auth']], function () {
    Route::resource("staffmembers", "\App\Http\Controllers\StaffMembersController");
    Route::post("staffmembers/{id}/toggle-status", [\App\Http\Controllers\StaffMembersController::class, 'toggleStatus'])->name('staffmembers.toggle-status');
    Route::resource("staffroutes", "\App\Http\Controllers\StaffRoutesController");
    Route::post("staffroutes/{id}/toggle-status", [\App\Http\Controllers\StaffRoutesController::class, 'toggleStatus'])->name('staffroutes.toggle-status');
    Route::get('staffroute/{id}/export-schedule', [StaffRoutesController::class, 'exportSchedule'])->name('staffroute.export_schedule');
    Route::resource("assignroutes", "\App\Http\Controllers\AssignRoutesController");
    Route::get("deposits/get-expected-cash", [\App\Http\Controllers\DepositsController::class, 'getExpectedCash'])->name('deposits.get-expected-cash');
    Route::get("deposits/{id}/get-data", [\App\Http\Controllers\DepositsController::class, 'getDepositData'])->name('deposits.get-data');
    Route::post("deposits/{id}/update-status", [\App\Http\Controllers\DepositsController::class, 'updateStatus'])->name('deposits.update-status');
    Route::post("deposits/mark-deposited", [\App\Http\Controllers\DepositsController::class, 'markDeposited'])->name('deposits.mark-deposited');
    Route::get("deposits/route/{routeId}", [\App\Http\Controllers\DepositsController::class, 'routeDetail'])->name('deposits.route-detail');
    Route::post("deposits/route/{routeId}/mark-deposited", [\App\Http\Controllers\DepositsController::class, 'markRouteDeposited'])->name('deposits.mark-route-deposited');
    Route::resource("deposits", "\App\Http\Controllers\DepositsController");
    Route::resource("clients", "\App\Http\Controllers\ClientsController");
    Route::post('timelogs/{id}/end', ["\App\Http\Controllers\TimelogController", 'endTime'])->name('timelogs.end');
    Route::post('timelogs/start', ["\App\Http\Controllers\TimelogController", 'startTimer'])->name('timelogs.start');
    Route::post('timelogs/stop', ["\App\Http\Controllers\TimelogController", 'stopTimer'])->name('timelogs.stop');
    Route::get('timelogs/active', ["\App\Http\Controllers\TimelogController", 'getActiveTimers'])->name('timelogs.active');
    Route::resource('timelogs', "\App\Http\Controllers\TimelogController");
    Route::get('staff-log-hours', [\App\Http\Controllers\StaffLogHoursController::class, 'index'])->name('staff-log-hours.index');
    Route::post('staff-log-hours', [\App\Http\Controllers\StaffLogHoursController::class, 'store'])->name('staff-log-hours.store');
    Route::controller(ClientsController::class)->group(function () {
        Route::get('/export-clients', 'exportClients')->name('clients.export');
        Route::get('/check-email-phone', 'checkEmailPhone')->name('clients.check-email-phone');
        Route::patch('clients/{client}/toggle-status', 'toggleStatus')->name('clients.toggle-status');
        Route::get('/branch/{parent_id}/create', 'branchCreate')->name('branch.create');
        Route::post('/branch/{parent_id}/store', 'branchStore')->name('branch.store');
        Route::get('/branch/{id}/edit', 'branchEdit')->name('branch.edit');
        Route::put('/branch/{id}/update', 'branchUpdate')->name('branch.update');
    });
    Route::resource("clientimages", "\App\Http\Controllers\ClientImagesController");
    Route::resource("clientroutes", "\App\Http\Controllers\ClientRoutesController");
    Route::resource("assignweeks", "\App\Http\Controllers\AssignWeeksController");
    Route::resource("unavaildays", "\App\Http\Controllers\UnavailDaysController");
    Route::resource("staffrequirements", "\App\Http\Controllers\StaffRequirementsController");
    Route::post('staff-notes', [\App\Http\Controllers\StaffNotesController::class, 'store'])->name('staff-notes.store');
    Route::resource("contacts", "\App\Http\Controllers\ContactsController");
    Route::resource("contactuses", "\App\Http\Controllers\ContactusesController");
    Route::resource("contactcleanings", "\App\Http\Controllers\ContactCleaningsController");
    Route::resource("contactsidings", "\App\Http\Controllers\ContactSidingsController");
    Route::resource("contactimages", "\App\Http\Controllers\ContactImagesController");
});

Route::get('/clear-all', function () {
    Artisan::call('route:clear');
    Artisan::call('cache:clear');
    Artisan::call('view:clear');
    return '<div style="text-align:center;"> <h1 style="text-align:center;">Config Cache & Permission Cache Cleared.</h1><h3><a href="/">Go to home</a></h3></div>';
});

Route::resource("testimonials", "\App\Http\Controllers\TestimonialsController")->middleware("auth");
Route::post('testimonials/bulk-delete', [\App\Http\Controllers\TestimonialsController::class, 'destroyBulk'])->name('testimonials.bulkDelete')->middleware("auth");

Route::resource("clientpricelists", "\App\Http\Controllers\ClientPriceListsController")->middleware("auth");
Route::resource("clienttimes", "\App\Http\Controllers\ClientTimesController")->middleware("auth");

Route::resource("clientschedules", "\App\Http\Controllers\ClientSchedulesController")->middleware("auth");
Route::post('clientschedule/note/update', [\App\Http\Controllers\ClientSchedulesController::class, 'noteUpdate'])->name('clientschedule.note.update');
Route::post('clientschedule/bulk-move', [\App\Http\Controllers\ClientSchedulesController::class, 'bulkMoveSchedules'])->name('bulk.move.schedule');
Route::post('clientschedule/validate-move', [\App\Http\Controllers\ClientSchedulesController::class, 'validateScheduleMove'])->name('validate.schedule.move');
Route::post('clientschedule/move-entire-calendar', [\App\Http\Controllers\ClientSchedulesController::class, 'moveEntireCalendar'])->name('move.entire.calendar');
Route::post('clientschedule/selective-permanent-move', [\App\Http\Controllers\ClientSchedulesController::class, 'selectivePermanentMove'])->name('selective.permanent.move');
Route::post('clientschedule/update-monthly-date', [\App\Http\Controllers\ClientSchedulesController::class, 'updateMonthlyDate'])->name('client-schedules.update-monthly-date');
Route::resource("clientscheduleprices", "\App\Http\Controllers\ClientSchedulePricesController")->middleware("auth");
Route::resource("cmshomes", "\App\Http\Controllers\CmsHomesController")->middleware("auth");
Route::resource("cmsservices", "\App\Http\Controllers\CmsServicesController")->middleware("auth");
Route::resource("cmscontacts", "\App\Http\Controllers\CmsContactsController")->middleware("auth");
Route::resource("cmsblogs", "\App\Http\Controllers\CmsBlogsController")->middleware("auth");
Route::resource("cmsabouts", "\App\Http\Controllers\CmsAboutsController")->middleware("auth");

Route::resource("blogattachments", "\App\Http\Controllers\BlogAttachmentsController")->middleware("auth");
Route::resource("clientpayments", "\App\Http\Controllers\ClientPaymentsController")->middleware("auth");

Route::get('/view-logs', function (){
    $logFile = storage_path('logs/laravel.log');
    if (File::exists($logFile)) {
        $logs = File::get($logFile);
    } else {
        $logs = 'No logs available.';
    }
    return response()->make(nl2br(e($logs)), 200, [
        'Content-Type' => 'text/plain',
    ]);
});
Route::get('/clear-logs', function (){
    $logFile = storage_path('logs/laravel.log');
    if (File::exists($logFile)) {
        File::put($logFile, '');
        return response('Log file cleared successfully.', 200);
    } else {
        return response('Log file does not exist.', 404);
    }
});
