<?php
// database/seeders/TemplateCategorySeeder.php
namespace Database\Seeders;

use App\Models\{TemplateCategory, DocumentTemplate};
use Illuminate\Database\Seeder;

class TemplateCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name'        => 'Letterhead',
                'slug'        => 'letterhead',
                'description' => 'Company letterhead templates',
                'icon'        => 'fas fa-envelope-open-text',
                'color'       => '#534AB7',
                'sort_order'  => 2,
                'templates'   => [
                    ['name' => 'Simple Letterhead',  'slug' => 'letterhead-simple',  'blade_view' => 'pdf.templates.letterhead', 'is_default' => true],
                    ['name' => 'Formal Letterhead',  'slug' => 'letterhead-formal',  'blade_view' => 'pdf.templates.letterhead', 'is_default' => false],
                ],
            ],
            [
                'name'        => 'Quotation',
                'slug'        => 'quotation',
                'description' => 'Trip quotation templates',
                'icon'        => 'fas fa-file-contract',
                'color'       => '#E8650A',
                'sort_order'  => 3,
                'templates'   => [
                    ['name' => 'Standard Quotation', 'slug' => 'quotation-standard', 'blade_view' => 'pdf.templates.quotation', 'is_default' => true],
                    ['name' => 'Detailed Quotation', 'slug' => 'quotation-detailed', 'blade_view' => 'pdf.templates.quotation', 'is_default' => false],
                ],
            ],
            [
                'name'        => 'Duty Slip',
                'slug'        => 'duty-slip',
                'description' => 'Driver duty slip templates',
                'icon'        => 'fas fa-clipboard-list',
                'color'       => '#378ADD',
                'sort_order'  => 4,
                'templates'   => [
                    ['name' => 'Standard Duty Slip', 'slug' => 'duty-slip-standard', 'blade_view' => 'pdf.duty-slip', 'is_default' => true],
                ],
            ],
        ];

        foreach ($categories as $catData) {
            $templates = $catData['templates'];
            unset($catData['templates']);

            $category = TemplateCategory::firstOrCreate(
                ['slug' => $catData['slug']],
                $catData
            );

            foreach ($templates as $i => $tpl) {
                DocumentTemplate::firstOrCreate(
                    ['slug' => $tpl['slug']],
                    array_merge($tpl, [
                        'category_id' => $category->id,
                        'sort_order'  => $i,
                        'is_active'   => true,
                    ])
                );
            }
        }
    }
}
