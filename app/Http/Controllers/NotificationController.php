<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use Carbon\Carbon;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::where('user_id', auth()->id())
            ->orderBy('id', 'desc')
            ->get();

        // Check voor ongelezen meldingen
        $hasUnreadNotifications = Notification::where('user_id', auth()->id())->where('is_read', 0)->exists();

        // Markeer alle ongelezen meldingen als gelezen
        Notification::where('user_id', auth()->id())->where('is_read', 0)->update(['is_read' => 1]);

        return view('notifications.index', compact('notifications', 'hasUnreadNotifications'));
    }

    public function destroy(Notification $notification)
    {
        // Zorg ervoor dat alleen de eigenaar de melding kan verwijderen
        if (auth()->id() !== $notification->user_id) {
            abort(403);
        }

        $notification->delete();

        return back()->with('success', 'Melding verwijderd');
    }


    // ... Andere methodes voor het beheren van meldingen
}
