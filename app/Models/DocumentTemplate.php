<?php
// app/Models/DocumentTemplate.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class DocumentTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'thumbnail',
        'blade_view',
        'variables',
        'sample_data',
        'is_active',
        'is_default',
        'sort_order',
        'usage_count',
    ];

    protected $casts = [
        'variables'   => 'array',
        'sample_data' => 'array',
        'is_active'   => 'boolean',
        'is_default'  => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($t) {
            if (empty($t->slug)) {
                $t->slug = Str::slug($t->name);
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(TemplateCategory::class, 'category_id');
    }

    public function submissions()
    {
        return $this->hasMany(DocumentTemplateSubmission::class);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail ? asset("storage/{$this->thumbnail}") : null;
    }

    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }
}
