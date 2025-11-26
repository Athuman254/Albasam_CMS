<?php

namespace App\Rules;

use App\Models\Lesson;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;

class LessonTimeAvailabilityRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($lesson = null)
    {
        $this->lesson = $lesson;
    }
    
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        $weekDay = request()->input('weekday');
        $startTime = Carbon::parse($value)->format('H:i');
        $endTime = Carbon::parse(request()->input('end_time'))->format('H:i');
        $rankId = request()->input('rank_id');
        $teacherId = request()->input('teacher_id');
        
        return Lesson::isTimeAvailable($weekDay, $startTime, $endTime, $rankId, $teacherId, $this->lesson);
    }
    
    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'This time is not available';
    }
}
