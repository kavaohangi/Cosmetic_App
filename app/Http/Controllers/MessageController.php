<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Requests\StoreMessageRequest;
use App\Models\Message;
use App\Models\User;
use App\Services\TerrainService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class MessageController extends Controller
{
    public function __construct(private TerrainService $terrainService) {}

    /**
     * List the contacts a user is allowed to chat with:
     *  - their supervisor (N+1),
     *  - their colleagues (same level / same supervisor).
     * Non-terrain roles see every active user.
     */
    public function index(Request $request, ?User $user = null): View
    {
        $auth = $request->user();
        $contacts = $this->contactsFor($auth);

        $conversation = collect();

        if ($user !== null && $auth->can('chat-with', $user)) {
            $conversation = Message::query()
                ->between($auth->id, $user->id)
                ->with(['sender', 'receiver'])
                ->orderBy('created_at')
                ->get();

            Message::query()
                ->where('sender_id', $user->id)
                ->where('receiver_id', $auth->id)
                ->where('lu', false)
                ->update(['lu' => true]);
        }

        return view('messages.index', [
            'contacts' => $contacts,
            'conversation' => $conversation,
            'activeContact' => $user,
        ]);
    }

    public function store(StoreMessageRequest $request): RedirectResponse
    {
        $message = Message::create([
            'sender_id' => $request->user()->id,
            'receiver_id' => $request->integer('receiver_id'),
            'content' => $request->string('content'),
            'lu' => false,
        ]);

        return back()->with('status', 'Message envoyé.')->with('message_id', $message->id);
    }

    /**
     * Allowed contacts for the given user.
     *
     * @return Collection<int, User>
     */
    protected function contactsFor(User $auth): Collection
    {
        if ($auth->role === Role::MarketeurTerrain) {
            $colleagues = $this->terrainService->getColleagues($auth->id);

            return $auth->supervisor !== null
                ? $colleagues->prepend($auth->supervisor)
                : $colleagues;
        }

        return User::query()
            ->where('id', '!=', $auth->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }
}
