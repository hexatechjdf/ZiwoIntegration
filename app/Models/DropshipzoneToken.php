<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
class DropshipzoneToken extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'access_token',
        'exp_date_time'
    ];
    public static function setAccessToken($user_id, $data)
    {
        if (isset($data['exp'])) {
            $expDateTime = Carbon::createFromTimestamp($data['exp'])->format('Y-m-d H:i:s');
        } else {
            $expDateTime = Carbon::now()->addDay()->format('Y-m-d H:i:s');
        }
        return self::updateOrCreate(
            ['user_id' => $user_id],
            [
                'access_token' => $data['token'],
                'exp_date_time' => $expDateTime
            ]
        );
    }
}
