<?php

use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\MeController;
use App\Http\Controllers\Api\V1\Auth\RefreshController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// API Version 1
Route::prefix('v1')->group(function (): void {
    // Authentication routes (public, no JWT required)
    Route::prefix('auth')->group(function (): void {
        Route::post('login', LoginController::class)->name('api.v1.auth.login');
        Route::post('register', \App\Http\Controllers\Api\V1\Auth\RegisterController::class)
            ->middleware('rate.limit.registration')
            ->name('api.v1.auth.register');
        Route::delete('cancel-registration', \App\Http\Controllers\Api\V1\Auth\CancelRegistrationController::class)
            ->name('api.v1.auth.cancel-registration');

        // Protected routes (JWT required)
        Route::middleware('jwt.auth')->group(function (): void {
            Route::post('refresh', RefreshController::class)->name('api.v1.auth.refresh');
            Route::post('logout', LogoutController::class)->name('api.v1.auth.logout');
            Route::get('me', MeController::class)->name('api.v1.auth.me');
        });
    });

    // Organization management routes (JWT required)
    Route::prefix('organizations')->middleware(['jwt.auth', 'jsonapi.headers', 'organization.ownership'])->group(function (): void {
        Route::get('{org}/pending-users', \App\Http\Controllers\Api\V1\Organizations\PendingUsersController::class)
            ->name('api.v1.organizations.pending-users');
        Route::post('{org}/users/{user}/approve', \App\Http\Controllers\Api\V1\Organizations\ApproveUserController::class)
            ->name('api.v1.organizations.users.approve');
        Route::post('{org}/users/{user}/reject', \App\Http\Controllers\Api\V1\Organizations\RejectUserController::class)
            ->name('api.v1.organizations.users.reject');
    });

    // Public Reference Data routes (no auth required, cached)
    Route::prefix('reference-data')->group(function (): void {
        Route::get('federation-units', \App\Http\Controllers\Api\V1\ReferenceData\FederationUnitsController::class)->name('api.v1.reference-data.federation-units');
        Route::get('race-ethnicities', \App\Http\Controllers\Api\V1\ReferenceData\RaceEthnicitiesController::class)->name('api.v1.reference-data.race-ethnicities');
        Route::get('marital-statuses', \App\Http\Controllers\Api\V1\ReferenceData\MaritalStatusesController::class)->name('api.v1.reference-data.marital-statuses');
        Route::get('benefit-programs', \App\Http\Controllers\Api\V1\ReferenceData\BenefitProgramsController::class)->name('api.v1.reference-data.benefit-programs');
    });

    // Protected API routes (JWT + JSON:API headers)
    Route::middleware(['jwt.auth', 'jsonapi.headers'])->group(function (): void {
        // Cases
        Route::get('cases', \App\Http\Controllers\Api\V1\Cases\IndexController::class)->name('api.v1.cases.index');
        Route::get('cases/{id}', \App\Http\Controllers\Api\V1\Cases\ShowController::class)->name('api.v1.cases.show');
        Route::post('cases', \App\Http\Controllers\Api\V1\Cases\StoreController::class)->name('api.v1.cases.store');

        // Families
        Route::get('families', \App\Http\Controllers\Api\V1\Families\IndexController::class)->name('api.v1.families.index');
        Route::get('families/{id}', \App\Http\Controllers\Api\V1\Families\ShowController::class)->name('api.v1.families.show');
        Route::post('families', \App\Http\Controllers\Api\V1\Families\StoreController::class)->name('api.v1.families.store');
        Route::patch('families/{id}', \App\Http\Controllers\Api\V1\Families\UpdateController::class)->name('api.v1.families.update');
        Route::delete('families/{id}', \App\Http\Controllers\Api\V1\Families\DestroyController::class)->name('api.v1.families.destroy');

        // Benefits
        Route::get('benefits', \App\Http\Controllers\Api\V1\Benefits\IndexController::class)->name('api.v1.benefits.index');
        Route::get('benefits/{id}', \App\Http\Controllers\Api\V1\Benefits\ShowController::class)->name('api.v1.benefits.show');
        Route::post('benefits', \App\Http\Controllers\Api\V1\Benefits\StoreController::class)->name('api.v1.benefits.store');

        // Persons
        Route::get('persons', \App\Http\Controllers\Api\V1\People\IndexController::class)->name('api.v1.persons.index');
        Route::get('persons/{id}', \App\Http\Controllers\Api\V1\People\ShowController::class)->name('api.v1.persons.show');
        Route::post('persons', \App\Http\Controllers\Api\V1\People\StoreController::class)->name('api.v1.persons.store');
        Route::patch('persons/{id}', \App\Http\Controllers\Api\V1\People\UpdateController::class)->name('api.v1.persons.update');
        Route::delete('persons/{id}', \App\Http\Controllers\Api\V1\People\DestroyController::class)->name('api.v1.persons.destroy');
        Route::get('persons/{id}/data-export', \App\Http\Controllers\Api\V1\People\DataExportController::class)->name('api.v1.persons.data-export');

        // Bulk Operations
        Route::post('bulk/import', \App\Http\Controllers\Api\V1\Bulk\ImportController::class)->name('api.v1.bulk.import');
        Route::post('bulk/export', \App\Http\Controllers\Api\V1\Bulk\ExportController::class)->name('api.v1.bulk.export');

        // Approval Requests
        Route::get('approval-requests', \App\Http\Controllers\Api\V1\ApprovalRequests\IndexController::class)->name('api.v1.approval-requests.index');
        Route::get('approval-requests/{approvalRequest}', \App\Http\Controllers\Api\V1\ApprovalRequests\ShowController::class)->name('api.v1.approval-requests.show');
        Route::post('approval-requests', \App\Http\Controllers\Api\V1\ApprovalRequests\StoreController::class)->name('api.v1.approval-requests.store');
        Route::post('approval-requests/{approvalRequest}/submit', \App\Http\Controllers\Api\V1\ApprovalRequests\SubmitController::class)->name('api.v1.approval-requests.submit');
        Route::post('approval-requests/{approvalRequest}/start-review', \App\Http\Controllers\Api\V1\ApprovalRequests\StartReviewController::class)->name('api.v1.approval-requests.start-review');
        Route::post('approval-requests/{approvalRequest}/approve', \App\Http\Controllers\Api\V1\ApprovalRequests\ApproveController::class)->name('api.v1.approval-requests.approve');
        Route::post('approval-requests/{approvalRequest}/reject', \App\Http\Controllers\Api\V1\ApprovalRequests\RejectController::class)->name('api.v1.approval-requests.reject');
        Route::post('approval-requests/{approvalRequest}/request-documents', \App\Http\Controllers\Api\V1\ApprovalRequests\RequestDocumentsController::class)->name('api.v1.approval-requests.request-documents');
        Route::post('approval-requests/{approvalRequest}/resubmit', \App\Http\Controllers\Api\V1\ApprovalRequests\ResubmitController::class)->name('api.v1.approval-requests.resubmit');
        Route::post('approval-requests/{approvalRequest}/cancel', \App\Http\Controllers\Api\V1\ApprovalRequests\CancelController::class)->name('api.v1.approval-requests.cancel');
        Route::post('approval-requests/{approvalRequest}/revoke', \App\Http\Controllers\Api\V1\ApprovalRequests\RevokeController::class)->name('api.v1.approval-requests.revoke');
        Route::post('approval-requests/{approvalRequest}/fast-track-approve', \App\Http\Controllers\Api\V1\ApprovalRequests\FastTrackApproveController::class)->name('api.v1.approval-requests.fast-track-approve');

        // Reports
        Route::post('reports', [\App\Http\Controllers\Api\V1\ReportController::class, 'store'])->name('api.v1.reports.store');
        Route::get('reports/history', [\App\Http\Controllers\Api\V1\ReportHistoryController::class, 'index'])->name('api.v1.reports.history');
        Route::get('reports/{report}', [\App\Http\Controllers\Api\V1\ReportController::class, 'show'])->name('api.v1.reports.show');
        Route::get('reports/{report}/download', [\App\Http\Controllers\Api\V1\ReportController::class, 'download'])->name('api.v1.reports.download');

        // Report Schedules
        Route::get('report-schedules', [\App\Http\Controllers\Api\V1\ReportScheduleController::class, 'index'])->name('api.v1.report-schedules.index');
        Route::post('report-schedules', [\App\Http\Controllers\Api\V1\ReportScheduleController::class, 'store'])->name('api.v1.report-schedules.store');
        Route::get('report-schedules/{reportSchedule}', [\App\Http\Controllers\Api\V1\ReportScheduleController::class, 'show'])->name('api.v1.report-schedules.show');
        Route::patch('report-schedules/{reportSchedule}', [\App\Http\Controllers\Api\V1\ReportScheduleController::class, 'update'])->name('api.v1.report-schedules.update');
        Route::delete('report-schedules/{reportSchedule}', [\App\Http\Controllers\Api\V1\ReportScheduleController::class, 'destroy'])->name('api.v1.report-schedules.destroy');
        Route::get('report-schedules/{reportSchedule}/executions', [\App\Http\Controllers\Api\V1\ReportScheduleController::class, 'executions'])->name('api.v1.report-schedules.executions');

        // Report Templates
        Route::get('report-templates', [\App\Http\Controllers\Api\V1\ReportTemplateController::class, 'index'])->name('api.v1.report-templates.index');
        Route::post('report-templates', [\App\Http\Controllers\Api\V1\ReportTemplateController::class, 'store'])->name('api.v1.report-templates.store');
        Route::get('report-templates/{reportTemplate}', [\App\Http\Controllers\Api\V1\ReportTemplateController::class, 'show'])->name('api.v1.report-templates.show');
        Route::patch('report-templates/{reportTemplate}', [\App\Http\Controllers\Api\V1\ReportTemplateController::class, 'update'])->name('api.v1.report-templates.update');
        Route::delete('report-templates/{reportTemplate}', [\App\Http\Controllers\Api\V1\ReportTemplateController::class, 'destroy'])->name('api.v1.report-templates.destroy');
    });
});
