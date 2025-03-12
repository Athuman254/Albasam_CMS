<?php

namespace App\Http\Requests;

use App\Rules\LessonTimeAvailabilityRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LessonRequest extends FormRequest
{
    public function rules(): array
    {
        if($this->isMethod('POST')) {
            return $this->storeLesson();
        }

        return $this->updateLesson();
    }
    
    public function storeLesson(): array
    {
        return [
            'rank_id' => ['required', Rule::exists('ranks', 'id')],
            'subject_id' => ['required', Rule::exists('subjects', 'id')],
            'teacher_id' => ['nullable', Rule::exists('teachers', 'id')],
            'weekday' => ['required', 'integer', 'min:1', 'max:7'],
            'start_time' => ['required', new LessonTimeAvailabilityRule(), 'date_format:' . config('panel.lesson_time_format')],
            'end_time' => ['required', 'after:start_time', 'date_format:' . config('panel.lesson_time_format')],
        ];
    }

    public function updateLesson(): array
    {
        return [
            'rank_id' => ['required', Rule::exists('ranks', 'id')],
            'subject_id' => ['required', Rule::exists('subjects', 'id')],
            'teacher_id' => ['nullable', Rule::exists('teachers', 'id')],
            'weekday' => ['required', 'integer', 'min:1', 'max:7'],
            'start_time' => ['required', new LessonTimeAvailabilityRule($this->route('lesson')->id), 'date_format:' . config('panel.lesson_time_format')],
            'end_time' => ['required', 'after:start_time', 'date_format:' . config('panel.lesson_time_format')],
        ];
    }
}
