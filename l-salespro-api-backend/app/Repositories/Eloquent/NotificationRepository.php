<?php

namespace App\Repositories\Eloquent;

use App\Models\Notification;
use App\Repositories\Interfaces\NotificationRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class NotificationRepository implements NotificationRepositoryInterface
{
    protected $model;

    public function __construct(Notification $model)
    {
        $this->model = $model;
    }

    public function getUserNotifications(int $userId, array $filters = []): LengthAwarePaginator|Collection
    {
        $query = $this->model->where('user_id', $userId);

        if (isset($filters['type'])) {
            $query->ofType($filters['type']);
        }

        if (isset($filters['unread']) && $filters['unread']) {
            $query->unread();
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->paginate($perPage);
    }

    public function findById(string $id, int $userId)
    {
        return $this->model->where('id', $id)->where('user_id', $userId)->firstOrFail();
    }

    public function markAsRead(string $id, int $userId)
    {
        $notification = $this->findById($id, $userId);
        $notification->update(['is_read' => true, 'read_at' => now()]);
        return $notification;
    }

    public function markAllAsRead(int $userId)
    {
        $this->model->where('user_id', $userId)->where('is_read', false)->update(['is_read' => true, 'read_at' => now()]);
    }

    public function delete(string $id, int $userId)
    {
        $notification = $this->findById($id, $userId);
        $notification->delete();
    }

    public function getUnreadCount(int $userId): int
    {
        return $this->model->where('user_id', $userId)->unread()->count();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }
}