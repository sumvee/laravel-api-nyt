<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class FetchBestSellers extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'isbn' => ['nullable', 'array'], // The parameter must be an array
            'isbn.*' => ['nullable','string', 'regex:/^\d{10}(\d{3})?$/'], // Each element must be a string matching ISBN-10 or ISBN-13 format
            'author' => 'nullable|string', // The parameter must be a string
            'title' => 'nullable|string', // The parameter must be a string
            'offset' => 'nullable|integer|min:0|multiple_of:20', // The parameter must be an integer and a multiple of 20
        ];
    }

    public function messages(): array
    {
        return [
            'isbn.array' => 'The isbn parameter must be an array.',
            'isbn.*.string' => 'Each ISBN must be a string.',
            'isbn.*.regex' => 'Each ISBN must be a valid ISBN-10 or ISBN-13.',
            'author.string' => 'The author parameter must be a string.',
            'title.string' => 'The title parameter must be a string.',
            'offset.integer' => 'The offset parameter must be an integer.',
            'offset.min' => 'The offset parameter must be a positive integer.',
            'offset.multiple_of' => 'The offset parameter must be a multiple of 20.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $response = new JsonResponse([
            'message' => 'The given data was invalid.',
            'errors' => $validator->errors(),
        ], 422);

        throw new ValidationException($validator, $response);
    }


}
