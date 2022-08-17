<?php

Route::get('/versions', function () {
	return response()->json(['available_versions' => ['v1']]);
});

Route::group(['prefix' => 'auth'], function () {
	Route::post('/token', 'Auth\IdentityController@authenticate');
});

Route::group(['prefix' => 'v1', 'middleware' => 'api'], function () {

	Route::resource('/classes', 'Resources\ClasseController');
	Route::get('/frequencies/{id}', 'Resources\ClasseController@frequencies');
	Route::put('/frequency/{id}', 'Resources\ClasseController@updateFrequency');
	Route::put('/frequencies', 'Resources\ClasseController@updateFrequencies');
});

Route::group(['prefix' => 'v1', 'middleware' => 'api'], function () {
	Route::post('/open/schools', 'Resources\SchoolsController@openSearch');
});

Route::group(['prefix' => 'v1', 'middleware' => 'api'], function () {

	Route::group(['middleware' => 'jwt.auth'], function () { // Authenticated routes

		// Children
		Route::get('/children/map', 'Resources\ChildrenController@getMap')->middleware('can:cases.map');
		Route::get('/children/{child}/alert', 'Resources\ChildrenController@getAlert')->middleware('can:cases.view');
		Route::resource('/children', 'Resources\ChildrenController');
		Route::post('/children/search', 'Resources\ChildrenController@search')->middleware('can:cases.view');
		Route::post('/children/export', 'Resources\ChildrenController@export')->middleware('can:cases.view');
		Route::get('/children/exported/{filename}', 'Resources\ChildrenController@download_exported')->name('api.children.download_exported')->middleware('can:cases.view');
		Route::get('/children/{child}/comments', 'Resources\ChildrenController@comments')->middleware('can:cases.view');
		Route::delete('/children/{child}/comments/{comment}', 'Resources\ChildrenController@removeComment');
		Route::get('/children/{child}/comments/{comment}', 'Resources\ChildrenController@getComment');
		Route::put('/children/{child}/comments', 'Resources\ChildrenController@updateComment');
		Route::get('/children/{child}/attachments', 'Resources\ChildrenController@attachments')->middleware('can:cases.view');
		Route::get('/children/{child}/activity', 'Resources\ChildrenController@activityLog')->middleware('can:cases.view');
		Route::post('/children/{child}/comments', 'Resources\ChildrenController@addComment')->middleware('can:cases.view');
		Route::post('/children/{child}/attachments', 'Resources\ChildrenController@addAttachment')->middleware('can:cases.view');
		Route::delete('/children/{child}/attachments/{attachment}', 'Resources\ChildrenController@removeAttachment')->middleware('can:cases.manage');


		// My alerts
		Route::get('/alerts/mine', 'Resources\AlertsController@get_mine');

		// Pending alerts
		Route::get('/alerts/pending', 'Resources\AlertsController@get_pending')->middleware('can:alerts.pending');
		Route::post('/alerts/{child}/accept', 'Resources\AlertsController@accept')->middleware('can:alerts.pending');
		Route::post('/alerts/{child}/reject', 'Resources\AlertsController@reject')->middleware('can:alerts.pending');

		// Child Cases
		Route::group(['middleware' => 'can:cases.view'], function () {
			Route::post('/cases/{case}/cancel', 'Resources\CasesController@cancel');
			Route::post('/cases/{case}/reopen', 'Resources\CasesController@reopen')->middleware('can:cases.reopen');
			Route::post('/cases/{case}/transfer', 'Resources\CasesController@transfer')->middleware('can:cases.transfer');
			Route::post('/cases/{case}/request-reopen', 'Resources\CasesController@requestReopen')->middleware('can:cases.request-reopen');
			Route::post('/cases/{case}/request-transfer', 'Resources\CasesController@requestTransfer')->middleware('can:cases.request-transfer');
			Route::resource('/cases', 'Resources\CasesController');
		});

		// Requests
		Route::get('/requests/all', 'Resources\RequestsController@all')->middleware('can:requests.view');
		Route::put('/requests/{request}/reject', 'Resources\RequestsController@reject')->middleware('can:requests.reject');

		// Users
		Route::post('/users/search', 'Resources\UsersController@search')->middleware('can:users.view');
		Route::get('/users/export', 'Resources\UsersController@export')->middleware('can:users.export');
		Route::get('/users/reports', 'Resources\UsersController@reports')->middleware('can:users.reports');
		Route::get('/users/reports/download', 'Resources\UsersController@getReport')->middleware('can:users.reports');
		Route::post('/users/reports/create', 'Resources\UsersController@createReport')->middleware('can:users.reports');
		Route::get('/users/myself', 'Auth\IdentityController@identity');
		Route::post('/users/clean_history', 'Auth\IdentityController@clearHistoryUser');

		Route::put('/user/{user}/update_yourself', 'Resources\UsersController@update_yourself')->middleware('can:update.yourself');
		Route::post('/user/{user_id}/send_reactivation_mail', 'Resources\UsersController@send_reactivation_mail')->middleware('can:users.manage');

		Route::group(['middleware' => 'can:users.manage'], function () {
			Route::post('/users/{user_id}/restore', 'Resources\UsersController@restore');
			Route::resource('/users', 'Resources\UsersController');
		});

		// User Groups
		Route::get('/groups', 'Resources\GroupsController@index');
		Route::post('/groups/tenant', 'Resources\GroupsController@findByTenant');
		Route::post('/groups/uf', 'Resources\GroupsController@findByUf');
		Route::post('/groups', 'Resources\GroupsController@store')->middleware('can:groups.manage');
		Route::get('/groups/{group}', 'Resources\GroupsController@show')->middleware('can:groups.manage');
		Route::put('/groups/{group}/settings', 'Resources\GroupsController@update_settings')->middleware('can:groups.manage');
		Route::put('/groups/{group}', 'Resources\GroupsController@update')->middleware('can:groups.manage');
		Route::delete('/groups/{group}', 'Resources\GroupsController@destroy')->middleware('can:groups.manage');

		// Tenant Settings
		Route::get('/settingstenantcase/tenant/{id}', 'Resources\SettingsController@get_tenant_settings_of_case');
		Route::get('/settings/tenant', 'Resources\SettingsController@get_tenant_settings'); //->middleware('can:settings.view');
		Route::put('/settings/tenant', 'Resources\SettingsController@update_tenant_settings')->middleware('can:settings.manage');

		// Tenant Educacenso
		Route::group(['middleware' => 'can:settings.educacenso'], function () {
			Route::post('/settings/educacenso/import', 'Resources\EducacensoController@import');
			Route::get('/settings/educacenso/jobs', 'Resources\EducacensoController@list_jobs');
		});

		// Import cases from XLS file
		Route::group(['middleware' => 'can:settings.educacenso'], function () {
			Route::get('/settings/import/jobs', 'Resources\ImportXLSChildrenController@list_jobs');
			Route::post('/settings/import/xls', 'Resources\ImportXLSChildrenController@import_xls');
		});

		// Maintenance
		Route::group(['middleware' => 'can:maintenance'], function () {
			Route::get('/maintenance/import_jobs', 'Maintenance\ImportController@index');
			Route::post('/maintenance/import_jobs/new', 'Maintenance\ImportController@upload_file');
			Route::post('/maintenance/import_jobs/{job}/process', 'Maintenance\ImportController@process_job');
			Route::get('/maintenance/import_jobs/{job}', 'Maintenance\ImportController@get_job');

			Route::get('/maintenance/sms_conversations', 'Maintenance\SmsStatusController@get_conversations');
			Route::get('/maintenance/system_health', 'Maintenance\SystemHealthController@get_health');
		});

		// Case Steps
		Route::post('/steps/{step_type}/{step_id}/complete', 'Resources\StepsController@complete')->middleware('can:cases.manage');
		Route::get('/steps/{step_type}/{step_id}/assignable_users', 'Resources\StepsController@getAssignableUsers')->middleware('can:cases.manage');
		Route::post('/steps/{step_type}/{step_id}/assign_user', 'Resources\StepsController@assignUser')->middleware('can:cases.manage');
		Route::post('/steps/{step_type}/{step_id}', 'Resources\StepsController@update')->middleware('can:cases.manage');
		Route::get('/steps/{step_type}/{step_id}', 'Resources\StepsController@show')->middleware('can:cases.view');

		//Maintenance into system
		Route::post('/maintenance/{user_id}', 'Resources\MaintenanceController@assignForAdminUser')->middleware('can:cases.manage');

		// Tenant Sign-ups
		Route::any('/signups/tenants/pending', 'Tenants\TenantSignupController@get_pending');
		Route::any('/signups/tenants/export', 'Tenants\TenantSignupController@export_signups')->middleware('can:tenants.export_signups');
		Route::post('/signups/tenants/complete_setup', 'Tenants\TenantSignupController@completeSetup')->middleware('can:tenant.complete_setup');
		Route::post('/signups/tenants/{signup}/approve', 'Tenants\TenantSignupController@approve')->middleware('can:tenants.manage');
		Route::post('/signups/tenants/{signup}/reject', 'Tenants\TenantSignupController@reject')->middleware('can:tenants.manage');
		Route::post('/signups/tenants/{signup}/update_registration_data', 'Tenants\TenantSignupController@updateData')->middleware('can:tenants.manage');
		Route::post('/signups/tenants/{signup}/resend_notification', 'Tenants\TenantSignupController@resendNotification');
		Route::post('/signups/tenants/{signup}/resendmail', 'Tenants\TenantSignupController@resendMail');

		// State Sign-ups
		Route::any('/signups/state/pending', 'Tenants\StateSignupController@get_pending')->middleware('can:ufs.manage');
		Route::post('/signups/state/{signup}/approve', 'Tenants\StateSignupController@approve')->middleware('can:ufs.manage');
		Route::post('/signups/state/{signup}/reject', 'Tenants\StateSignupController@reject')->middleware('can:ufs.manage');
		Route::post('/signups/state/{signup}/update_registration_data', 'Tenants\StateSignupController@updateData')->middleware('can:ufs.manage');
		Route::post('/signups/state/{signup}/resend_notification', 'Tenants\StateSignupController@resendNotification')->middleware('can:ufs.manage');
		Route::post('/signups/state/{signup}/resendmail', 'Tenants\StateSignupController@resendMail');

		// Tenants (authenticated)
		Route::any('/tenants/all', 'Tenants\TenantsController@all')->middleware('can:tenants.view');
		Route::get('/tenants/uf', 'Tenants\TenantsController@getByUf')->middleware('can:tenants.view');
		Route::get('/tenants/export', 'Tenants\TenantsController@export')->middleware('can:tenants.export');
		Route::post('/tenants/{tenant}/cancel', 'Tenants\TenantsController@cancel')->middleware('can:tenants.manage');
		Route::get('/tenants/recent_activity', 'Tenants\TenantsController@get_recent_activity');
		Route::get('/tenants/public/uf', 'Tenants\TenantsController@getUfWithTenant');



		Route::any('/states/all', 'Resources\StatesController@all')->middleware('can:ufs.view');
		Route::get('/states/export', 'Resources\StatesController@export');

		// Schools comunications
		Route::post('/schools/educacenso/notification', 'Resources\SchoolsController@sendNotificationsEducacensoSchool');
		Route::post('/schools/frequency/notification', 'Resources\SchoolsController@sendNotificationsFrequencySchool');

		// INEP Schools
		Route::post('/schools/search', 'Resources\SchoolsController@search')->name('api.school.search');
		Route::post('/schools', 'Resources\SchoolsController@store')->middleware('can:schools.view');
		Route::get('/schools/all_educacenso', 'Resources\SchoolsController@all_educacenso')->middleware('can:settings.educacenso');
		Route::put('/schools/{id}', 'Resources\SchoolsController@update')->middleware('can:update.school');

		Route::get('/schools/all', 'Resources\SchoolsController@getAll')->middleware('can:school.list');

		// Notifications
		Route::get('/notifications/unread', 'Resources\NotificationsController@getUnread');
		Route::post('/notifications/{id}/mark_as_read', 'Resources\NotificationsController@markAsRead');

		Route::get('/user_preferences', 'Resources\PreferencesController@getSettings');
		Route::post('/user_preferences', 'Resources\PreferencesController@updateSettings');

		// Reports
		Route::post('/reports/children/by_tenant', 'Resources\ReportsController@query_children_by_tenant'); //APLICAR MIDLLEWARE NACIONAL!


		Route::post('/reports/children', 'Resources\ReportsController@query_children')->middleware('can:reports.view');
		Route::post('/reports/children_tests', 'Resources\ReportsController@query_children_tests')->middleware('can:reports.view');

		Route::post('/reports/tenants', 'Resources\ReportsController@query_tenants')->middleware('can:reports.view');
		Route::post('/reports/ufs', 'Resources\ReportsController@query_ufs')->middleware('can:reports.view');
		Route::post('/reports/signups', 'Resources\ReportsController@query_signups')->middleware('can:reports.view');
		Route::get('/reports/country_stats', 'Resources\ReportsController@country_stats');
		Route::get('/reports/state_stats', 'Resources\ReportsController@state_stats');
		Route::get('/reports/exported/{filename}', 'Resources\ReportsController@download_exported')->name('api.reports.download_exported')->middleware('can:reports.view');

		Route::get('/reports/selo', 'Resources\ReportsController@getSeloReports')->middleware('can:cities.selo_reports');
		Route::get('/reports/selo/download', 'Resources\ReportsController@getSeloReport')->middleware('can:cities.selo_reports');
		Route::post('/reports/selo/create', 'Resources\ReportsController@createSeloReport')->middleware('can:cities.selo_reports');

		Route::get('/reports/child', 'Resources\ChildrenController@list_files_exported')->middleware('can:reports.view');
		Route::get('/reports/child/download', 'Resources\ChildrenController@get_file_exported')->middleware('can:reports.view');
		Route::post('/reports/child/create', 'Resources\ChildrenController@create_report_child')->middleware('can:reports.view');

		//Reports Bar
		Route::get('/reports/city_bar', 'Bar\ReportsBar@city_bar');
		Route::get('/reports/data_rematricula_daily', 'Bar\ReportsBar@getDataRematriculaDaily');
		Route::get('/reports/ufs_by_selo', 'Bar\ReportsBar@ufsBySelo');
		Route::get('/reports/tenants_by_selo', 'Bar\ReportsBar@tenantsBySelo');
		Route::get('/reports/data_map_fusion_chart', 'Bar\ReportsBar@getDataMapFusionChart');
	});

	Route::get('/maintenance/test_error_reporting', 'Maintenance\SystemHealthController@test_error_reporting');

	// Attachment download
	// TODO: IMPORTANT: authenticate this with special timed download token
	Route::get('/attachments/download/{attachment}', 'Resources\AttachmentsController@download')->name('api.attachments.download');

	// Static data
	Route::get('/language.json', 'Resources\LanguageController@generateLanguageFile');
	Route::get('/static/static_data', 'Resources\StaticDataController@render');

	// Open data for sign-up
	Route::post('/cities/search', 'Resources\CitiesController@search')->name('api.cities.search');
	Route::post('/cities/check_availability', 'Resources\CitiesController@check_availability');
	Route::resource('/cities', 'Resources\CitiesController');
	Route::resource('/tenants', 'Tenants\TenantsController');

	// Password reset
	Route::post('/password_reset/begin', 'Auth\IdentityController@begin_password_reset');
	Route::post('/password_reset/complete', 'Auth\IdentityController@complete_password_reset');

	// Support tickets
	Route::post('/support/tickets/submit', 'Support\TicketsController@submit_ticket');

	// Tenant Sign-up
	Route::post('/signups/tenants/register', 'Tenants\TenantSignupController@register');
	Route::get('/signups/tenants/via_token/{signup}', 'Tenants\TenantSignupController@get_via_token');
	Route::post('/signups/tenants/{signup}/complete', 'Tenants\TenantSignupController@complete');
	Route::post('/signups/tenants/uploadfile', 'Tenants\TenantSignupController@uploadfile');
	Route::get('/signups/tenants/{signup}/accept', 'Tenants\TenantSignupController@accept');
	Route::get('/signups/tenants/{signup}/accepted', 'Tenants\TenantSignupController@checkAccepted');
	Route::get('/signups/tenants/mayor/by/cpf/{cpf}', 'Tenants\TenantSignupController@getMayorByCpf');
	Route::get('/signups/users/via_token/{user}', 'Tenants\TenantSignupController@get_user_via_token');
	Route::post('/signups/users/{user}/confirm', 'Tenants\TenantSignupController@confirm_user');


	// State Sign-up
	Route::get('/signups/state/{signup}/accept', 'Tenants\StateSignupController@accept');
	Route::get('/signups/state/{signup}/accepted', 'Tenants\StateSignupController@checkAccepted');
	Route::post('/signups/state/register', 'Tenants\StateSignupController@register');
	Route::post('/signups/state/check_if_available', 'Tenants\StateSignupController@checkIfAvailable');

	Route::post('/integration/lp/alert_spawn', 'Integration\AlertSpawnController@spawn_alert');
	Route::any('/integration/sms/on_receive', 'Integration\SmsConversationController@on_message_received');
	Route::get('/integration/forms/{form}', 'Integration\FormBuilderController@render_form');

	//Open data to landing page
	Route::get('/lp/report', 'LP\ReportsLandingPageController@report');
	Route::get('/lp/report/city', 'LP\ReportsLandingPageController@report_city');
	Route::get('/lp/report/list/cities', 'LP\ReportsLandingPageController@list_cities');
	Route::get('/lp/report/reach', 'LP\ReportsLandingPageController@reach');
    Route::get('/lp/report/by_dates/city', 'LP\ReportsLandingPageController@report_by_dates');

	//Open data to landing page pnad
	Route::get('/pnad/report', 'LP\ReportsPnadController@pnad');

	//Webhooks Mailgun
	Route::post('/mailgun/update', 'Mailgun\MailgunController@update');
});