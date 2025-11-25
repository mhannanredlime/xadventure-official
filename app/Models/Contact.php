<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = [
        'name',
        'email',
        'is_subscriber',
        'subject',
        'message',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->name = strip_tags(trim($model->name));
            $model->email = filter_var($model->email, FILTER_SANITIZE_EMAIL);
            $model->subject = strip_tags(trim($model->subject));
            $model->message = strip_tags(trim($model->message));
        });
    }
}
