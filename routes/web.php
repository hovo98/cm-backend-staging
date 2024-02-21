<?php

use App\Http\Controllers\AdditionalFieldsConstructionController;
use App\Http\Controllers\AdditionalSponsorsController;
use App\Http\Controllers\AdminProfile;
use App\Http\Controllers\AnnualIncomeMigration;
use App\Http\Controllers\CompaniesAccounts;
use App\Http\Controllers\ConstructionProjectionMigration;
use App\Http\Controllers\DealMigrationOtherIncomeController;
use App\Http\Controllers\ExportData;
use App\Http\Controllers\Heatmap;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\MessagesController;
use App\Http\Controllers\MigrationAWSS3;
use App\Http\Controllers\MigrationFipsCode;
use App\Http\Controllers\MigrationProjectionConstruction;
use App\Http\Controllers\MigrationTotalOperatingIncome;
use App\Http\Controllers\QuoteMigrationCustomPercentage;
use App\Http\Controllers\RentRollMigration;
use App\Http\Controllers\SheetImport;
use App\Http\Controllers\SparkRedirectController;
use App\Http\Controllers\SponsorNameController;
use App\Http\Controllers\TfaController;
use App\Http\Controllers\UpTimeRobotMonitoring;
use App\Http\Controllers\UsersAccounts;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes([
    'register' => false,
    'verify' => false,
]);

Route::post('/login/verification/resend', [App\Http\Controllers\Auth\LoginController::class, 'resendCode'])->name('login.2fa.resend');
Route::post('/login/verification', [App\Http\Controllers\Auth\LoginController::class, 'verification'])->name('login.2fa');

// Route for Google Sheet authorization
Route::get('/export-data/authorize', [ExportData::class, 'authorizeGoogleClient'])->name('export-data-authorize');

Route::get('/heatmap/{key}', [Heatmap::class, 'index']);

Route::get('/maintenence/check', [MaintenanceController::class, 'index']);

Route::get('subscribe', [SparkRedirectController::class, 'index'])
    ->middleware(['signed'])
    ->name('subscribe');

Route::get('/ping-monitor', [UpTimeRobotMonitoring::class, 'index']);

// Admin Dashboard routes - only logged in admins allowed
Route::middleware(['auth', 'admin'])->group(function () {
    //Set profile as home page
    Route::view('/', 'pages.profile')->name('home');

    // Admin Profile
    Route::view('/profile', 'pages.profile')->name('profile');
    Route::post('/profile/edit-profile', [AdminProfile::class, 'editProfile'])
        ->name('edit-profile');
    Route::post('/profile/edit-password', [AdminProfile::class, 'editPassword'])
        ->name('edit-password');

    //Messages
    Route::view('/messages', 'pages.messages')->name('messages');
    Route::get('/messages', [MessagesController::class, 'messages'])->name('messages');
    Route::get('/threads/{room_id}', [MessagesController::class, 'threads'])->name('threads');

    // Users Accounts
    Route::view('/users', 'pages.user.users')->name('users');
    Route::get('/users', [UsersAccounts::class, 'index'])->name('users');
    Route::delete('/users/{id}/{flag}', [UsersAccounts::class, 'destroy']);
    Route::post('/users/{id}/giftSub', [UsersAccounts::class, 'giftSub']);
    Route::post('/users/approve/{id}', [UsersAccounts::class, 'approveBeta']);

    // Export lender's deal preferences
    Route::get('/users/export', [UsersAccounts::class, 'exportLenders'])->name('export-lenders');

    // Trashed Users
    Route::view('/users/blocked', 'pages.user.blocked')->name('users-blocked');
    Route::get('/users/blocked', [UsersAccounts::class, 'blocked'])->name('users-blocked');
    Route::post('/users/{id}', [UsersAccounts::class, 'restore']);

    // Companies Accounts
    Route::view('/companies', 'pages.company.companies')->name('companies');
    Route::get('/companies', [CompaniesAccounts::class, 'index'])->name('companies');
    Route::post('/companies/approve/{id}', [CompaniesAccounts::class, 'update']);
    //This Route is needed when we go Beta or Live launch after delete route
    Route::get('/companies/sync-company-status', [CompaniesAccounts::class, 'syncCompanyStatus'])->name('sync-company-status');

    // Manage single Company
    Route::view('/company', 'pages.company.company')->name('company');
    Route::post('/company', [CompaniesAccounts::class, 'store']);
    Route::get('/company/{id}', [CompaniesAccounts::class, 'show']);
    Route::post('/company/{id}', [CompaniesAccounts::class, 'edit']);

    //Export all data
    Route::get('/export-data', [ExportData::class, 'exportView'])->name('export-data-view');
    Route::get('/export-data/start', [ExportData::class, 'exportStart'])->name('export-data-start');
    Route::get('/export-data/process-chunk', [ExportData::class, 'processChunk'])->name('export-data-process-chunk');

    // Sheet import URLs
    Route::view('/sheet-import', 'pages.sheet-import')->name('sheet-import');
    Route::post('/sheet-import/get-data', [SheetImport::class, 'getData'])->name('sheet-import-get-data');
    Route::post('/sheet-import', [SheetImport::class, 'processChunk'])->name('sheet-import-chunk');

    //TODO delete after migration is done (02/02/2022) - this is left as example
    Route::get('/deals/other-income', [DealMigrationOtherIncomeController::class, 'updateOtherIncome']);

    //TODO delete after migration is done (14/02/2022)
    Route::get('/quotes/custom-year-percentage', [QuoteMigrationCustomPercentage::class, 'updateCustomPercentage']);

    //TODO delete after migration is done (30/03/2022)
    Route::get('/update-tfa', [TfaController::class, 'updateUsersTfa']);

    Route::get('/test', [AdditionalSponsorsController::class, 'updateFields']);

    Route::get('/sponsor-name', [SponsorNameController::class, 'updateField']);

    Route::get('/update-construction', [AdditionalFieldsConstructionController::class, 'updateFields']);

    Route::get('/rent-roll', [RentRollMigration::class, 'updateRentRoll']);

    Route::get('/rent-roll-annual-income', [AnnualIncomeMigration::class, 'updateRentRoll']);

    Route::get('/local-to-aws', [MigrationAWSS3::class, 'updateFromLocalToAws']);
    Route::get('/construction-projection', [ConstructionProjectionMigration::class, 'updateRentRoll']);

    Route::get('/construction-projection-2', [MigrationProjectionConstruction::class, 'updateConstructionProjection']);

    Route::get('/fips-code', [MigrationFipsCode::class, 'updateFips']);

    Route::get('/calculate-income', [MigrationTotalOperatingIncome::class, 'updateCalculation']);
});
