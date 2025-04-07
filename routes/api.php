<?php

use Illuminate\Http\Request;
use App\Http\Controllers\DoctorsController;
use App\Http\Controllers\StudentsController;
use App\Http\Controllers\GradesController;
use App\Http\Controllers\SubjectsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminsController;


///////////////////////////////////////////////////////////// Start of Admin Setting APIs ///////////////////////////////////////////////////////////

Route::post('/setting', [AdminsController::class, 'store']); // Create new admin settings
Route::get('/setting', [AdminsController::class, 'index']); // Get all admin settings
Route::put('/setting/{user_id}', [AdminsController::class, 'update']); // Update admin settings by ID

///////////////////////////////////////////////////////////// End of Admin Setting APIs /////////////////////////////////////////////////////////////


///////////////////////////////////////////////////////////// Start of Admin Subjects APIs ///////////////////////////////////////////////////////////

Route::post('/admin/subjects', [SubjectsController::class, 'store']); // Create new subject
Route::get('/admin/subjects', [SubjectsController::class, 'index']); // Get all subjects
Route::put('/admin/subjects/{id}', [SubjectsController::class, 'update']); // Update subject by ID
Route::delete('/admin/subjects/{id}', [SubjectsController::class, 'delete']); // Update subject by ID

///////////////////////////////////////////////////////////// End of Admin Subjects APIs /////////////////////////////////////////////////////////////


///////////////////////////////////////////////////////////// Start of Admin Doctors APIs ///////////////////////////////////////////////////////////

Route::post('/admin/doctors', [DoctorsController::class, 'store']); // Create new subject
Route::get('/admin/doctors', [DoctorsController::class, 'index']); // Get all subjects
Route::put('/admin/doctors/{id}', [DoctorsController::class, 'update']); // Update subject by ID
Route::delete('/admin/doctors/{id}', [DoctorsController::class, 'delete']); // Update subject by ID

///////////////////////////////////////////////////////////// End of Admin Doctors APIs /////////////////////////////////////////////////////////////


///////////////////////////////////////////////////////////// Start of Admin Students APIs ///////////////////////////////////////////////////////////

Route::post('/admin/students', [StudentsController::class, 'store']); // Create new subject
Route::get('/admin/students', [StudentsController::class, 'index']); // Get all subjects
Route::put('/admin/students/{id}', [StudentsController::class, 'update']); // Update subject by ID
Route::delete('/admin/students/{id}', [StudentsController::class, 'delete']); // Update subject by ID

///////////////////////////////////////////////////////////// End of Admin Students APIs /////////////////////////////////////////////////////////////


///////////////////////////////////////////////////////////// Start of Doctor Subject APIs ///////////////////////////////////////////////////////////

Route::get('/doctor/{doctor_id}/subjects', [SubjectsController::class, 'getDoctorSubjects']); // Update subject grades by ID
Route::get('/doctor/{doctor_id}/subjects/without-grades', [SubjectsController::class, 'getDoctorSubjectsWithoutGrades']);
Route::put('/doctor/subject/{subject_id}', [SubjectsController::class, 'updateSubjectGrades']); // Update subject grades by ID

///////////////////////////////////////////////////////////// End of Doctor Subject APIs /////////////////////////////////////////////////////////////


///////////////////////////////////////////////////////////// Start of Student Grade APIs ///////////////////////////////////////////////////////////

Route::post('/student/{student_id}/grades', [GradesController::class, 'store']); // Create new subject
Route::get('/students', [GradesController::class, 'getStudents']); // Get all subjects
Route::put('/student/grades/{grade_id}', [GradesController::class, 'updateGrade']); // Create new subject

///////////////////////////////////////////////////////////// Start of Student Grade APIs ///////////////////////////////////////////////////////////
