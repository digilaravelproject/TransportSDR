<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TemplateCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TemplateCategoryController extends Controller
{
    public function index()
    {
        $categories = TemplateCategory::withCount('templates')
            ->orderBy('sort_order')
            ->paginate(20);

        return view('admin.template-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.template-categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string|unique:template_categories,slug',
            'description' => 'nullable|string',
            'icon'        => 'nullable|string|max:100',
            'color'       => 'nullable|string|max:20',
            'sort_order'  => 'nullable|integer|min:0',
            'is_active'   => 'boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        TemplateCategory::create($validated);

        return redirect()
            ->route('admin.template-categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function show(TemplateCategory $templateCategory)
    {
        $templateCategory->loadCount('templates');
        $templates = $templateCategory->templates()
            ->orderBy('sort_order')
            ->paginate(10);

        return view('admin.template-categories.show', compact('templateCategory', 'templates'));
    }

    public function edit(TemplateCategory $templateCategory)
    {
        return view('admin.template-categories.edit', compact('templateCategory'));
    }

    public function update(Request $request, TemplateCategory $templateCategory)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string|unique:template_categories,slug,' . $templateCategory->id,
            'description' => 'nullable|string',
            'icon'        => 'nullable|string|max:100',
            'color'       => 'nullable|string|max:20',
            'sort_order'  => 'nullable|integer|min:0',
            'is_active'   => 'boolean',
        ]);

        $templateCategory->update($validated);

        return redirect()
            ->route('admin.template-categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(TemplateCategory $templateCategory)
    {
        if ($templateCategory->templates()->count() > 0) {
            return redirect()
                ->back()
                ->with('error', 'Cannot delete category with templates. Delete templates first.');
        }

        $templateCategory->delete();

        return redirect()
            ->route('admin.template-categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
