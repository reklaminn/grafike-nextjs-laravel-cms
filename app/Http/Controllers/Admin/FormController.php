<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FormController extends Controller
{
    public function index()
    {
        $forms = Form::withCount(['submissions', 'fields'])
            ->withCount(['submissions as unread_count' => function ($q) {
                $q->where('status', 'new');
            }])
            ->orderBy('name')
            ->get();

        return view('admin.forms.index', compact('forms'));
    }

    public function create()
    {
        return view('admin.forms.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'notification_email' => 'nullable|email',
        ]);

        $form = Form::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'notification_email' => $request->notification_email,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('admin.forms.edit', $form)
            ->with('success', 'Form başarıyla oluşturuldu.');
    }

    public function edit(Form $form)
    {
        $form->load(['fields' => function ($q) {
            $q->orderBy('sort_order');
        }]);

        return view('admin.forms.edit', compact('form'));
    }

    public function update(Request $request, Form $form)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'notification_email' => 'nullable|email',
            'webhook_url' => 'nullable|url|max:1000',
        ]);

        $form->update([
            'name' => $request->name,
            'description' => $request->description,
            'notification_email' => $request->notification_email,
            'is_active' => $request->boolean('is_active', true),
            'webhook_url' => $request->input('webhook_url'),
            'webhook_enabled' => $request->boolean('webhook_enabled'),
        ]);

        return redirect()
            ->route('admin.forms.edit', $form)
            ->with('success', 'Form başarıyla güncellendi.');
    }

    public function destroy(Form $form)
    {
        if ($form->is_system) {
            return redirect()
                ->route('admin.forms.index')
                ->with('error', '"' . $form->name . '" bir sistem formudur ve silinemez.');
        }

        $form->fields()->delete();
        $form->submissions()->delete();
        $form->delete();

        return redirect()
            ->route('admin.forms.index')
            ->with('success', 'Form başarıyla silindi.');
    }

    /**
     * View form submissions.
     */
    public function submissions(Form $form)
    {
        $submissions = $form->submissions()
            ->latest()
            ->paginate(25);

        // Mark as read
        $form->submissions()->where('status', 'new')->update(['status' => 'read']);

        return view('admin.forms.submissions', compact('form', 'submissions'));
    }

    /**
     * Add/update form field via AJAX.
     */
    public function saveField(Request $request, Form $form)
    {
        $request->validate([
            'field_id' => 'nullable|exists:form_fields,id',
            'label' => 'required|string|max:255',
            'name' => 'required|string|max:100',
            'type' => 'required|in:text,email,textarea,select,checkbox,radio,number,date,file,tel,url,hidden',
            'placeholder' => 'nullable|string|max:255',
            'options' => 'nullable|string',
            'validation_rules' => 'nullable|string',
            'is_required' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $data = $request->only(['label', 'name', 'type', 'placeholder', 'options', 'validation_rules', 'is_required', 'sort_order']);

        if ($request->field_id) {
            $field = FormField::findOrFail($request->field_id);
            $field->update($data);
        } else {
            $data['form_id'] = $form->id;
            $data['sort_order'] = $data['sort_order'] ?? ($form->fields()->max('sort_order') + 1);
            $field = FormField::create($data);
        }

        return response()->json(['success' => true, 'field' => $field]);
    }

    /**
     * Delete form field via AJAX.
     */
    public function deleteField(Form $form, FormField $field)
    {
        $field->delete();
        return response()->json(['success' => true]);
    }

    /**
     * Export form submissions as CSV.
     */
    public function exportSubmissions(Form $form)
    {
        $submissions = $form->submissions()->orderBy('created_at', 'desc')->get();
        $fields = $form->fields()->orderBy('sort_order')->get();

        $filename = Str::slug($form->name) . '_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($submissions, $fields) {
            $file = fopen('php://output', 'w');
            // BOM for UTF-8
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header row
            $headerRow = ['#', 'Tarih', 'Durum'];
            foreach ($fields as $field) {
                $headerRow[] = $field->label;
            }
            fputcsv($file, $headerRow);

            // Data rows
            foreach ($submissions as $submission) {
                $data = is_array($submission->data) ? $submission->data : json_decode($submission->data, true) ?? [];
                $row = [
                    $submission->id,
                    $submission->created_at->format('d.m.Y H:i'),
                    $submission->status,
                ];
                foreach ($fields as $field) {
                    $row[] = $data[$field->name] ?? '';
                }
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
