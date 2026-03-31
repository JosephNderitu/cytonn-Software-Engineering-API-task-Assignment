<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'    => [
                'required',
                'string',
                'max:255',
            ],
            'due_date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today',   // due_date must be today or later
            ],
            'priority' => [
                'required',
                'in:low,medium,high',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'           => 'A task title is required.',
            'title.max'                => 'Task title may not exceed 255 characters.',
            'due_date.required'        => 'A due date is required.',
            'due_date.date_format'     => 'Due date must be in YYYY-MM-DD format.',
            'due_date.after_or_equal'  => 'Due date must be today or a future date.',
            'priority.required'        => 'Priority is required.',
            'priority.in'              => 'Priority must be one of: low, medium, high.',
        ];
    }

    /**
     * Return JSON error responses instead of redirecting.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}