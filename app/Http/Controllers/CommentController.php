<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CommentController extends Controller
{
    use AuthorizesRequests;

    /**
     * Stores new comment
     * @param Request $request
     * @param $taskId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, Task $task)
    {
        setPermissionsTeamId($task->project->team_id);

        $validated = $request->validate([
            'content' => 'required|string|min:1',
        ]);

        $task->comments()->create([
            'content' => $validated['content'],
            'created_by' => Auth::id(),
        ]);

        return back()->with('message', 'Commento aggiunto con successo.');
    }

    /**
     * Update the specified comment
     * @param Request $request
     * @param Comment $comment
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Comment $comment)
    {
        setPermissionsTeamId($comment->task->project->team_id);
        $this->authorize('update', $comment);

        $validated = $request->validate([
            'content' => 'required|string|min:1',
        ]);

        $comment->update($validated);

        return back()->with('message', 'Commento aggiornato.');
    }

    /**
     * Removes the task
     * @param Comment $comment
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Comment $comment)
    {
        setPermissionsTeamId($comment->task->project->team_id);
        $this->authorize('delete', $comment);

        $comment->delete();

        return back()->with('message', 'Commento eliminato.');
    }
}
