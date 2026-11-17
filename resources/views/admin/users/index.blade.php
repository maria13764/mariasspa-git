<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-rose-500">Admin tools</p>
                <h2 class="mt-1 text-2xl font-semibold text-slate-900">Manage accounts</h2>
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.users.create') }}" class="rounded-md bg-[#173b3f] px-4 py-2 text-sm font-semibold text-white hover:bg-[#24595d]">Add user</a>
                <a href="{{ route('dashboard') }}" class="text-sm font-semibold text-[#c27867] hover:underline">Back to dashboard</a>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6 px-6 py-10 lg:px-8">
        @if (session('status'))
            <div class="rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                {{ match (session('status')) {
                    'user-created' => 'Account created.',
                    'user-updated' => 'Account updated.',
                    default => 'Account deleted.',
                } }}
            </div>
        @endif

        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <th class="px-6 py-4">Name</th>
                            <th class="px-6 py-4">Login email</th>
                            <th class="px-6 py-4">Role</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach ($users as $user)
                            <tr class="text-sm text-slate-700">
                                <td class="whitespace-nowrap px-6 py-4 font-semibold text-slate-900">{{ $user->name }}</td>
                                <td class="whitespace-nowrap px-6 py-4">{{ $user->email }}</td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold capitalize text-slate-700">{{ $user->role->value }}</span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="font-semibold text-[#c27867] hover:underline">Edit</a>
                                    @if (! $user->is(auth()->user()))
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="ml-4 inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="font-semibold text-rose-600 hover:underline" onclick="return confirm('Delete this account?')">Delete</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>