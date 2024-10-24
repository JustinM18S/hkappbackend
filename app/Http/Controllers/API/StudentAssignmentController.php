<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\StudentAssignment;
use App\Models\User;
use Illuminate\Http\Request;

class StudentAssignmentController extends Controller
{
    public function assignStudent(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'faculty_id' => 'required|exists:users,id',
            'deadline' => 'required|date',
            'hk_duty_type' => 'required|in:Internal Facilitator,External Facilitator', 
        ]);
    
        $student = User::findOrFail($validated['student_id']);
    
        if ($student->user_type !== 'student') {
            return response()->json(['message' => 'The provided user is not a student'], 400);
        }
    
        $hkType = $student->hk_type;
    
        $faculty = User::findOrFail($validated['faculty_id']);
    
        $assignment = StudentAssignment::create([
            'student_id' => $validated['student_id'],
            'faculty_id' => $validated['faculty_id'],
            'deadline' => $validated['deadline'],
            'hk_type' => $hkType,
            'hk_duty_type' => $validated['hk_duty_type'],
        ]);
    
        return response()->json([
            'message' => 'Student assigned successfully',
            'assignment' => [
                'student_name' => $student->name,
                'faculty_name' => $faculty->name,
                'deadline' => $assignment->deadline,
                'hk_type' => $assignment->hk_type,
                'hk_duty_type' => $assignment->hk_duty_type,
                'created_at' => $assignment->created_at,
                'updated_at' => $assignment->updated_at,
                'id' => $assignment->id,
            ],
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
            'hk_duty_type' => 'required|in:Internal Facilitator,External Facilitator', // Add hk_duty_type validation
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
}
