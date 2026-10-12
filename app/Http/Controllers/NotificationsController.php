<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationsController extends Controller
{
    /**
     * Display the authenticated user's in-app notifications.
     */
    public function index(Request $request): View
    {
        return view('notifications.index', [
            'notifications' => $request->user()->notifications()->latest()->paginate(20),
        ]);
    }

    /**
     * Mark one notification as read and open its report.
     */
    public function markAsRead(Request $request, string $notification): RedirectResponse
    {
        $item = $request->user()->notifications()->findOrFail($notification);
        $item->markAsRead();

        $reportId = $item->data['report_id'] ?? null;

        return $reportId !== null
            ? redirect()->route('reports.show', $reportId)
            : redirect()->route('notifications.index');
    }

    /**
     * Mark every unread notification as read.
     */
    public function markAllAsRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('status', 'Semua notifikasi ditandai telah dibaca.');
    }
}
