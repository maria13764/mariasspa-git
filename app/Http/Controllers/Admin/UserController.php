<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage-users');

        return view('admin.users.index', [
            'users' => User::query()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('manage-users');

        return view('admin.users.create', [
            'roles' => UserRole::cases(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        User::create($request->validated());

        return redirect()->route('admin.users.index')->with('status', 'user-created');
    }

    public function edit(User $user): View
    {
        Gate::authorize('manage-users');

        return view('admin.users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $user->update($request->validated());

        return redirect()->route('admin.users.index')->with('status', 'user-updated');
    }

    public function destroy(User $user): RedirectResponse
    {
        Gate::authorize('manage-users');

        abort_if($user->is(auth()->user()), 403, 'You cannot delete your own admin account.');

        $user->delete();

        return redirect()->route('admin.users.index')->with('status', 'user-deleted');
    }
}
