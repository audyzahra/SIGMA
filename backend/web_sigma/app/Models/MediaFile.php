<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaFile extends Model
{
    protected $fillable = [
        'model_type',
        'model_id',
        'file_type',
        'category',
        'file_name',
        'file_path',
        'file_extension',
        'file_size',
        'mime_type',
        'thumbnail_path',
        'uploaded_by',
        'upload_source',
        'status',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    /**
     * Relasi polymorphic ke model pemilik file.
     */
    public function model()
    {
        return $this->morphTo();
    }

    /**
     * User yang mengupload file.
     */
    public function uploader()
    {
        return $this->belongsTo(
            User::class,
            'uploaded_by'
        );
    }
}
