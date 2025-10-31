<?php
// app/Traits/LogsActions.php

namespace App\Traits;

use App\Models\ActionLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

trait LogsActions
{
    /**
     * Log when a record is CREATED
     */
    public static function logCreate(Model $model, string $comments = null): void
    {
        self::createLog($model, 'created', $comments);
    }

    /**
     * Log when a record is UPDATED
     */
    public static function logUpdate(Model $model, string $comments = null): void
    {
        self::createLog($model, 'updated', $comments);
    }

    /**
     * Log when a record is DELETED (only if delete succeeds)
     */
    public static function logDelete(Model $model, string $comments = null): bool
    {
        if ($model->delete()) {
            self::createLog($model, 'deleted', $comments);
            return true;
        }
        return false;
    }

    /**
     * Internal: Create the ActionLog entry
     */
    private static function createLog(Model $model, string $action, ?string $comments): void
    {
        ActionLog::create([
            'module'     => class_basename($model),
            'action'     => $action,
            'record_id'  => $model->id,
            'comments'   => $comments,
            'user_id'    => Auth::id() ?? null,
            'deleted_at' => $action === 'deleted' ? now() : null,
        ]);
    }
}