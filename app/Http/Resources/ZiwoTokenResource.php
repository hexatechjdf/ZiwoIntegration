<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ZiwoTokenResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        //https://asnanimedia-api.aswat.co/surveillance/recordings/
        $crmAuth = $this->location_token ?? null;
        return [
            'endpoint' => $this->endpoint, // Assuming this is stored in your credentials
            'token' => $this->token,
            'accountName'=>$this->ziwo_account_name??'asnanimedia',
            'company_id' => $crmAuth->company_id??'',
            'location_id' => $crmAuth->location_id?? '', // This will be null if not applicable
           // 'company_access_token' => $crmAuth->access_token??'', // This will be null if not applicable
            'location_token' =>  $crmAuth->access_token ?? '', // This will be null if not applicable
        ];
    }
}
