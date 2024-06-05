<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;

class TaskController extends Controller
{
    public function getTasks(Request $request)
    {
        // Fetch all tasks
        $tasks = Task::all();

        // Return tasks as JSON
        return response()->json($tasks);
    }
}
