<?php

namespace App\Services;

use App\Jobs\SendNotificationEmail;
use App\Models\User;
use App\Repositories\Interfaces\NotificationRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class LeysNotificationService
{
    protected $repository;

    public function __construct(NotificationRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getUserNotifications(int $userId, array $filters = [])
    {
        return $this->repository->getUserNotifications($userId, $filters);
    }

    public function markAsRead(string $id, int $userId)
    {
        $notification = $this->repository->markAsRead($id, $userId);
        $this->clearCache($userId);
        return $notification;
    }

    public function markAllAsRead(int $userId)
    {
        $this->repository->markAllAsRead($userId);
        $this->clearCache($userId);
    }

    public function deleteNotification(string $id, int $userId)
    {
        $this->repository->delete($id, $userId);
        $this->clearCache($userId);
    }

    public function getUnreadCount(int $userId): int
    {
        return Cache::remember('unread_notifications_' . $userId, 60, function () use ($userId) {
            return $this->repository->getUnreadCount($userId);
        });
    }

    public function createNotification(int $userId, string $type, string $title, string $message)
    {
        $data = [
            'id' => (string) Str::uuid(),
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
        ];

        $notification = $this->repository->create($data);

        $user = User::find($userId);
        $preferences = $user->notification_preferences ?? [];

        if (isset($preferences[$type]) && $preferences[$type]['email'] === true) {
            SendNotificationEmail::dispatch($notification);
        }

        $this->clearCache($userId);

        return $notification;
    }

    protected function clearCache(int $userId)
    {
        Cache::forget('unread_notifications_' . $userId);
    }
}