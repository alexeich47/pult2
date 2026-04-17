<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Task;
use App\Models\Unit;
use App\Support\UnitContextScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Task::query()->with(['unit', 'assignee', 'creator']);
        UnitContextScope::apply($query);
        $tasks = $query
            ->orderByRaw("CASE status WHEN 'blocked' THEN 0 WHEN 'in_progress' THEN 1 WHEN 'todo' THEN 2 WHEN 'done' THEN 3 END")
            ->orderByRaw("CASE priority WHEN 'critical' THEN 0 WHEN 'high' THEN 1 WHEN 'medium' THEN 2 WHEN 'low' THEN 3 END")
            ->orderBy('due_date')
            ->get();

        return Inertia::render('Tasks/Index', [
            'tasks' => $tasks,
            'allUnits' => Unit::orderBy('sort_order')->get(['id', 'name', 'color', 'unit_type']),
            'allEmployees' => Employee::query()
                ->whereNotNull('name')
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name', 'position', 'unit_id']),
            'statuses' => ['todo', 'in_progress', 'blocked', 'done'],
            'priorities' => ['low', 'medium', 'high', 'critical'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateInput($request);
        $validated['created_by'] = $request->user()?->id;
        if ($validated['status'] === 'done' && empty($validated['completed_at'])) {
            $validated['completed_at'] = now();
        }

        Task::create($validated);

        return back()->with('flash.success', 'Задача создана');
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $validated = $this->validateInput($request);
        // Auto-stamp completed_at when transitioning to "done".
        if ($validated['status'] === 'done' && $task->completed_at === null) {
            $validated['completed_at'] = now();
        } elseif ($validated['status'] !== 'done' && $task->completed_at !== null) {
            $validated['completed_at'] = null;
        }

        $task->update($validated);

        return back()->with('flash.success', 'Обновлено');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $task->delete();

        return back()->with('flash.success', 'Удалено');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateInput(Request $request): array
    {
        return $request->validate([
            'unit_id' => ['nullable', 'string', 'exists:units,id'],
            'assignee_id' => ['nullable', 'integer', 'exists:employees,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:todo,in_progress,done,blocked'],
            'priority' => ['required', 'in:low,medium,high,critical'],
            'due_date' => ['nullable', 'date'],
        ]);
    }
}
