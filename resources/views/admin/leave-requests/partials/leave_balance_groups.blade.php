@php
    $quotas = collect($quotas ?? []);
    $unconditional = $quotas->filter(
        fn ($q) => ($q['leave_condition'] ?? 'unconditional') !== 'conditional'
    );
    $conditional = $quotas->filter(
        fn ($q) => ($q['leave_condition'] ?? '') === 'conditional'
    );
@endphp

@if($quotas->isEmpty())
    <div class="col-12 text-center py-2 opacity-50 small">{{ $emptyMessage ?? 'No leave quotas assigned' }}</div>
@else
    @if($unconditional->isNotEmpty())
        <div class="col-12">
            <div class="small fw-semibold text-white-50 mb-2">Unconditional Leaves</div>
            <div class="row g-2">
                @foreach($unconditional as $quota)
                    <div class="col-6">
                        <div class="small">{{ $quota['type'] }}: <strong>{{ $quota['remaining'] }}</strong> days</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($conditional->isNotEmpty())
        <div class="col-12 {{ $unconditional->isNotEmpty() ? 'mt-3' : '' }}">
            <div class="small fw-semibold text-white-50 mb-2">Conditional Leaves</div>
            <div class="row g-2">
                @foreach($conditional as $quota)
                    <div class="col-6">
                        <div class="small">{{ $quota['type'] }}: <strong>{{ $quota['remaining'] }}</strong> days</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endif
