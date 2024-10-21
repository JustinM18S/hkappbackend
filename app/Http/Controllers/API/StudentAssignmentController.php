<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\StudentAssignment;
use Illuminate\Http\Request;

class StudentAssignmentController extends Controller
{
    public function assignStudent(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'faculty_id' => 'required|exists:users,id',
            'deadline' => 'required|date',
            'hk_type' => 'required|string',
        ]);

        $assignment = StudentAssignment::create($validated);

        return response()->json([
            'message' => 'Student assigned successfully',
            'data' => $assignment,
        ], 201);
    }

    public function getAssignments()
    {
        $assignments = StudentAssignment::all();
        return response()->json($assignments, 200);
    }

    public function getAssignmentsByFaculty($faculty_id)
    {
        $assignments = StudentAssignment::where('faculty_id', $faculty_id)->get();
        return response()->json($assignments, 200);
    }

    public function getAssignmentsByStudent($student_id)
    {
        $assignments = StudentAssignment::where('student_id', $student_id)->get();
        return response()->json($assignments, 200);
    }

    public function updateAssignment(Request $request, $id)
    {
        $assignment = StudentAssignment::find($id);

        if (!$assignment) {
            return response()->json(['message' => 'Assignment not found'], 404);
        }

        $validated = $request->validate([
            'faculty_id' => 'required|exists:users,id',
            'deadline' => 'required|date',
            'hk_type' => 'required|string',
        ]);

        $assignment->update($validated);

        return response()->json(['message' => 'Assignment updated successfully', 'assignment' => $assignment], 200);
    }

    public function deleteAssignment($id)
    {
        $assignment = StudentAssignment::find($id);

        if (!$assignment) {
            return response()->json(['message' => 'Assignment not found'], 404);
        }

        $assignment->delete();

        return response()->json(['message' => 'Assignment deleted successfully'], 200);
    }

    public function getStudents()
    {
        $students = User::where('user_type', 'student')
                        ->get(['id', 'name', 'email']);

        return response()->json([
            'message' => 'Student list fetched successfully',
            'students' => $students,
        ], 200);
    }

    public function getFaculties()
    {
        $faculties = User::where('user_type', 'faculty')
                        ->get(['id', 'name', 'email']);

        return response()->json([
            'message' => 'Faculty list fetched successfully',
            'faculties' => $faculties,
        ], 200);
    }



}
