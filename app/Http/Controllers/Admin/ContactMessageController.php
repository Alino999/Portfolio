<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;

class ContactMessageController extends Controller
{
    public function index()
    {
        $messages = ContactMessage::latest()->paginate(10);

        return view('admin.contact-messages.index', compact('messages'));
    }

    public function show(ContactMessage $contactMessage)
    {
        $contactMessage->status = 'read';
        $contactMessage->save();

        return view('admin.contact-messages.show', compact('contactMessage'));
    }

    public function destroy(ContactMessage $contactMessage)
    {
        $contactMessage->delete();

        return redirect()->route('admin.messages.index')->with('success', 'Message supprimé avec succès.');
    }
}
