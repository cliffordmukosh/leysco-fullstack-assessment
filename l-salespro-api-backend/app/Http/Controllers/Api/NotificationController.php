<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Notification\MarkAsReadRequest;
use App\Http\Resources\NotificationCollection;
use App\Http\Resources\NotificationResource;
use App\Services\LeysNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class NotificationController extends Controller
{
    protected $service;

    public function __construct(LeysNotificationService $service)
    {
        $this->service = $service;
    }

    /**
     * Get paginated list of user notifications
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $notifications = $this->service->getUserNotifications(
            $user->id,
            $request->all()
        );

        return response()->json([
            'success' => true,
            'data' => new NotificationCollection($notifications),
            'message' => 'Notifications retrieved successfully'
        ]);
    }

    /**
     * Mark single notification as read
     */
    public function markAsRead(MarkAsReadRequest $request, string $id): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        try {
            $notification = $this->service->markAsRead($id, $user->id);
            
            return response()->json([
                'success' => true,
                'data' => new NotificationResource($notification),
                'message' => 'Notification marked as read'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found or does not belong to you',
            ], 404);
        }
    }

    /**
     * Mark ALL notifications as read
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            Log::warning('Attempt to mark all notifications as read - unauthenticated');
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $this->service->markAllAsRead($user->id);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read'
        ]);
    }

    /**
     * Delete a notification
     */
    public function destroy(string $id): JsonResponse
    {
        $user = request()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        try {
            $this->service->deleteNotification($id, $user->id);
            
            return response()->json([
                'success' => true,
                'message' => 'Notification deleted'
            ], 200); // or 204 if you prefer no-content
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found or does not belong to you',
            ], 404);
        }
    }

    /**
     * Get count of unread notifications
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $count = $this->service->getUnreadCount($user->id);

        return response()->json([
            'success' => true,
            'data' => ['unread_count' => $count],
            'message' => 'Unread count retrieved'
        ]);
    }
}