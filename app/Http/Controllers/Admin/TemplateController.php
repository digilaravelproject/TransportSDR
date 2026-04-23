<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{TemplateCategory, Template};
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TemplateController extends Controller
{
    // Category Index
    public function categoryIndex()
    {
        $categories = TemplateCategory::all();
        return view('admin.templates.categories.index', compact('categories'));
    }

    public function categoryStore(Request $request)
    {
        $request->validate(['name' => 'required|unique:template_categories']);
        TemplateCategory::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name)
        ]);
        return back()->with('success', 'Category created!');
    }

    // Template Index
    public function index()
    {
        $templates = Template::with('category')->get();
        return view('admin.templates.index', compact('templates'));
    }

    public function create()
    {
        $categories = TemplateCategory::where('is_active', true)->get();
        return view('admin.templates.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required',
            'name' => 'required',
            'html_content' => 'required'
        ]);

        Template::create($request->all());
        return redirect()->route('admin.templates.index')->with('success', 'Template added!');
    }
}
