<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmAuths extends Model
{
    protected $table = 'crm_tokens';
    use HasFactory;
    protected $guarded =[];
}
