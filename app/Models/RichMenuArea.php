<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RichMenuArea extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'menu_area';

    protected $casts = [
        'bounds' => 'json',
        'action' => 'json',
    ];

    protected $primaryKey = 'idx';

    protected $fillable = [
        '_menu', // 外鍵
        'bounds',
        'action',
    ];

    public $timestamps = false;

    public function richMenu()
    {
        return $this->belongsTo(RichMenu::class, '_menu', 'idx');
    }
}
