<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduledDocuments extends Model
{
    protected $table = 'scheduled_documents';

    protected $fillable = [
        'is_executed',
        'run_at',
        'document_id',
        'account_id',
        'user_id'
    ];
    
    public function document()
    {
        return $this->belongsTo(UserOpenai::class, 'document_id', 'id');
    }

    public function account()
    {
        return $this->belongsTo(Integration::class, 'account_id', 'id');
    }
}

?>