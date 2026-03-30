<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MediaFile extends Model
{
    use SoftDeletes;

    protected $table    = 'media_files';
    protected $fillable = ['module_name', 'module_id', 'file_type', 'file_path', 'file_name', 'mime_type', 'uploaded_by'];
}
