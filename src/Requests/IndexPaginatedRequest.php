<?php

namespace SupplementBacon\LaravelPaginable\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class IndexPaginatedRequest extends FormRequest
{
    const PAGINATION = 'pagination';
    const FILTER = 'filters';
    const SORT = 'columnKey';
    const SORT_DIRECTION = 'order';
    const SEARCH = 'search';
    const WITH = 'with';
    const WITHOUT = 'without';
    const IDS = 'ids';

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            self::PAGINATION => 'required|array',
            self::PAGINATION . '.current' => 'required|numeric',
            self::PAGINATION . '.pageSize' => 'required|numeric',

            self::FILTER => 'nullable|array',

            self::SORT => 'nullable|string',
            self::SORT_DIRECTION => 'nullable|in:ascend,descend',

            self::SEARCH => 'nullable|array',

            self::WITH => 'nullable|array',
            self::WITH . '.*' => 'string',

            self::WITHOUT => 'nullable|array',
            self::WITHOUT . '.*' => 'string',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'errors' => $validator->errors(),
        ])->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY));
    }

    public function isRequestingNotApproved(): bool
    {
        return $this->has(IndexPaginatedRequest::WITH) && array_search('notApproved', $this->{IndexPaginatedRequest::WITH}) !== false;
    }
}
