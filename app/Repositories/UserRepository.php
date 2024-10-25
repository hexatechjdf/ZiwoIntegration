<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    public function findOrCreateLocationUser($locationId)
    {
        $user = User::where('location_id', $locationId)->first();

        if (!$user) {
            $user = new User();
            $user->name = 'Location User';
            $user->email = $locationId . '@presave.net';
            $user->password = bcrypt('presave_' . $locationId);
            $user->location_id = $locationId;
            $user->ghl_api_key = '-';
            $user->save();
        }

        return $user;
    }
}

?>