<?php

namespace App\Traits;

use App\Services\AuditTrailService;
use Illuminate\Database\Eloquent\Model;

trait LogsActivity
{
    public static function bootLogsActivity()
    {
        static::registerEvents();
    }

    protected static function booted()
    {
        static::registerEvents();
    }

    protected static function registerEvents()
    {
        static::created(function (Model $model) {
            app(AuditTrailService::class)->logModelAction($model, 'created');
        });

        static::updating(function (Model $model) {
            app(AuditTrailService::class)->logModelAction($model, 'updated');
        });

        static::deleted(function (Model $model) {
            app(AuditTrailService::class)->logModelAction($model, 'deleted');
        });
    }
}
