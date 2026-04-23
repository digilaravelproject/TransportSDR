<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{DocumentTemplate, TemplateCategory};
use Illuminate\Http\Request;

class DocumentTemplateController extends Controller
{
    // ─────────────────────────────────────────────────
    // GET /api/v1/document-templates
    // Query: ?category=invoice OR ?category_id=1
    // ─────────────────────────────────────────────────
    // public function index(Request $request)
    // {
    //     try {
    //         $templates = DocumentTemplate::with('category')
    //             ->where('is_active', true)
    //             ->when(
    //                 $request->category,
    //                 fn($q, $v) =>
    //                 $q->whereHas('category', fn($q) => $q->where('slug', $v))
    //             )
    //             ->when(
    //                 $request->category_id,
    //                 fn($q, $v) =>
    //                 $q->where('category_id', $v)
    //             )
    //             ->orderBy('sort_order')
    //             ->get()
    //             ->map(fn($t) => [
    //                 'id'            => $t->id,
    //                 'name'          => $t->name,
    //                 'slug'          => $t->slug,
    //                 'description'   => $t->description,
    //                 'thumbnail_url' => $t->thumbnail_url,
    //                 'is_default'    => $t->is_default,
    //                 'variables'     => $t->variables,
    //                 'category'      => [
    //                     'id'    => $t->category->id,
    //                     'name'  => $t->category->name,
    //                     'slug'  => $t->category->slug,
    //                     'icon'  => $t->category->icon,
    //                     'color' => $t->category->color,
    //                 ],
    //             ]);

    //         return response()->json([
    //             'success' => true,
    //             'data'    => $templates,
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function index(Request $request)
    {
        try {
            $templates = DocumentTemplate::with('category')
                ->where('is_active', true)
                ->when(
                    $request->category,
                    fn($q, $v) =>
                    $q->whereHas('category', fn($q) => $q->where('slug', $v))
                )
                ->when(
                    $request->category_id,
                    fn($q, $v) =>
                    $q->where('category_id', $v)
                )
                ->orderBy('sort_order')
                ->get()
                ->map(fn($t) => [
                    'id'          => (string) $t->id, // Format as string
                    'name'        => $t->name,
                    'description' => $t->description ?? '',
                    'type'        => $t->category->name, // Mapping type to category name
                    'lastUpdated' => $t->updated_at->toIso8601String(), // Valid DateTime format
                    // Preview URL generate ho rahi hai
                    'url'         => route('admin.document-templates.preview', $t->id),
                    'thumbnail'   => $t->thumbnail_url,
                    'is_default'  => (bool) $t->is_default,
                ]);

            return response()->json([
                'success' => true,
                'data'    => $templates,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // GET /api/v1/document-templates/categories
    // All active categories with template count
    // ─────────────────────────────────────────────────
    public function categories()
    {
        try {
            $categories = TemplateCategory::where('is_active', true)
                ->withCount(['activeTemplates as templates_count'])
                ->orderBy('sort_order')
                ->get()
                ->map(fn($c) => [
                    'id'              => $c->id,
                    'name'            => $c->name,
                    'slug'            => $c->slug,
                    'description'     => $c->description,
                    'icon'            => $c->icon,
                    'color'           => $c->color,
                    'templates_count' => $c->templates_count,
                ]);

            return response()->json([
                'success' => true,
                'data'    => $categories,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // GET /api/v1/document-templates/{id}
    // Single template detail
    // ─────────────────────────────────────────────────
    public function show(DocumentTemplate $documentTemplate)
    {
        try {
            if (!$documentTemplate->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template not found.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data'    => [
                    'id'            => $documentTemplate->id,
                    'name'          => $documentTemplate->name,
                    'slug'          => $documentTemplate->slug,
                    'description'   => $documentTemplate->description,
                    'thumbnail_url' => $documentTemplate->thumbnail_url,
                    'is_default'    => $documentTemplate->is_default,
                    'variables'     => $documentTemplate->variables,
                    'blade_view'    => $documentTemplate->blade_view,
                    'category'      => [
                        'id'    => $documentTemplate->category->id,
                        'name'  => $documentTemplate->category->name,
                        'slug'  => $documentTemplate->category->slug,
                        'icon'  => $documentTemplate->category->icon,
                        'color' => $documentTemplate->category->color,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
