<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentNoticeController extends Controller
{
    /**
     * Display a listing of the notices.
     */
    public function index()
    {
        $notices = Notice::active()
            ->published()
            ->forStudents()
            ->orderBy('published_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->through(function ($notice) {
                return [
                    'id' => $notice->id,
                    'title' => $notice->title,
                    'content' => $notice->content,
                    'type' => ucfirst($notice->type),
                    'published_at' => $notice->published_at ? $notice->published_at->format('M j, Y') : $notice->created_at->format('M j, Y'),
                    'is_new' => $notice->published_at ? $notice->published_at->diffInDays(now()) <= 3 : false,
                ];
            });

        return Inertia::render('Student/Notices/Index', [
            'notices' => $notices,
        ]);
    }
}
