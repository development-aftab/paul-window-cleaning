<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\StaffNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffNotesController extends Controller
{
    /**
     * Store a new note from staff to admin.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'note' => 'required|string|max:2000',
        ]);

        StaffNote::create([
            'staff_id' => Auth::id(),
            'note' => $request->input('note'),
        ]);

        Notification::create([
            'user_id' => 2,
            'action_id' => Auth::id(),
            'title' => 'Note from ' . Auth::user()->name,
            'message' => $request->input('note'),
            'type' => 'staff_note',
        ]);

        return response()->json(['success' => true, 'message' => 'Note Sent Successfully']);
    }
}
