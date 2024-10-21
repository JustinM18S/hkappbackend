<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController; 
use App\Http\Controllers\API\StudentAssignmentController;
use Illuminate\Http\Request; 

// Get authenticated user details
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Authentication Routes
Route::post('/auth/login', [UserController::class, 'login']);
Route::post('/auth/verify-code', [UserController::class, 'verifyCode']);
Route::post('/auth/forgot-password', [UserController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [UserController::class, 'resetPassword']);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/user', [UserController::class, 'user']);
    Route::post('/auth/logout', [UserController::class, 'logout']);
    Route::post('/auth/logout-all', [UserController::class, 'logoutFromAllDevices']);

    Route::post('/create-user', [UserController::class, 'createUser']);
    Route::delete('/users/{id}', [UserController::class, 'deleteUser']);
    Route::put('/users/{id}', [UserController::class, 'updateUser']);
    Route::get('/students', [UserController::class, 'getStudents']);
    Route::get('/faculties', [UserController::class, 'getFaculties']);

    // Student assignment routes
    Route::post('/assign-student', [StudentAssignmentController::class, 'assignStudent']);
    Route::get('/assignments', [StudentAssignmentController::class, 'getAssignments']);
    Route::put('/assignments/{id}', [StudentAssignmentController::class, 'updateAssignment']);
    Route::delete('/assignments/{id}', [StudentAssignmentController::class, 'deleteAssignment']);
    Route::get('/assignments/faculty/{faculty_id}', [StudentAssignmentController::class, 'getAssignmentsByFaculty']);
    Route::get('/assignments/student/{student_id}', [StudentAssignmentController::class, 'getAssignmentsByStudent']);
});
