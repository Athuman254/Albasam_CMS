<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'content',
        'type',
        'is_active',
        'published_at',
        'created_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'published_at' => 'datetime',
    ];

    /**
     * Scope for active notices
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for published notices
     */
    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Scope for student notices
     */
    public function scopeForStudents($query)
    {
        return $query->whereNotIn('type', ['teacher', 'staff', 'employee']);
    }

    /**
     * Scope for teacher notices
     */
    public function scopeForTeachers($query)
    {
        return $query->whereIn('type', ['general', 'teacher']);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
