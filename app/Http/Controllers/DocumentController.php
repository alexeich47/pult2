<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Unit;
use App\Support\UnitContextScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class DocumentController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Document::query()->with(['unit', 'creator']);
        UnitContextScope::apply($query);
        $docs = $query->orderByDesc('created_at')->paginate(25)->withQueryString();

        return Inertia::render('Documents/Index', [
            'documents' => $docs,
            'allUnits' => Unit::orderBy('sort_order')->get(['id', 'name', 'color', 'unit_type']),
        ]);
    }

    public function show(Document $document): Response
    {
        $document->load(['unit', 'creator']);

        $activities = Activity::query()
            ->where('log_name', 'document')
            ->where('subject_type', Document::class)
            ->where('subject_id', $document->id)
            ->with('causer')
            ->latest()
            ->get()
            ->map(fn (Activity $a) => [
                'id' => $a->id,
                'description' => $a->description,
                'causer_name' => $a->causer?->name ?? '—',
                'created_at' => $a->created_at?->toISOString(),
                'changes' => $a->properties?->toArray() ?? [],
            ]);

        return Inertia::render('Documents/Show', [
            'document' => $document,
            'activities' => $activities,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'unit_id' => ['nullable', 'string', 'exists:units,id'],
            'title' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'content' => ['nullable', 'string'],
        ]);

        $doc = Document::create($validated + [
            'created_by' => $request->user()?->id,
            'version' => '0.1',
        ]);

        return redirect("/documents/{$doc->id}")->with('flash.success', 'Документ создан');
    }

    public function update(Request $request, Document $document): RedirectResponse
    {
        $validated = $request->validate([
            'unit_id' => ['nullable', 'string', 'exists:units,id'],
            'title' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'content' => ['nullable', 'string'],
        ]);

        $document->update($validated);

        return back()->with('flash.success', 'Документ обновлён');
    }

    public function destroy(Document $document): RedirectResponse
    {
        $document->delete();

        return redirect('/documents')->with('flash.success', 'Документ удалён');
    }
}
