<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{DocumentTemplate, TemplateCategory};
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\{File, Storage};
use Barryvdh\DomPDF\Facade\Pdf;

class DocumentTemplateController extends Controller
{
    public function index(Request $request)
    {
        $categories = TemplateCategory::where('is_active', true)->get();

        $templates = DocumentTemplate::with('category')
            ->when($request->category_id, fn($q, $v) => $q->where('category_id', $v))
            ->when($request->is_active,   fn($q, $v) => $q->where('is_active', (bool)$v))
            ->when($request->search,      fn($q, $v) => $q->where('name', 'like', "%{$v}%"))
            ->orderBy('category_id')
            ->orderBy('sort_order')
            ->paginate(15)
            ->withQueryString();

        return view('admin.document-templates.index', compact('templates', 'categories'));
    }

    public function create()
    {
        $categories = TemplateCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('admin.document-templates.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:template_categories,id',
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string|unique:document_templates,slug',
            'description' => 'nullable|string',
            'blade_view'  => 'required|string|max:255',
            'variables'   => 'nullable|string',  // JSON string
            'sample_data' => 'nullable|string',  // JSON string
            'sort_order'  => 'nullable|integer|min:0',
            'is_active'   => 'boolean',
            'is_default'  => 'boolean',
            'thumbnail'   => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Thumbnail upload
        if ($request->hasFile('thumbnail')) {
            $file = $request->file('thumbnail');
            $path = $file->storeAs(
                'public/template-thumbnails',
                'thumb-' . time() . '.' . $file->extension()
            );
            $validated['thumbnail'] = str_replace('public/', '', $path);
        }

        // JSON decode
        $validated['variables']   = $request->variables   ? json_decode($request->variables, true)   : null;
        $validated['sample_data'] = $request->sample_data ? json_decode($request->sample_data, true) : null;

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // If is_default — unset others in same category
        if (!empty($validated['is_default'])) {
            DocumentTemplate::where('category_id', $validated['category_id'])
                ->update(['is_default' => false]);
        }

        DocumentTemplate::create($validated);

        return redirect()
            ->route('admin.document-templates.index')
            ->with('success', 'Template created successfully.');
    }

    public function show(DocumentTemplate $documentTemplate)
    {
        $documentTemplate->load('category');
        return view('admin.document-templates.show', compact('documentTemplate'));
    }

    public function edit(DocumentTemplate $documentTemplate)
    {
        $categories = TemplateCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('admin.document-templates.edit', compact('documentTemplate', 'categories'));
    }

    public function update(Request $request, DocumentTemplate $documentTemplate)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:template_categories,id',
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string|unique:document_templates,slug,' . $documentTemplate->id,
            'description' => 'nullable|string',
            'blade_view'  => 'required|string|max:255',
            'variables'   => 'nullable|string',
            'sample_data' => 'nullable|string',
            'sort_order'  => 'nullable|integer|min:0',
            'is_active'   => 'boolean',
            'is_default'  => 'boolean',
            'thumbnail'   => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Thumbnail upload
        if ($request->hasFile('thumbnail')) {
            // Delete old
            if ($documentTemplate->thumbnail) {
                Storage::delete('public/' . $documentTemplate->thumbnail);
            }
            $file = $request->file('thumbnail');
            $path = $file->storeAs(
                'public/template-thumbnails',
                'thumb-' . $documentTemplate->id . '-' . time() . '.' . $file->extension()
            );
            $validated['thumbnail'] = str_replace('public/', '', $path);
        }

        $validated['variables']   = $request->variables   ? json_decode($request->variables, true)   : null;
        $validated['sample_data'] = $request->sample_data ? json_decode($request->sample_data, true) : null;

        if (!empty($validated['is_default'])) {
            DocumentTemplate::where('category_id', $validated['category_id'])
                ->where('id', '!=', $documentTemplate->id)
                ->update(['is_default' => false]);
        }

        $documentTemplate->update($validated);

        return redirect()
            ->route('admin.document-templates.index')
            ->with('success', 'Template updated successfully.');
    }

    public function destroy(DocumentTemplate $documentTemplate)
    {
        if ($documentTemplate->thumbnail) {
            Storage::delete('public/' . $documentTemplate->thumbnail);
        }

        $documentTemplate->delete();

        return redirect()
            ->route('admin.document-templates.index')
            ->with('success', 'Template deleted successfully.');
    }

    // Preview template with sample data
    // public function preview(DocumentTemplate $documentTemplate)
    // {
    //     try {
    //         $data = $documentTemplate->sample_data ?? [];

    //         if (!view()->exists($documentTemplate->blade_view)) {
    //             return back()->with('error', "Blade view '{$documentTemplate->blade_view}' not found.");
    //         }

    //         $pdf = Pdf::loadView($documentTemplate->blade_view, $data)->setPaper('a4');

    //         return $pdf->stream("preview-{$documentTemplate->slug}.pdf");
    //     } catch (\Exception $e) {
    //         return back()->with('error', 'Preview failed: ' . $e->getMessage());
    //     }
    // }

    // DocumentTemplateController.php
    // public function preview(DocumentTemplate $documentTemplate)
    // {
    //     $data = $documentTemplate->sample_data ?? [];
    //     // Direct view return karke check karein error kya hai
    //     return view($documentTemplate->blade_view, $data);
    // }

    public function preview(DocumentTemplate $documentTemplate)
    {
        try {
            $data = $documentTemplate->sample_data ?? [];

            // Essential: Variables ko structure karein aur objects mein convert karein
            $vars = [
                'tenant'   => (object) ($data['tenant'] ?? []),
                'trip'     => (object) ($data['trip'] ?? []),
                'gst'      => $data['gst'] ?? [],
                'einvoice' => $data['einvoice'] ?? [],
            ];

            // Date fix: Agar date string hai toh Carbon object banayein
            if (isset($vars['trip']->trip_date)) {
                $vars['trip']->trip_date = \Carbon\Carbon::parse($vars['trip']->trip_date);
            } else {
                $vars['trip']->trip_date = now();
            }

            if (!view()->exists($documentTemplate->blade_view)) {
                return back()->with('error', "View file '{$documentTemplate->blade_view}' nahi mili.");
            }

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($documentTemplate->blade_view, $vars)
                ->setPaper('a4')
                ->setOption(['isRemoteEnabled' => true]);

            return $pdf->stream("preview-{$documentTemplate->slug}.pdf");
        } catch (\Exception $e) {
            return "PDF Error: " . $e->getMessage();
        }
    }

    // Toggle active status
    public function toggleStatus(DocumentTemplate $documentTemplate)
    {
        $documentTemplate->update(['is_active' => !$documentTemplate->is_active]);

        return back()->with('success', 'Template status updated.');
    }
}
