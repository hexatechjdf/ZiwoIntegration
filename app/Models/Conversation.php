<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;
    protected $fillable = [
        'crm_conversation_id',
        'crm_contact_id',
        'conversation_data'
    ];
}
