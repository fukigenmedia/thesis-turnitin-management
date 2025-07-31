<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Models\TurnitinThread;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class ThreadOwnerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        $threadId = $request->route('thread');

        // If no thread parameter, allow the request to continue
        if (! $threadId) {
            return $next($request);
        }

        // Find the thread
        $thread = TurnitinThread::find($threadId);

        if (! $thread) {
            abort(404, 'Thread not found');
        }

        // Only allow students to edit their own threads
        if ($user->role !== UserRole::STUDENT || $user->id !== $thread->student_id) {
            abort(403, 'Unauthorized. Only the thread creator can edit this thread.');
        }

        return $next($request);
    }
}
