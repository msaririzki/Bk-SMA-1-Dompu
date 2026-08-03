<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CmsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\InstrumentController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentImportController;
use App\Http\Controllers\StudentPortalController;
use App\Http\Controllers\ViolationCaseController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CmsController::class, 'home'])->name('home');
Route::get('/profil', fn (CmsController $controller) => $controller->publicPage('profil'))->name('public.profile');
Route::get('/tata-tertib', fn (CmsController $controller) => $controller->publicPage('tata-tertib'))->name('public.rules');
Route::get('/tata-krama', fn (CmsController $controller) => $controller->publicPage('tata-krama'))->name('public.etiquette');
Route::get('/informasi-bk', fn (CmsController $controller) => $controller->publicPage('informasi-bk'))->name('public.bk');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'staffForm'])->name('login');
    Route::post('/login', [AuthController::class, 'staffLogin'])->name('login.store');
    Route::get('/siswa/masuk', [AuthController::class, 'studentForm'])->name('student.login');
    Route::post('/siswa/masuk', [AuthController::class, 'studentLogin'])->name('student.login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/keluar', [AuthController::class, 'logout'])->name('logout');
    Route::get('/ganti-kata-sandi', [AuthController::class, 'passwordForm'])->name('password.edit');
    Route::put('/ganti-kata-sandi', [AuthController::class, 'changePassword'])->name('password.update');

    Route::middleware(['password.changed', 'role:student'])->group(function (): void {
        Route::get('/siswa', StudentPortalController::class)->name('student.portal');
    });

    Route::prefix('app')->middleware(['password.changed', 'role:super_admin,coordinator_bk,guru_bk'])->group(function (): void {
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::get('/siswa', [StudentController::class, 'index'])->name('students.index');
        Route::get('/siswa/{student}', [StudentController::class, 'show'])->name('students.show');
        Route::put('/siswa/{student}', [StudentController::class, 'update'])->name('students.update');
        Route::get('/siswa/{student}/rekap', [DocumentController::class, 'recap'])->name('students.recap');
        Route::post('/akun-siswa', [StudentController::class, 'generateAccounts'])->name('students.accounts');

        Route::get('/pelanggaran', [ViolationCaseController::class, 'index'])->name('cases.index');
        Route::get('/pelanggaran/buat', [ViolationCaseController::class, 'create'])->name('cases.create');
        Route::post('/pelanggaran', [ViolationCaseController::class, 'store'])->name('cases.store');
        Route::get('/pelanggaran/{case}', [ViolationCaseController::class, 'show'])->name('cases.show');
        Route::post('/pelanggaran/{case}/tindak-lanjut', [ViolationCaseController::class, 'followUp'])->name('cases.follow-up');
        Route::put('/pelanggaran/{case}/status', [ViolationCaseController::class, 'status'])->name('cases.status');

        Route::get('/lampiran/{attachment}', [ViolationCaseController::class, 'attachment'])
            ->middleware('signed')
            ->name('attachments.download');

        Route::get('/dokumen', [DocumentController::class, 'index'])->name('documents.index');
        Route::get('/dokumen/buat', [DocumentController::class, 'create'])->name('documents.create');
        Route::post('/dokumen', [DocumentController::class, 'store'])->name('documents.store');
        Route::get('/dokumen/{document}', [DocumentController::class, 'show'])->name('documents.show');
        Route::get('/dokumen/{document}/pdf', [DocumentController::class, 'pdf'])->name('documents.pdf');
        Route::get('/home-visit/buat', [DocumentController::class, 'homeVisitForm'])->name('home-visits.create');
        Route::post('/home-visit', [DocumentController::class, 'homeVisitStore'])->name('home-visits.store');

        Route::middleware('role:super_admin,coordinator_bk')->group(function (): void {
            Route::get('/instrumen', [InstrumentController::class, 'index'])->name('instruments.index');
            Route::post('/instrumen', [InstrumentController::class, 'store'])->name('instruments.store');
            Route::put('/instrumen/{instrument}', [InstrumentController::class, 'update'])->name('instruments.update');
            Route::put('/tingkat-pelanggaran', [InstrumentController::class, 'severities'])->name('severities.update');

            Route::get('/impor', [StudentImportController::class, 'index'])->name('imports.index');
            Route::post('/impor', [StudentImportController::class, 'store'])->name('imports.store');
            Route::get('/impor/template', [StudentImportController::class, 'template'])->name('imports.template');
            Route::get('/impor/{batch}', [StudentImportController::class, 'show'])->name('imports.show');
            Route::post('/impor/{batch}/konfirmasi', [StudentImportController::class, 'commit'])->name('imports.commit');
            Route::post('/impor/{batch}/baris/{row}/review', [StudentImportController::class, 'resolve'])->name('imports.resolve');

            Route::get('/master', [MasterDataController::class, 'index'])->name('master.index');
            Route::post('/master/tahun-pelajaran', [MasterDataController::class, 'year'])->name('master.year');
            Route::post('/master/kelas', [MasterDataController::class, 'classStore'])->name('master.class');
            Route::post('/master/guru', [MasterDataController::class, 'teacher'])->name('master.teacher');
            Route::post('/master/kelas-binaan', [MasterDataController::class, 'assign'])->name('master.assign');
            Route::put('/master/akun/{user}', [MasterDataController::class, 'account'])->name('accounts.update');
        });

        Route::middleware('role:super_admin')->group(function (): void {
            Route::get('/cms', [CmsController::class, 'index'])->name('cms.index');
            Route::get('/cms/{page}/edit', [CmsController::class, 'edit'])->name('cms.edit');
            Route::put('/cms/{page}', [CmsController::class, 'update'])->name('cms.update');
            Route::put('/cms-pengaturan', [CmsController::class, 'settings'])->name('cms.settings');
            Route::get('/audit', AuditLogController::class)->name('audit.index');
        });
    });
});
