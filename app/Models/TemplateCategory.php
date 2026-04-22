<?php
// app/Models/TemplateCategory.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class TemplateCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($cat) {
            if (empty($cat->slug)) {
                $cat->slug = Str::slug($cat->name);
            }
        });
    }

    public function templates()
    {
        return $this->hasMany(DocumentTemplate::class, 'category_id');
    }

    public function activeTemplates()
    {
        return $this->hasMany(DocumentTemplate::class, 'category_id')
            ->where('is_active', true)
            ->orderBy('sort_order');
    }
}
