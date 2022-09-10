<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    // Task index page
    public function show()
    {
        return view("task.index", [
            "staff" => Auth::user()
        ]);
    }

    public function create()
    {
        $managed_staff = Staff::select('staffs.*')
            ->where('manager_id', Auth::user()->staff_id)
            ->get();
        return view("task.create", [
            "staff" => Auth::user(),
            "managed_staff" => $managed_staff,
        ]);
    }

    public function store(Request $request)
    {
        $formFields = $request->validate([
            'task_name' => ['required'],
            'staff_id' => ['required']
        ]);

        $formFields['task_status'] = 'assigned';
        $formFields['task_assign_date'] = Carbon::now()->toDateTimeString();

        Task::create($formFields);

        if(Auth::user()->cannot('create-task')) {
            abort(403);
        }

        return back()
            ->with([
                "staff" => Auth::user(),
                "success" => "Task successfully created."
            ])
            ->withErrors('staff_id');
    }

    public function list()
    {
        $managed_staff = Staff::select('staffs.*')
            ->where('manager_id', Auth::user()->staff_id)
            ->get();

        $task = Task::where('staff_id', '=', Auth::user()->staff_id)
            ->where('task_status', '<>', 'completed')
            ->get();

        return Auth::user()->role === 'admin'
            ? view('task.show', [
                'staff' => Auth::user(),
                'managed_staff' => $managed_staff
            ])
            : view('task.show', [
                'staff' => Auth::user(),
                'task' => $task,
                'task_count' => count($task),
                'message_type' => 'info'
            ]);
    }

    public function listGet(Request $request)
    {
        $managed_staff = Staff::select('staffs.*')
            ->where('manager_id', Auth::user()->staff_id)
            ->get();

        $task = Task::where('staff_id', '=', $request->get('employee'))
                    ->where('task_status', '<>', 'completed' )
                    ->get();

        $employee = Staff::where('staff_id', '=', $request->get('employee'))
                        ->get();

        return view('task.show', [
                'staff' => Auth::user(),
                'task' => $task,
                'task_count' => count($task),
                'managed_staff' => $managed_staff,
                'employee' => $employee,
                'message_type' => 'info'
            ]);
    }

    public function update(Request $request)
    {
        $task = Task::find($request->input('task'));
        $task->task_status = $request->input('status');
        $task->save();

        return back()
            ->withErrors('Error')
            ->withSuccess('Status successfully updated.');
    }

    public function delete(Request $request)
    {

    }

    public function listAll()
    {
        $managed_staff = Staff::select('staffs.*')
            ->where('manager_id', Auth::user()->staff_id)
            ->get();

        if(Auth::user()->role === 'admin') {
            $managed_staff_id = Staff::select('staffs.staff_id')
                ->where('manager_id', Auth::user()->staff_id)
                ->get('staff_id')
                ->toArray();
            $task = Task::select('tasks.*')
                ->whereIn('staff_id', $managed_staff_id)
                ->where('task_status', '<>', 'completed' )
                ->get();
        } else {
            $task = Task::where('staff_id', '=', Auth::user()->staff_id)
                ->where('task_status', '=', 'assigned' )
                ->orWhere('task_status', '=', 'accepted')
                ->orWhere('task_status', '=', 'review')
                ->get();
        }

        return view('task.show', [
            'staff' => Auth::user(),
            'task' => $task,
            'task_count' => count($task),
            'managed_staff' => $managed_staff,
            'message_type' => 'info',
            'is_list_all' => true
        ]);
    }
}
