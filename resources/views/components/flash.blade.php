{{--
Flash messages and validation errors.
Reads the session bag directly so any controller that flashes a message gets
feedback without having to pass it through the view.
--}}

@php
    $flashStatus = session('status');
    $flashError = session('error');
    $errorMessages = $errors->all();
@endphp

@if ($flashStatus || $flashError || count($errorMessages) > 0)
    <div class="mb-6 space-y-3" role="status" aria-live="polite">
        @if ($flashStatus)
            <div data-flash class="flex items-start gap-3 rounded-[12px] border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900">
                <i data-lucide="circle-check" class="mt-0.5 h-4 w-4 shrink-0 text-emerald-600" aria-hidden="true"></i>
                <span class="flex-1">{{ $flashStatus }}</span>
                <button type="button" data-flash-dismiss aria-label="Dismiss message" class="-mr-1 shrink-0 rounded-md p-1 text-emerald-700/70 transition hover:bg-emerald-100 hover:text-emerald-900">
                    <i data-lucide="x" class="h-3.5 w-3.5" aria-hidden="true"></i>
                </button>
            </div>
        @endif

        @if ($flashError)
            <div data-flash class="flex items-start gap-3 rounded-[12px] border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-900">
                <i data-lucide="circle-alert" class="mt-0.5 h-4 w-4 shrink-0 text-red-600" aria-hidden="true"></i>
                <span class="flex-1">{{ $flashError }}</span>
                <button type="button" data-flash-dismiss aria-label="Dismiss message" class="-mr-1 shrink-0 rounded-md p-1 text-red-700/70 transition hover:bg-red-100 hover:text-red-900">
                    <i data-lucide="x" class="h-3.5 w-3.5" aria-hidden="true"></i>
                </button>
            </div>
        @endif

        @if (count($errorMessages) > 0)
            <div data-flash class="rounded-[12px] border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">
                <div class="flex items-start gap-3">
                    <i data-lucide="circle-alert" class="mt-0.5 h-4 w-4 shrink-0 text-red-600" aria-hidden="true"></i>
                    <div class="flex-1">
                        <p class="font-semibold">Please fix the following before continuing:</p>
                        <ul class="mt-1.5 space-y-1">
                            @foreach ($errorMessages as $errorMessage)
                                <li class="flex gap-2">
                                    <span aria-hidden="true">&middot;</span>
                                    <span>{{ $errorMessage }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button" data-flash-dismiss aria-label="Dismiss message" class="-mr-1 shrink-0 rounded-md p-1 text-red-700/70 transition hover:bg-red-100 hover:text-red-900">
                        <i data-lucide="x" class="h-3.5 w-3.5" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
        @endif
    </div>

    <script>
        document.querySelectorAll('[data-flash-dismiss]').forEach(function (button) {
            button.addEventListener('click', function () {
                var panel = button.closest('[data-flash]');
                if (panel) { panel.remove(); }
            });
        });
    </script>
@endif
