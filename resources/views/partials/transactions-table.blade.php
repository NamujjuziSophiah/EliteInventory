@if(empty($rows) || count($rows) === 0)
    <p class="text-muted">No records found.</p>
@else
    <div class="table-responsive">
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Created</th>
                    <th>Reference</th>
                    <th>Items</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $r)
                    <tr>
                        <td>{{ $r->id ?? 'n/a' }}</td>
                        <td>{{ optional($r->created_at)->toDateTimeString() ?? '' }}</td>
                        <td>
                            @if(isset($r->supplier_name))
                                {{ $r->supplier_name }}
                            @elseif(isset($r->supplier) && $r->supplier)
                                {{ $r->supplier->name ?? '' }}
                            @else
                                {{ $r->reference ?? ($r->supplier_id ?? $r->customer_id ?? '-') }}
                            @endif
                        </td>
                        <td>
                            @if(isset($r->items) && count($r->items) > 0)
                                <ul class="small mb-0">
                                    @foreach($r->items as $it)
                                        <li>{{ optional($it->product)->name ?? 'Product #'.($it->product_id ?? $it->id ?? '') }} × {{ $it->qty ?? $it->quantity ?? '1' }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-end">{{ format_currency($r->total ?? 0) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
