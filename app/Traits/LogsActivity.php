<?php

namespace App\Traits;

use App\Services\AuditTrailService;
use Illuminate\Database\Eloquent\Model;

trait LogsActivity
{
    public static function bootLogsActivity(): void
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
