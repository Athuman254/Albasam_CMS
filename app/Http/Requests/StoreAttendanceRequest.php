<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            '*.teacher_id' => 'required',
            '*.id' => 'nullable',
            '*.class_id' => 'required',
            '*.date' => 'required|date',
            '*.status' => 'required|in:Present,Absent,Late,Excused',
            '*.remarks' => 'nullable|string|max:255',
        ];
    }
}
