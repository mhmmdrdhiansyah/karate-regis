<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;

class ResultController extends Controller
{
    public function index()
    {
        $events = Event::orderBy('name')->get();

        return view('admin.results.index', compact('events'));
    }
}
