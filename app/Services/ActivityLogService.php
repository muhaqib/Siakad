<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogService
{
    /**
     * Log an action
     */
    public function log(string $action, ?Model $model = null, ?array $changes = null): ActivityLog
    {
        return ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model?->id,
            'changes' => $changes,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Log a create action
     */
    public function logCreate(Model $model): ActivityLog
    {
        return $this->log('create', $model, ['new' => $model->toArray()]);
    }

    /**
     * Log an update action
     */
    public function logUpdate(Model $model, array $oldValues): ActivityLog
    {
        return $this->log('update', $model, [
            'old' => $oldValues,
            'new' => $model->fresh()->toArray(),
        ]);
    }

    /**
     * Log a delete action
     */
    public function logDelete(Model $model): ActivityLog
    {
        return $this->log('delete', $model, ['deleted' => $model->toArray()]);
    }

    /**
     * Log user login
     */
    public function logLogin(): ActivityLog
    {
        return $this->log('login');
    }

    /**
     * Log user logout
     */
    public function logLogout(): ActivityLog
    {
        return $this->log('logout');
    }

    /**
     * Get recent activities
     */
    public function getRecent(int $limit = 20)
    {
        return ActivityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get activities for a specific model
     */
    public function getForModel(Model $model)
    {
        return ActivityLog::where('model_type', get_class($model))
            ->where('model_id', $model->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
