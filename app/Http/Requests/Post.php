<?php

namespace App\Http\Requests;

use App\Models\Posts;
use Illuminate\Foundation\Http\FormRequest;

class Post extends FormRequest
{
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
            'content' => 'required',
            'title' => 'required',
            'privacy' => 'required|in:' . Posts::PRIVACY_PUBLIC . "," . Posts::PRIVACY_HIDDEN,
            'status' => 'required|in:' . Posts::STATUS_PUBLISH . "," . Posts::STATUS_DRAFT,
        ];
    }
}
