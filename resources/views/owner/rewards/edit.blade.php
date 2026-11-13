<x-layouts.owner title="Edit Reward">
    <div class="space-y-6">
        <header>
            <a href="{{ route('owner.rewards.index') }}" class="text-sm font-semibold text-orange-600 hover:text-orange-700 hover:underline">
                &larr; Kembali ke Loyalty & Reward
            </a>
            <h1 class="mt-3 text-xl font-black text-stone-900">Edit Reward</h1>
        </header>

        <section class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm sm:p-7">
            @include('owner.rewards._form', ['reward' => $reward])
        </section>
    </div>
</x-layouts.owner>
