<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubmissionLog extends Model
{
    public const STATUS_SIGNED = 1;
    public const STATUS_REJECT = 2;
    public const STATUS_ONPROGRESS = 3;

    protected $guarded = ['id'];
}
