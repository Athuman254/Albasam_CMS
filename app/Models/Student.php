<?php

namespace App\Models;

use App\Traits\HasHashid;
use App\Traits\HashidRouting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use App\Models\Settings\AcademicYear;

class Student extends Authenticatable implements HasMedia
{
    use SoftDeletes, HasHashid, HashidRouting, InteractsWithMedia;

    protected $table = 'students';
    protected $primaryKey = 'id';

    // Add this method to use hashid for route binding
    public function getRouteKeyName()
    {
        return 'id';
    }

    protected $appends = [
        'hashid',
        'photo_url',
        'full_name',
        // 'fee_summary', 
        'was_promoted_this_year',
        'age',
        'formatted_dob',
        'user_email',
        'user_name',
        'has_user_account',
        'has_balance',
        'is_fully_paid',
        'adm_no',
        'name'
    ];

    protected $fillable = [
        'user_id',
        'student_admission_id',
        'admission_number',
        'rank_id',
        'first_name',
        'middle_name',
        'last_name',
        'date_of_birth',
        'birth_certificate_number',
        'gender_id',
        'religion_id',
        'citizenship',
        'county',
        'ward',
        'permanent_address',
        'previous_school',
        'kcpe_score',
        'physical_disability',
        'hobby',
        'medical_details',
        'character_book',
        'username',
        'password',
        'user_type',
        'password_changed_at',
        'force_password_change',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'date_of_birth' => 'date',

    ];
    /**
     * Resolve the binding using hashid
     * This method is called when using route model binding
     */
    public function resolveRouteBinding($value, $field = null)
    {
        // If the value is numeric, it's probably an ID, so use normal resolution
        if (is_numeric($value)) {
            return $this->where('id', $value)->firstOrFail();
        }

        // Otherwise, treat it as a hashid
        $decoded = app('hashid')->decode($value);

        if (empty($decoded)) {
            abort(404, 'Invalid student ID');
        }

        $id = $decoded[0];
        return $this->where('id', $id)->firstOrFail();
    }
    /**
     * Register media collections for the student
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('student_photos')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/gif'])
            ->useDisk('public');
    }

    /**
     * Get all student media - Required by Spatie Media Library
     */
    public function media(): MorphMany
    {
        return $this->morphMany('Spatie\MediaLibrary\MediaCollections\Models\Media', 'model');
    }

    /**
     * Get the student's primary photo (convenience method)
     */
    public function studentPhoto()
    {
        return $this->morphOne('Spatie\MediaLibrary\MediaCollections\Models\Media', 'model')
            ->where('collection_name', 'student_photos');
    }

    /**
     * Get the student's photo URL
     */
    public function getPhotoUrlAttribute(): string
    {
        $media = $this->getFirstMedia('student_photos');
        return $media ? $media->getUrl() : $this->getDefaultPhotoUrl();
    }

    /**
     * Get default photo URL if no photo exists
     */
    protected function getDefaultPhotoUrl(): string
    {
        $gender = $this->gender?->name ?? 'default';
        $defaultPhotos = [
            'male' => '/images/default-male-student.png',
            'female' => '/images/default-female-student.png',
            'default' => '/images/default-student.png'
        ];

        return asset($defaultPhotos[strtolower($gender)] ?? $defaultPhotos['default']);
    }

    /**
     * Get the student's full name
     */
    public function getFullNameAttribute(): string
    {
        $names = array_filter([$this->first_name, $this->middle_name, $this->last_name]);
        return implode(' ', $names);
    }

    /**
     * Get name attribute (alias for full_name)
     */
    public function getNameAttribute(): string
    {
        return $this->full_name;
    }

    /**
     * Get admission number with fallback
     */
    public function getAdmNoAttribute(): string
    {
        return $this->admission_number ?? $this->admission?->admission_number ?? 'N/A';
    }

    /**
     * RELATIONSHIPS
     */

    /**
     * Relationship with user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Relationship with admission - CORRECT: BelongsTo relationship
     */
    public function admission(): BelongsTo
    {
        return $this->belongsTo(StudentAdmission::class, 'student_admission_id', 'id');
    }

    /**
     * Relationship with rank/class
     */
    public function rank(): BelongsTo
    {
        return $this->belongsTo(Rank::class, 'rank_id', 'id');
    }

    /**
     * Get the student's current rank (alias for rank relationship)
     */
    public function currentRank(): BelongsTo
    {
        return $this->rank();
    }

    /**
     * Relationship with gender
     */
    public function gender(): BelongsTo
    {
        return $this->belongsTo(Gender::class, 'gender_id', 'id');
    }

    /**
     * Relationship with religion
     */
    public function religion(): BelongsTo
    {
        return $this->belongsTo(Religion::class, 'religion_id', 'id');
    }

    /**
     * Relationship with guardians
     */
    public function guardians(): HasMany
    {
        return $this->hasMany(Guardian::class, 'student_id', 'id');
    }

    /**
     * Relationship with siblings
     */
    public function siblings(): HasMany
    {
        return $this->hasMany(Sibling::class, 'student_id', 'id');
    }

    /**
     * Relationship with student promotions
     */
    public function promotions(): HasMany
    {
        return $this->hasMany(StudentPromotion::class, 'student_id', 'id');
    }

    /**
     * Get primary guardian
     */
    public function primaryGuardian(): BelongsTo
    {
        return $this->belongsTo(Guardian::class, 'id', 'student_id')
            ->where('is_primary', true)
            ->withDefault();
    }

    /**
     * Relationship with exam marks
     */
    public function examMarks(): HasMany
    {
        return $this->hasMany(ExamMark::class, 'student_id', 'id');
    }

    /**
     * Relationship with class (through rank) - alias
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(Rank::class, 'rank_id', 'id');
    }

    /**
     * FEE RELATIONSHIPS
     */

    /**
     * Relationship with fees
     */
    public function fees(): HasMany
    {
        return $this->hasMany(\App\Models\Fee::class, 'student_id', 'id');
    }

    /**
     * Relationship with fee payments
     */
    public function feePayments(): HasMany
    {
        return $this->hasMany(\App\Models\FeePayment::class, 'student_id', 'id');
    }

    /**
     * Relationship with autorecorded payments
     */
    public function autorecordedPayments(): HasMany
    {
        return $this->hasMany(\App\Models\AutorecordedPayment::class, 'matched_student_id', 'id');
    }

    /**
     * ATTRIBUTES
     */

    /**
     * Get total fees amount for student
     */
    public function getTotalFeesAttribute(): float
    {
        return (float) $this->fees()->where('status', '!=', 'carried_over')->sum('amount');
    }

    /**
     * Get total paid amount for student
     */
    public function getTotalPaidAttribute(): float
    {
        return (float) $this->feePayments()->where('status', 'completed')->sum('amount');
    }

    /**
     * Get balance amount for student
     */
    public function getBalanceAttribute(): float
    {
        return $this->total_fees - $this->total_paid;
    }

    /**
     * Check if student has fee balance
     */
    public function getHasBalanceAttribute(): bool
    {
        return $this->balance > 0;
    }

    /**
     * Check if student is fully paid
     */
    public function getIsFullyPaidAttribute(): bool
    {
        return $this->balance <= 0;
    }

    /**
     * Get pending payments for student
     */
    public function pendingPayments(): HasMany
    {
        return $this->autorecordedPayments()->where('status', 'recorded');
    }

    /**
     * Get verified payments for student
     */
    public function verifiedPayments(): HasMany
    {
        return $this->autorecordedPayments()->where('status', 'verified');
    }

    /**
     * Get current term fees
     */
    public function currentTermFees()
    {
        $currentTerm = \App\Models\Fee::getCurrentTerm();
        $currentYear = now()->year;

        return $this->fees()
            ->where('academic_year', $currentYear)
            ->where('term', $currentTerm)
            ->get();
    }

    /**
     * Get current term balance
     */
    public function getCurrentTermBalanceAttribute(): float
    {
        $currentTerm = \App\Models\Fee::getCurrentTerm();
        $currentYear = now()->year;

        $currentTermFees = $this->fees()
            ->where('academic_year', $currentYear)
            ->where('term', $currentTerm)
            ->sum('amount');

        $currentTermPayments = $this->feePayments()
            // ->where('academic_year', $currentYear)
            // ->where('term', $currentTerm)
            ->where('status', 'completed')
            ->sum('amount');

        return $currentTermFees - $currentTermPayments;
    }

    /**
     * Check if student has overdue fees
     */
    public function getHasOverdueFeesAttribute(): bool
    {
        return $this->fees()
            ->where('due_date', '<', now())
            ->where('balance', '>', 0)
            ->exists();
    }

    /**
     * Get current academic year promotion
     */
    public function currentAcademicYearPromotion()
    {
        $currentAcademicYear = AcademicYear::where('is_active', true)->first();

        if (!$currentAcademicYear) {
            return null;
        }

        return $this->promotions()
            ->where('academic_year_id', $currentAcademicYear->id)
            ->first();
    }

    /**
     * Check if student was promoted in current academic year
     */
    public function getWasPromotedThisYearAttribute(): bool
    {
        return $this->currentAcademicYearPromotion() !== null;
    }

    /**
     * Get latest promotion record
     */
    public function latestPromotion()
    {
        return $this->promotions()
            ->with(['fromClass', 'toClass', 'academicYear', 'promotedBy'])
            ->latest('promoted_at')
            ->first();
    }

    /**
     * Get age of student
     */
    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth?->age;
    }

    /**
     * Get formatted date of birth
     */
    public function getFormattedDobAttribute(): ?string
    {
        return $this->date_of_birth?->format('d/m/Y');
    }

    /**
     * Get user email (convenience method)
     */
    public function getUserEmailAttribute(): ?string
    {
        return $this->user?->email;
    }

    /**
     * Get user name (convenience method)
     */
    public function getUserNameAttribute(): ?string
    {
        return $this->user?->name;
    }

    /**
     * Check if student has user account
     */
    public function getHasUserAccountAttribute(): bool
    {
        return !is_null($this->user_id);
    }

    /**
     * Get fee summary for student
     */
    public function getFeeSummaryAttribute(): array
    {
        $totalFees = $this->total_fees;
        $totalPaid = $this->total_paid;
        $balance = $this->balance;
        $currentTermBalance = $this->current_term_balance;

        return [
            'total_fees' => $totalFees,
            'total_paid' => $totalPaid,
            'balance' => $balance,
            'current_term_balance' => $currentTermBalance,
            'payment_progress' => $totalFees > 0 ? round(($totalPaid / $totalFees) * 100, 2) : 100,
            'has_balance' => $balance > 0,
            'is_fully_paid' => $balance <= 0,
            'current_term_paid' => $currentTermBalance <= 0,
            'has_overdue_fees' => $this->has_overdue_fees,
        ];
    }

    /**
     * SCOPES
     */

    /**
     * Scope for students who haven't been promoted in current academic year
     */
    public function scopeNotPromotedThisYear($query)
    {
        $currentAcademicYear = AcademicYear::where('is_active', true)->first();

        if (!$currentAcademicYear) {
            return $query;
        }

        return $query->whereDoesntHave('promotions', function ($q) use ($currentAcademicYear) {
            $q->where('academic_year_id', $currentAcademicYear->id);
        });
    }

    /**
     * Scope for students who have been promoted in current academic year
     */
    public function scopePromotedThisYear($query)
    {
        $currentAcademicYear = AcademicYear::where('is_active', true)->first();

        if (!$currentAcademicYear) {
            return $query;
        }

        return $query->whereHas('promotions', function ($q) use ($currentAcademicYear) {
            $q->where('academic_year_id', $currentAcademicYear->id);
        });
    }

    /**
     * Scope for students eligible for promotion (have exam marks in current academic year)
     */
    public function scopeEligibleForPromotion($query)
    {
        $currentAcademicYear = AcademicYear::where('is_active', true)->first();

        if (!$currentAcademicYear) {
            return $query;
        }

        return $query->whereHas('examMarks', function ($q) use ($currentAcademicYear) {
            $q->whereHas('examSubject.exam', function ($q2) use ($currentAcademicYear) {
                $q2->where('academic_year_id', $currentAcademicYear->id);
            });
        });
    }

    /**
     * Scope for filtering by class
     */
    public function scopeClassfilter($query, $terms = '')
    {
        if (empty($terms)) {
            return $query;
        }

        collect(explode(' ', $terms))->filter()->each(function ($term) use ($query) {
            $term = '%' . $term . '%';
            $query->whereHas('rank', function ($q) use ($term) {
                $q->where('name', 'like', $term);
            });
        });
    }

    /**
     * Scope for searching students
     */
    public function scopeSearch($query, $search)
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
                ->orWhere('middle_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('admission_number', 'like', "%{$search}%")
                ->orWhereHas('rank', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
        });
    }

    /**
     * Scope for students in a specific class/rank
     */
    public function scopeInRank($query, $rankId)
    {
        return $query->where('rank_id', $rankId);
    }

    /**
     * Scope for active students (not deleted)
     */
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Scope for students with user accounts
     */
    public function scopeHasUserAccount($query)
    {
        return $query->whereNotNull('user_id');
    }

    /**
     * Scope for students without user accounts
     */
    public function scopeDoesntHaveUserAccount($query)
    {
        return $query->whereNull('user_id');
    }

    /**
     * Scope for students with fee balance
     */
    public function scopeWithBalance($query)
    {
        return $query->whereHas('fees', function ($q) {
            $q->where('balance', '>', 0);
        });
    }

    /**
     * Scope for students without fee balance
     */
    public function scopeWithoutBalance($query)
    {
        return $query->whereDoesntHave('fees', function ($q) {
            $q->where('balance', '>', 0);
        });
    }

    /**
     * Scope for students with overdue fees
     */
    public function scopeWithOverdueFees($query)
    {
        return $query->whereHas('fees', function ($q) {
            $q->where('due_date', '<', now())
                ->where('balance', '>', 0);
        });
    }

    /**
     * Scope for students with admission
     */
    public function scopeWithAdmission($query)
    {
        return $query->whereNotNull('student_admission_id');
    }

    /**
     * Scope for students without admission
     */
    public function scopeWithoutAdmission($query)
    {
        return $query->whereNull('student_admission_id');
    }

    /**
     * MODEL BOOT METHOD
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($student) {
            if (empty($student->admission_number)) {
                $student->admission_number = static::generateAdmissionNumber();
            }
        });

        // Auto-create user account when student is created (optional)
        static::created(function ($student) {
            // You can add logic here to automatically create a user account
            // if needed for the student portal
        });
    }

    /**
     * Generate unique admission number
     */
    protected static function generateAdmissionNumber(): string
    {
        $prefix = 'STD';
        $year = date('Y');

        do {
            $number = mt_rand(1000, 9999);
            $admissionNumber = "{$prefix}{$year}{$number}";
        } while (static::where('admission_number', $admissionNumber)->exists());

        return $admissionNumber;
    }

    /**
     * PROMOTION METHODS
     */

    /**
     * Promote student to new class
     */
    public function promoteTo($newRankId, $academicYearId, $promotedById = null)
    {
        $currentRankId = $this->rank_id;

        $promotion = StudentPromotion::create([
            'student_id' => $this->id,
            'from_class_id' => $currentRankId,
            'to_class_id' => $newRankId,
            'academic_year_id' => $academicYearId,
            'promoted_by' => $promotedById,
            'promoted_at' => now(),
            'status' => 'promoted'
        ]);

        // Update student's current class
        $this->rank_id = $newRankId;
        $this->save();

        return $promotion;
    }

    /**
     * Check if student can be promoted (has exam results)
     */
    public function canBePromoted($academicYearId = null): bool
    {
        if (!$academicYearId) {
            $academicYear = AcademicYear::where('is_active', true)->first();
            $academicYearId = $academicYear?->id;
        }

        if (!$academicYearId) {
            return false;
        }

        return $this->examMarks()
            ->whereHas('examSubject.exam', function ($q) use ($academicYearId) {
                $q->where('academic_year_id', $academicYearId);
            })
            ->exists();
    }

    /**
     * Get promotion history
     */
    public function promotionHistory()
    {
        return $this->promotions()
            ->with(['fromClass', 'toClass', 'academicYear', 'promotedBy'])
            ->orderBy('promoted_at', 'desc')
            ->get();
    }

    /**
     * Get student's current class name
     */
    public function getCurrentClassName(): string
    {
        return $this->rank?->name ?? 'N/A';
    }

    /**
     * Check if student has admission record
     */
    public function hasAdmission(): bool
    {
        return !is_null($this->student_admission_id);
    }

    /**
     * Get admission details
     */
    public function getAdmissionDetails()
    {
        return $this->admission ? [
            'id' => $this->admission->id,
            'date' => $this->admission->formatted_date,
            'division' => $this->admission->division_name,
            'is_active' => $this->admission->is_active,
        ] : null;
    }

    /**
     * Convert model to array for API responses
     */
    public function toArray()
    {
        $array = parent::toArray();

        // Ensure all appended attributes are included
        $array['photo_url'] = $this->photo_url;
        $array['full_name'] = $this->full_name;
        $array['fee_summary'] = $this->fee_summary;
        $array['was_promoted_this_year'] = $this->was_promoted_this_year;
        $array['age'] = $this->age;
        $array['formatted_dob'] = $this->formatted_dob;
        $array['user_email'] = $this->user_email;
        $array['user_name'] = $this->user_name;
        $array['has_user_account'] = $this->has_user_account;
        $array['has_balance'] = $this->has_balance;
        $array['is_fully_paid'] = $this->is_fully_paid;
        $array['adm_no'] = $this->adm_no;
        $array['name'] = $this->name;

        return $array;
    }
}
