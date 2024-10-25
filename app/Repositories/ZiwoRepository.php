<?php

namespace App\Repositories;

use App\Models\ZiwoDetail;

class ZiwoRepository
{
    public function getCredentialsByLocation($locationId = null)
    {
        return ZiwoDetail::where('location_id', $locationId)->first()
            ?? ZiwoDetail::where('location_id', null)->first();
    }
}


?>