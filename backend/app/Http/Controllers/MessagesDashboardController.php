<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessagesDashboardController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', '');
        $search = $request->input('search', '');

        $query = ContactMessage::orderByDesc('created_at');

        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $messages = $query->paginate(20)->withQueryString();

        $totalMessages  = ContactMessage::count();
        $unreadMessages = ContactMessage::where('status', 'unread')->count();
        $repliedMessages = ContactMessage::where('status', 'replied')->count();

        return view('admin.messages.index', compact(
            'messages', 'totalMessages', 'unreadMessages', 'repliedMessages', 'status', 'search'
        ));
    }

    public function markRead(int $id): JsonResponse
    {
        $msg = ContactMessage::findOrFail($id);
        if ($msg->status === 'unread') {
            $msg->update(['status' => 'read']);
        }

        return response()->json(['status' => $msg->status]);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:unread,read,replied'],
        ]);

        $msg = ContactMessage::findOrFail($id);
        $msg->update($validated);

        return response()->json(['status' => $msg->status]);
    }
}
