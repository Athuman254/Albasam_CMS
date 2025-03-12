<?php

namespace App\Models;

use App\Traits\HasHashid;
use App\Traits\HashidRouting;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasHashid, HashidRouting;
    
    protected $table = 'lessons';
    protected $primaryKey = 'id';
    protected $appends = ['hashid'];
    protected $fillable = [
        'rank_id', 'subject_id', 'teacher_id', 'weekday', 'start_time', 'end_time',
    ];
    
    const MONDAY = 1;
    const TUESDAY = 2;
    const WEDNESDAY = 3;
    const THURSDAY = 4;
    const FRIDAY = 5;
    const SATURDAY = 6;
    const SUNDAY = 7;
    
    const WEEK_DAYS = [
        '1' => 'Monday',
        '2' => 'Tuesday',
        '3' => 'Wednesday',
        '4' => 'Thursday',
        '5' => 'Friday',
        '6' => 'Saturday',
        '7' => 'Sunday',
    ];
    
    public function rank(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Rank::class, 'rank_id', 'id');
    }
    
    public function subject(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id', 'id');
    }
    
    public function teacher(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'id');
    }
    
    public function getDifferenceAttribute(): ?string
    {
        return Carbon::parse($this->end_time)->diffInMinutes($this->start_time);
    }
    
    public function getStartTimeAttribute($value): ?string
    {
        return $value ? Carbon::createFromFormat('H:i:s', $value)->format(config('panel.lesson_time_format')) : null;
    }
    
    public function setStartTimeAttribute($value): void
    {
        $this->attributes['start_time'] = $value ? Carbon::createFromFormat(config('panel.lesson_time_format'),
            $value)->format('H:i') : null;
    }
    
    public function getEndTimeAttribute($value): ?string
    {
        return $value ? Carbon::createFromFormat('H:i:s', $value)->format(config('panel.lesson_time_format')) : null;
    }
    
    public function setEndTimeAttribute($value): void
    {
        $this->attributes['end_time'] = $value ? Carbon::createFromFormat(config('panel.lesson_time_format'),
            $value)->format('H:i') : null;
    }
    
    public static function isTimeAvailable($weekday, $startTime, $endTime, $class, $teacher, $lesson): bool
    {
        $lessons = self::where('weekday', $weekday)
            ->when($lesson, function ($query) use ($lesson) {
                $query->where('id', '!=', $lesson);
            })
            ->where(function ($query) use ($class, $teacher) {
                $query->where('rank_id', $class)
                    ->orWhere('teacher_id', $teacher);
            })
            ->where([
                ['start_time', '<', $endTime],
                ['end_time', '>', $startTime],
            ])
            ->count();
        
        return !$lessons;
    }
    
    public function scopeCalendarByRoleOrClassId($query)
    {
        return $query->when(!request()->input('rank_id'), function ($query) {
            $query->when(auth()->user()->is_teacher, function ($query) {
                $query->where('teacher_id', auth()->user()->id);
            })
                ->when(auth()->user()->is_student, function ($query) {
                    $query->where('rank_id', auth()->user()->rank_id ?? '0');
                });
            })
            ->when(request()->input('rank_id'), function ($query) {
                $query->where('rank_id', request()->input('rank_id'));
            });
    }
}
