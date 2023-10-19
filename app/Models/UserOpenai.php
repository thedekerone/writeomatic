<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserOpenai extends Model
{
    use HasFactory;
    protected $table = 'user_openai';

    // STORAGE
    public const STORAGE_LOCAL = "public";
    public const STORAGE_AWS = "s3";

    public function generator(){
        return $this->belongsTo(OpenAIGenerator::class , 'openai_id','id' );
    }
}
