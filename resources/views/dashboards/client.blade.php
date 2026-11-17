<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-rose-500">Your self-care</p>
                <h2 class="mt-1 text-2xl font-semibold text-slate-900">Client dashboard</h2>
            </div>
            <span class="rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-800">Client</span>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-6 py-10 lg:px-8">
        <div class="flex flex-wrap items-end justify-between gap-6 rounded-3xl bg-[#e8f2f0] p-8 shadow-xl shadow-[#173b3f]/10">
            <div>
            <p class="text-sm text-[#35716b]">Welcome, {{ auth()->user()->name }}.</p>
            <h1 class="mt-3 max-w-xl font-serif text-4xl leading-tight text-[#173b3f]">Make space for feeling your best.</h1>
            <p class="mt-4 max-w-lg text-sm leading-6 text-[#35716b]">Manage your bookings and keep your next moment of calm in view.</p>
            </div>
            <a href="{{ route('client.appointments.create') }}" class="inline-flex rounded-full bg-[#173b3f] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#24565a]">Book an appointment</a>
        </div>

        <section aria-label="Appointment summary" class="mt-8 grid gap-4 sm:grid-cols-3">
            <article class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-[#173b3f]/10">
                <p class="text-sm text-[#35716b]">Upcoming</p>
                <p class="mt-2 font-serif text-3xl text-[#173b3f]">{{ $upcomingCount }}</p>
            </article>
            <article class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-[#173b3f]/10">
                <p class="text-sm text-[#35716b]">Awaiting confirmation</p>
                <p class="mt-2 font-serif text-3xl text-[#173b3f]">{{ $pendingCount }}</p>
            </article>
            <article class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-[#173b3f]/10">
                <p class="text-sm text-[#35716b]">Total bookings</p>
                <p class="mt-2 font-serif text-3xl text-[#173b3f]">{{ $appointmentCount }}</p>
            </article>
        </section>

        <section aria-labelledby="appointments-heading" class="mt-10">
            <div class="mb-5 flex items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-rose-500">Your schedule</p>
                    <h2 id="appointments-heading" class="mt-1 font-serif text-3xl text-[#173b3f]">Your appointments</h2>
                </div>
            </div>

            @if ($appointments->isEmpty())
                <div class="rounded-3xl bg-white p-8 text-center shadow-sm ring-1 ring-[#173b3f]/10">
                    <h3 class="font-serif text-2xl text-[#173b3f]">Your next visit starts here.</h3>
                    <p class="mt-2 text-sm text-[#35716b]">You don't have any appointments yet. Choose a massage and request a time that suits you.</p>
                    <a href="{{ route('client.appointments.create') }}" class="mt-5 inline-flex rounded-full bg-[#173b3f] px-6 py-3 text-sm font-semibold text-white hover:bg-[#24565a]">Book your first appointment</a>
                </div>
            @else
                <div class="overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-[#173b3f]/10">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[640px] text-left text-sm">
                            <thead class="bg-[#e8f2f0] text-xs uppercase tracking-wider text-[#35716b]">
                                <tr>
                                    <th class="px-6 py-4 font-semibold">Massage</th>
                                    <th class="px-6 py-4 font-semibold">Date and time</th>
                                    <th class="px-6 py-4 font-semibold">Therapist</th>
                                    <th class="px-6 py-4 font-semibold">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#173b3f]/10">
                                @foreach ($appointments as $appointment)
                                    <tr>
                                        <td class="px-6 py-5">
                                            <span class="block font-semibold">{{ $services[$appointment->service]['name'] ?? $appointment->service }}</span>
                                            <span class="mt-1 block text-xs text-slate-500">{{ $services[$appointment->service]['duration'] ?? '' }}</span>
                                        </td>
                                        <td class="px-6 py-5">{{ $appointment->appointment_at->format('M j, Y · g:i A') }}</td>
                                        <td class="px-6 py-5">{{ $appointment->therapist?->name ?? 'To be assigned' }}</td>
                                        <td class="px-6 py-5">
                                            <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-900">{{ $appointment->status->label() }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="mt-6">{{ $appointments->links() }}</div>
            @endif
        </section>
    </div>
</x-app-layout>