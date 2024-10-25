<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CallRequest extends FormRequest
{
    public function rules()
    {
        return [
            'location_id' => 'required|integer',
            'call_id' => 'required|string',
            'user_id' => 'required|integer',
            'user_name' => 'required|string',
            'user_email' => 'required|email',
            'call_duration' => 'required|integer',
            'call_status' => 'required|string|in:success,missed',
            'call_direction' => 'nullable|string|in:outbound,inbound',
            'attachment' => 'nullable|string',
            'from_phone' => 'required|string',
            'contact_phone' => 'required|string',
            'contact_name' => 'nullable|string',
            'conversation_id' => 'nullable|string',
            'contact_id' => 'nullable|integer',
        ];
    }

    public function authorize()
    {
        return true;
    }
}

?>