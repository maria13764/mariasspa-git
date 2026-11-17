<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-rose-500">Admin tools</p>
            <h2 class="mt-1 text-2xl font-semibold text-slate-900">Add account</h2>
        </div>
    </x-slot>

    <div class="mx-auto max-w-2xl px-6 py-10 lg:px-8">
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-8">
            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-6">
                @csrf

                <div>
                    <x-input-label for="name" value="Name" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="email" value="Login email" />
                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" required autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="password" value="Temporary password" />
                    <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="password_confirmation" value="Confirm password" />
                    <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                </div>

                <div>
                    <x-input-label for="role" value="Role" />
                    <select id="role" name="role" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-[#c27867] focus:ring-[#c27867]" required>
                        @foreach ($roles as $role)
                            <option value="{{ $role->value }}" @selected(old('role', \App\Enums\UserRole::Client->value) === $role->value)>{{ ucfirst($role->value) }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('role')" class="mt-2" />
                </div>

                <div class="flex items-center justify-between gap-4">
                    <a href="{{ route('admin.users.index') }}" class="text-sm font-semibold text-slate-500 hover:text-slate-700">Cancel</a>
                    <x-primary-button class="bg-[#173b3f] hover:bg-[#24595d] focus:bg-[#24595d]">Create account</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>