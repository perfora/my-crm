<?php

namespace App\Http\Controllers;

use App\Models\SystemLog;
use Illuminate\Http\Request;

class SystemLogController extends Controller
{
    public function index(Request $request)
    {
        $query = SystemLog::query()->latest('id');

        if ($request->filled('channel')) {
            $query->where('channel', $request->string('channel'));
        }

        if ($request->filled('level')) {
            $query->where('level', $request->string('level'));
        }

        if ($request->filled('q')) {
            $q = (string) $request->string('q');
            $query->where(function ($inner) use ($q) {
                $inner->where('message', 'like', '%' . $q . '%')
                    ->orWhere('url', 'like', '%' . $q . '%')
                    ->orWhere('source', 'like', '%' . $q . '%');
            });
        }

        return view('sistem-loglari.index', [
            'logs' => $query->limit(300)->get(),
        ]);
    }
}
