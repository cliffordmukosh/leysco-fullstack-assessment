<?php

namespace App\Repositories\Interfaces;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface NotificationRepositoryInterface
{
    public function getUserNotifications(int $userId, array $filters = []): LengthAwarePaginator|Collection;

    public function findById(string $id, int $userId);

    public function markAsRead(string $id, int $userId);

    public function markAllAsRead(int $userId);

    public function delete(string $id, int $userId);

    public function getUnreadCount(int $userId): int;

    public function create(array $data);
}