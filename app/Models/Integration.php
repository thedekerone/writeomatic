<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Integration extends Model
{
    protected $table = 'integrations';

    protected $fillable = [
        'name',
        'url',
        'username',
        'password',
        'user_id'
    ];
    
    protected $hidden = [
        'password'
    ];
    
    public function user(){
        return $this->belongsTo(User::class);
    }
}

?>