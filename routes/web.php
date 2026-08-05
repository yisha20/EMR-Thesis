<?php

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

use Illuminate\Support\Facades\Route;

Auth::routes(['register' => false]);
Route::view('/', 'auth.login');

Route::get('/patient-portal/register', 'StudentAuthController@showAccountTypeSelection')->name('patient.register.type');
Route::get('/student/register', 'StudentAuthController@showRegistrationForm')->name('student.register');
Route::post('/student/register', 'StudentAuthController@register')->name('student.register.store');

Route::view('error', 'errors.admin')->name('errors.admin');

Route::view('/about', 'about');

Route::view('/forgot-password', 'auth.forgot_password')->name('auth.forgot_password');
Route::middleware('auth')->group(function () {
    Route::get('/notifications', 'NotificationController@index')->name('notifications.index');
    Route::get('/notifications/unread', 'NotificationController@unread')->name('notifications.unread');
    Route::post('/notifications/read-all', 'NotificationController@readAll')->name('notifications.read-all');
    Route::post('/notifications/{notification}/read', 'NotificationController@read')->name('notifications.read');
    Route::get('/change-password', [App\Http\Controllers\Auth\PasswordChangeController::class, 'showChangePasswordForm'])
        ->name('password.change');
    Route::post('/change-password', [App\Http\Controllers\Auth\PasswordChangeController::class, 'updatePassword'])
        ->name('password.change.update');
});


Route::middleware('auth')->group(function () {
    Route::get('/prescriptions/{prescription}', 'PrescriptionController@show')->name('prescriptions.show');
    Route::get('/prescriptions/{prescription}/print', 'PrescriptionController@print')->name('prescriptions.print');
    Route::get('/prescriptions/{prescription}/pdf', 'PrescriptionController@pdf')->name('prescriptions.pdf');
    Route::get('/prescriptions/{prescription}/download', 'PrescriptionController@download')->name('prescriptions.download');
    Route::get('/medical-certificates/{medicalCertificate}', 'MedicalCertificateController@show')->name('medical-certificates.show');
    Route::get('/medical-certificates/{medicalCertificate}/pdf', 'MedicalCertificateController@pdf')->name('medical-certificates.pdf');
    Route::get('/dental-referrals/{dentalReferral}', 'DentalReferralController@show')->name('dental-referrals.show');

    Route::middleware('role:Student,Patient')->group(function () {
        Route::get('/patient/health-assessment', 'HealthAssessmentController@edit')->name('patient.assessment.edit');
        Route::post('/patient/health-assessment/draft', 'HealthAssessmentController@save')->name('patient.assessment.save');
        Route::post('/patient/health-assessment/submit', 'HealthAssessmentController@submit')->name('patient.assessment.submit');
        Route::middleware('assessment.complete')->group(function () {
            Route::get('/student/dashboard', 'StudentIntakeController@dashboard')->name('student.dashboard');
            Route::get('/student/complaints', 'StudentIntakeController@index')->name('student.complaints.index');
            Route::post('/student/complaints', 'StudentIntakeController@store')->name('student.complaints.store');
            Route::get('/student/complaints/{complaint}', 'StudentIntakeController@show')->name('student.complaints.show');
            Route::get('/student/medical-history', 'StudentIntakeController@medicalHistory')->name('student.medical-history');
            Route::get('/student/prescriptions', 'PrescriptionController@index')->name('student.prescriptions.index');
            Route::get('/student/profile', 'StudentIntakeController@profile')->name('student.profile');
            Route::get('/patient/dependents', 'PatientDependentController@index')->name('patient.dependents.index');
            Route::post('/patient/dependents', 'PatientDependentController@store')->name('patient.dependents.store');
            Route::get('/patient/queue-status', 'ClinicQueueController@status')->name('patient.queue.status');
            Route::post('/patient/queue/{queue}/presence', 'ClinicQueueController@presence')->name('patient.queue.presence');
            Route::post('/patient/queue/{queue}/acknowledge', 'ClinicQueueController@acknowledge')->name('patient.queue.acknowledge');
        });
    });
    Route::get('/health-assessments/{assessment}/pdf', 'HealthAssessmentController@pdf')->name('health-assessments.pdf');
    Route::get('/student/complaints/{complaint}/attachment', 'StudentIntakeController@attachment')->name('student.complaints.attachment');

    Route::middleware('role:Administrator,Doctor,Nurse,Staff')->group(function () {
    Route::get('/dental-referrals', 'DentalReferralController@index')->middleware('role:Administrator,Doctor,Nurse,Staff')->name('dental-referrals.index');
    Route::post('/student-complaints/{complaint}/dental-referral', 'DentalReferralController@store')->middleware('role:Administrator,Nurse,Staff')->name('dental-referrals.store');
    Route::get('/nurse/intakes/create', 'EmergencyIntakeController@create')->middleware('role:Administrator,Nurse,Staff')->name('emergency-intakes.create');
    Route::get('/nurse/intakes/search', 'EmergencyIntakeController@search')->middleware('role:Administrator,Nurse,Staff')->name('emergency-intakes.search');
    Route::post('/nurse/intakes', 'EmergencyIntakeController@store')->middleware('role:Administrator,Nurse,Staff')->name('emergency-intakes.store');
    Route::get('/emergency-intakes/{emergencyIntake}', 'EmergencyIntakeController@show')->name('emergency-intakes.show');
    Route::post('/emergency-intakes/{emergencyIntake}/acknowledge', 'EmergencyIntakeController@acknowledge')->middleware('role:Doctor')->name('emergency-intakes.acknowledge');
    Route::get('/patients/{patient}/merge', 'PatientMergeController@create')->middleware('role:Administrator,Nurse')->name('patient-merges.create');
    Route::post('/patients/{patient}/merge', 'PatientMergeController@store')->middleware('role:Administrator,Nurse')->name('patient-merges.store');
    Route::get('/consultations/{consultation}/medical-certificate', 'MedicalCertificateController@create')->middleware('role:Doctor')->name('consultations.medical-certificates.create');
    Route::post('/consultations/{consultation}/medical-certificate', 'MedicalCertificateController@store')->middleware('role:Doctor')->name('consultations.medical-certificates.store');
    Route::get('/medical-certificates/{medicalCertificate}/edit', 'MedicalCertificateController@edit')->middleware('role:Doctor')->name('medical-certificates.edit');
    Route::put('/medical-certificates/{medicalCertificate}', 'MedicalCertificateController@update')->middleware('role:Doctor')->name('medical-certificates.update');
    Route::post('/medical-certificates/{medicalCertificate}/issue', 'MedicalCertificateController@issue')->middleware('role:Doctor')->name('medical-certificates.issue');
    Route::get('/health-assessments/{assessment}', 'HealthAssessmentController@show')->name('patient.assessment.staff');
    Route::post('/student-complaints/{complaint}/queue', 'ClinicQueueController@store')->middleware('role:Administrator,Nurse,Staff')->name('clinic-queues.store');
    Route::patch('/clinic-queues/{queue}', 'ClinicQueueController@update')->middleware('role:Administrator,Nurse,Staff')->name('clinic-queues.update');
    Route::post('/clinic-queues/call-next', 'ClinicQueueController@callNext')->middleware('role:Administrator,Nurse,Staff')->name('clinic-queues.call-next');
    Route::patch('/clinic-queues/policy/today', 'ClinicQueueController@policy')->middleware('role:Administrator,Nurse,Staff')->name('clinic-queues.policy');
    Route::get('/clinic-queues/live', 'ClinicQueueController@live')->name('clinic-queues.live');
    Route::post('/clinic-queues/{queue}/transfer', 'ClinicQueueController@transfer')->middleware('role:Administrator,Nurse,Staff')->name('clinic-queues.transfer');
    Route::post('/clinic-queues/{queue}/requeue', 'ClinicQueueController@requeue')->middleware('role:Administrator,Nurse,Staff')->name('clinic-queues.requeue');
    Route::post('/nurse/queue/{queue}/start-counter-service', 'CounterServiceController@start')->middleware('role:Administrator,Nurse,Staff')->name('counter-services.start');
    Route::get('/nurse/counter-services/{queue}', 'CounterServiceController@show')->middleware('role:Administrator,Nurse,Staff')->name('counter-services.show');
    Route::post('/nurse/counter-services/{queue}/complete', 'CounterServiceController@complete')->middleware('role:Administrator,Nurse,Staff')->name('counter-services.complete');
    Route::patch('/patient-dependents/{dependent}/verify', 'PatientDependentController@verify')->middleware('role:Administrator,Nurse')->name('patient-dependents.verify');
    Route::get('/dependent-sponsor-review', 'PatientDependentController@review')->middleware('role:Administrator,Nurse')->name('patient-dependents.review');
    Route::patch('/dependent-sponsor-review/{dependent}', 'PatientDependentController@resolve')->middleware('role:Administrator,Nurse')->name('patient-dependents.resolve');
    Route::get('dashboard', 'DashboardController@index')->name('dashboard');
    Route::get('/activity-logs', 'ActivityLogController@index')->name('activity.logs');
    Route::get('/doctors/{doctor}/prescription-profile', 'DoctorProfileController@edit')->middleware('role:Administrator,Doctor')->name('doctor-profile.edit');
    Route::patch('/doctors/{doctor}/prescription-profile', 'DoctorProfileController@update')->middleware('role:Administrator,Doctor')->name('doctor-profile.update');
    Route::patch('/doctors/{doctor}/availability', 'DoctorProfileController@availability')->middleware('role:Administrator,Doctor')->name('doctor-profile.availability');

    Route::middleware('role:Administrator,Nurse,Staff')->group(function () {
        Route::post('/consultations/{consultation}/call-student', 'ConsultationQueueController@callStudent')->name('consultations.call-student');
    });
    Route::get('/doctor/patients/{patient}/health-record', 'MedicalRecordController@doctorPatient')
        ->middleware('role:Doctor')->name('doctor.patients.health-record');
    Route::post('/doctor/consultations/{consultation}/start', 'ConsultationQueueController@start')
        ->middleware('role:Doctor')->name('doctor.consultations.start');

    Route::get('/student-complaints', 'StudentComplaintQueueController@index')->name('student-complaints.index');
    Route::get('/student-complaints/{complaint}', 'StudentComplaintQueueController@show')->name('student-complaints.show');
    Route::post('/student-complaints/{complaint}/patient', 'StudentComplaintQueueController@createPatientRecord')->middleware('role:Administrator,Nurse,Staff')->name('student-complaints.create-patient');
    Route::patch('/student-complaints/{complaint}/status', 'StudentComplaintQueueController@updateStatus')->middleware('role:Administrator,Nurse,Staff')->name('student-complaints.status');
    Route::post('/student-complaints/{complaint}/resolve-counter', 'StudentComplaintQueueController@resolveCounter')->middleware('role:Administrator,Nurse,Staff')->name('student-complaints.resolve-counter');
    Route::post('/student-complaints/{complaint}/forward', 'StudentComplaintQueueController@forwardConsultation')->middleware('role:Administrator,Nurse,Staff')->name('student-complaints.forward');
    Route::patch('/consultations/{consultation}/reassign', 'StudentComplaintQueueController@reassignConsultation')->middleware('role:Administrator,Nurse')->name('consultations.reassign');
    Route::post('/student-complaints/{complaint}/start-consultation', 'StudentComplaintQueueController@startConsultation')->middleware('role:Administrator,Doctor')->name('student-complaints.start-consultation');
    Route::post('/student-complaints/{complaint}/complete-consultation', 'StudentComplaintQueueController@completeConsultation')->middleware('role:Administrator,Doctor')->name('student-complaints.complete-consultation');
    Route::patch('/student-complaints/{complaint}/link-record', 'StudentComplaintQueueController@linkRecord')
        ->middleware('role:Administrator,Nurse,Staff')
        ->name('student-complaints.link-record');
    Route::patch('/student-complaints/{complaint}/clinical-notes', 'StudentComplaintQueueController@updateClinicalNotes')
        ->middleware('role:Administrator,Doctor')
        ->name('student-complaints.clinical-notes');

	Route::view('labreports', 'patients.labreport');

    Route::get('profile/{user}', 'ProfileController@show')->name('profile.show');

    Route::middleware('admin')->group(function () {
        Route::get('/complaint-options', 'CommonComplaintOptionController@index')->name('complaint-options.index');
        Route::post('/complaint-options', 'CommonComplaintOptionController@store')->name('complaint-options.store');
        Route::put('/complaint-options/{option}', 'CommonComplaintOptionController@update')->name('complaint-options.update');
        Route::resource('users', 'UserController');
        Route::patch('users/{user}/account-type', 'UserController@updateAccountType')->name('users.account-type');
        Route::post('users/{user}/assisted-password-reset', 'UserController@assistedPasswordReset')->name('users.assisted-password-reset');
        Route::get('users/archive/index', 'UserController@archive')->name('users.archive');
        Route::delete('users/force-delete/{id}', 'UserController@deleteUser')->name('users.delete');
        Route::get('users/restore/{id}', 'UserController@restoreUser')->name('users.restore');
        Route::post('users/search', 'UserController@search')->name('users.search');
        Route::post('users/archive/search', 'UserController@archive_search')->name('users.archive_search');
    });
    
    Route::resource('patients', 'PatientController')->except(['destroy']);
    Route::delete('patients/{patient}', 'PatientController@destroy')->middleware('role:Administrator')->name('patients.destroy');
    Route::get('patients/archive/index', 'PatientController@archive')->middleware('role:Administrator')->name('patients.archive');
    Route::delete('patients/force-delete/{id}', 'PatientController@deletePatient')->middleware('role:Administrator')->name('patients.delete');
    Route::get('patients/restore/{id}', 'PatientController@restorePatient')->middleware('role:Administrator')->name('patients.restore');
    Route::post('patients/search', 'PatientController@search')->name('patients.search');
    Route::post('patients/archive/search', 'PatientController@archive_search')->middleware('role:Administrator')->name('patients.archive_search');
    
    Route::resource('services', 'ServiceController')->except(['create', 'store', 'edit', 'update', 'destroy']);
    Route::middleware('role:Administrator')->group(function () {
        Route::get('services/create', 'ServiceController@create')->name('services.create');
        Route::post('services', 'ServiceController@store')->name('services.store');
        Route::get('services/{service}/edit', 'ServiceController@edit')->name('services.edit');
        Route::put('services/{service}', 'ServiceController@update')->name('services.update');
        Route::patch('services/{service}', 'ServiceController@update');
        Route::delete('services/{service}', 'ServiceController@destroy')->name('services.destroy');
        Route::get('services/archive/index', 'ServiceController@archive')->name('services.archive');
        Route::delete('services/force-delete/{id}', 'ServiceController@forceDestroy')->name('services.delete');
        Route::get('services/restore/{id}', 'ServiceController@restore')->name('services.restore');
    });
    Route::post('services/search', 'ServiceController@search')->name('services.search');
    Route::post('services/archive/search', 'ServiceController@archive_search')->middleware('role:Administrator')->name('services.archive_search');

    Route::get('help', 'HelpController@index')->name('help');
    Route::get('doctors', 'DoctorsController@index')->name('doctors');
    Route::get('contact', 'ContactController@index')->name('contact');
    Route::get('medical-records', 'MedicalRecordController@index')->name('medical-records.index');
    Route::get('medical-records/{medical_record}', 'MedicalRecordController@show')->name('medical-records.show');
    Route::middleware('role:Administrator,Doctor')->group(function () {
        Route::post('medical-records', 'MedicalRecordController@store')->name('medical-records.store');
        Route::get('medical-records/{medical_record}/edit', 'MedicalRecordController@edit')->name('medical-records.edit');
        Route::put('medical-records/{medical_record}', 'MedicalRecordController@update')->name('medical-records.update');
        Route::patch('medical-records/{medical_record}', 'MedicalRecordController@update');
        Route::delete('medical-records/{medical_record}', 'MedicalRecordController@destroy')->name('medical-records.destroy');
    });
    });
});
