<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\StudentTask;
use Illuminate\Http\Request;

class StudentTaskController extends Controller
{

    public function index()
    {
        $tasks = StudentTask::all();
        return response()->json(['tasks' => $tasks], 200);
    }


    public function getStudentTasks($student_id)
    {
        $tasks = StudentTask::with('student:id,name,email')
                    ->where('student_id', $student_id)
                    ->get();

        if ($tasks->isEmpty()) {
            return response()->json(['message' => 'No duties found for this student.'], 404);
        }

        return response()->json([
            'message' => 'Tasks fetched successfully.',
            'tasks' => $tasks->map(function ($task) {
                return [
                    'id' => $task->id,
                    'student_name' => $task->student->name,
                    'task' => $task->task,
                    'assigned_room' => $task->assigned_room,
                    'duty_date' => $task->duty_date,
                    'duty_start' => $task->duty_start,
                    'duty_end' => $task->duty_end,
                    'status' => $task->status,
                ];
            }),
        ], 200);
    }


    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'student_id' => 'required|exists:users,id',
            'task' => 'required|string',
            'assigned_room' => 'required|string',
            'duty_date' => 'required|date',
            'duty_start' => 'required|string',
            'duty_end' => 'required|string',
        ]);

        $task = StudentTask::create($validatedData);

        return response()->json([
            'message' => 'Duty assigned successfully',
            'task' => $task,
        ], 201);
    }

    public function markAsCompleted($id)
    {
        $task = StudentTask::find($id);

        if (!$task) {
            return response()->json(['message' => 'Duty not found.'], 404);
        }

        $task->update(['status' => 'completed']);

        return response()->json([
            'message' => 'Duty marked as completed.',
            'task' => $task,
        ], 200);
    }
}
