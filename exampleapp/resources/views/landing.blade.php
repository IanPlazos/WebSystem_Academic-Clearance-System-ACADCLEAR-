@extends('layouts.auth')

@section('content')
<div class="min-h-screen bg-slate-100 px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-6xl space-y-6">
        <section class="overflow-hidden rounded-[30px] bg-white shadow-2xl">
            <div class="grid lg:grid-cols-2">
                <div class="flex flex-col justify-between bg-blue-600 p-8 text-white md:p-12">
                    <div class="space-y-5">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-blue-100">AcadClear Tenant App</p>
                            <h1 class="mt-4 text-4xl font-bold leading-tight md:text-5xl">Choose a plan before logging in</h1>
                        </div>
                        <p class="max-w-xl text-base text-blue-100 md:text-lg">
                            Select the package that fits your institution, then submit your details and preferred payment method.
                        </p>
                    </div>

                    <div class="pt-8">
                        <a href="#plans" class="inline-flex items-center justify-center rounded-xl bg-white px-6 py-3 font-semibold text-blue-700 transition hover:bg-blue-50">
                            View Plans
                        </a>
                    </div>
                </div>

                <div class="flex flex-col justify-between bg-slate-950 p-8 text-white md:p-12">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-300">Already have an account?</p>
                            <a href="{{ route('login') }}" class="mt-3 inline-flex rounded-xl bg-blue-500 px-5 py-2.5 font-semibold transition hover:bg-blue-600">Go to Login</a>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-right">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Payments</p>
                            <p class="mt-1 text-sm text-slate-100">GCash or Bank Transfer</p>
                        </div>
                    </div>

                    <div class="mt-10 grid gap-3 text-sm text-slate-300 sm:grid-cols-2">
                        <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">Fast setup for schools</div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">Plan request review</div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">Clearance workflow access</div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">Support for onboarding</div>
                    </div>
                </div>
            </div>
        </section>

        @if (session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <section id="plans" class="grid gap-6 lg:grid-cols-3">
            @foreach ($plans as $plan)
                <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-lg">
                    <div class="flex items-center justify-between bg-slate-900 px-6 py-4 text-white {{ $plan['name'] === 'Standard' ? 'bg-emerald-600' : ($plan['name'] === 'Enterprise' ? 'bg-cyan-600' : '') }}">
                        <h2 class="text-2xl font-bold">{{ $plan['name'] }}</h2>
                        @if ($plan['tag'])
                            <span class="rounded-full bg-amber-300 px-3 py-1 text-xs font-bold uppercase text-amber-900">{{ $plan['tag'] }}</span>
                        @endif
                    </div>

                    <div class="px-6 py-6">
                        <p class="text-4xl font-semibold text-slate-900">{{ $plan['price'] }}</p>
                        <p class="mt-2 text-slate-500">{{ $plan['students'] }}</p>

                        <ul class="mt-6 space-y-2 text-slate-700">
                            @foreach ($plan['features'] as $feature)
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500">✓</span>
                                    <span>{{ $feature }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <button
                            type="button"
                            class="choose-plan mt-6 w-full rounded-xl bg-slate-900 px-4 py-3 font-semibold text-white transition hover:bg-slate-700"
                            data-plan="{{ $plan['name'] }}"
                            data-price="{{ $plan['price'] }}"
                        >
                            Choose {{ $plan['name'] }}
                        </button>
                    </div>
                </article>
            @endforeach
        </section>

        <section id="request" class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm md:p-8">
            <div class="mb-6 flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
                <div>
                    <h3 class="text-2xl font-bold text-slate-900">Plan request form</h3>
                    <p class="text-sm text-slate-500">Submit your institution details and payment information.</p>
                </div>
                <p class="text-sm text-slate-500">Selected plan updates automatically when you click a card.</p>
            </div>

            <form method="POST" action="{{ route('landing.store') }}" class="space-y-5">
                @csrf

                <input type="hidden" name="plan_name" id="plan_name" value="{{ old('plan_name', 'Basic') }}">

                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                    Selected plan: <span id="selected_plan" class="font-semibold text-slate-900">{{ old('plan_name', 'Basic') }}</span>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700" for="institution_name">Institution name</label>
                        <input id="institution_name" name="institution_name" type="text" value="{{ old('institution_name') }}" required class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700" for="contact_person">Contact person</label>
                        <input id="contact_person" name="contact_person" type="text" value="{{ old('contact_person') }}" required class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700" for="email">Email address</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700" for="contact_number">Contact number</label>
                        <input id="contact_number" name="contact_number" type="text" value="{{ old('contact_number') }}" required class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700" for="payment_method">Payment method</label>
                        <select id="payment_method" name="payment_method" required class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Select payment method</option>
                            <option value="gcash" {{ old('payment_method') === 'gcash' ? 'selected' : '' }}>GCash</option>
                            <option value="bank" {{ old('payment_method') === 'bank' ? 'selected' : '' }}>Bank Transfer</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700" for="amount">Payment amount</label>
                        <input id="amount" name="amount" type="text" value="{{ old('amount') }}" required class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500" placeholder="₱1,500.00">
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700" for="payment_reference">Payment reference (optional)</label>
                    <input id="payment_reference" name="payment_reference" type="text" value="{{ old('payment_reference') }}" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Reference number / transaction ID">
                </div>

                <div id="gcash_fields" class="grid gap-4 md:grid-cols-2 {{ old('payment_method') === 'gcash' ? '' : 'hidden' }}">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700" for="gcash_number">GCash number</label>
                        <input id="gcash_number" name="gcash_number" type="text" value="{{ old('gcash_number') }}" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500" placeholder="09XXXXXXXXX">
                    </div>
                </div>

                <div id="bank_fields" class="grid gap-4 md:grid-cols-3 {{ old('payment_method') === 'bank' ? '' : 'hidden' }}">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700" for="bank_name">Bank name</label>
                        <input id="bank_name" name="bank_name" type="text" value="{{ old('bank_name') }}" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700" for="bank_account_name">Account name</label>
                        <input id="bank_account_name" name="bank_account_name" type="text" value="{{ old('bank_account_name') }}" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700" for="bank_account_number">Account number</label>
                        <input id="bank_account_number" name="bank_account_number" type="text" value="{{ old('bank_account_number') }}" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700" for="notes">Additional notes (optional)</label>
                    <textarea id="notes" name="notes" rows="4" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">{{ old('notes') }}</textarea>
                </div>

                <div class="flex flex-col gap-3 border-t border-slate-200 pt-4 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-slate-500">Your request will be reviewed after submission.</p>
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-6 py-3 font-semibold text-white transition hover:bg-blue-700">
                        Submit Request
                    </button>
                </div>
            </form>
        </section>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const planNameInput = document.getElementById('plan_name');
    const selectedPlan = document.getElementById('selected_plan');
    const amountInput = document.getElementById('amount');
    const paymentMethod = document.getElementById('payment_method');
    const gcashFields = document.getElementById('gcash_fields');
    const bankFields = document.getElementById('bank_fields');

    function togglePaymentFields() {
        gcashFields.classList.toggle('hidden', paymentMethod.value !== 'gcash');
        bankFields.classList.toggle('hidden', paymentMethod.value !== 'bank');
    }

    document.querySelectorAll('.choose-plan').forEach(function (button) {
        button.addEventListener('click', function () {
            const plan = button.getAttribute('data-plan');
            const price = button.getAttribute('data-price');

            planNameInput.value = plan;
            selectedPlan.textContent = plan;
            amountInput.value = price;

            document.getElementById('request').scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    paymentMethod.addEventListener('change', togglePaymentFields);
    togglePaymentFields();
});
</script>
@endsection
