<tr data-order-id="{{ $order->id }}" class="transition hover:bg-orange-50/40">
    <td class="whitespace-nowrap px-5 py-4 sm:px-6">
        <p class="font-bold text-gray-900">{{ $order->order_code }}</p>
        @if ($order->items->isNotEmpty())
            <ul class="mt-2 space-y-1 text-xs text-gray-600">
                @foreach ($order->items as $item)
                    <li>{{ $item->menu_name }} × {{ $item->quantity }}</li>
                @endforeach
            </ul>
        @endif
    </td>
    <td class="px-5 py-4 font-medium text-gray-700">{{ $order->customer_name ?? 'Pelanggan' }}</td>
    <td class="whitespace-nowrap px-5 py-4 text-gray-600">
        <p>{{ $order->created_at?->format('d M Y') ?? '-' }}</p>
        <p class="mt-1 text-xs text-gray-400">{{ $order->created_at?->format('H:i') ?? '-' }}</p>
    </td>
    <td class="whitespace-nowrap px-5 py-4">
        <span @class([
            'inline-flex items-center rounded-full px-3 py-1.5 text-xs font-semibold ring-1 ring-inset',
            $statusStyles[$order->order_status] ?? 'bg-gray-50 text-gray-600 ring-gray-200',
        ])>{{ $statusLabels[$order->order_status] ?? $order->order_status }}</span>
    </td>
    <td class="whitespace-nowrap px-5 py-4 text-right">
        @if ($order->order_status === 'confirmed')
            <form action="{{ route('chef.orders.start', $order) }}" method="POST">
                @csrf
                <button type="submit" class="rounded-lg bg-orange-500 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2">Mulai Memasak</button>
            </form>
        @elseif ($order->order_status === 'in_progress')
            <form action="{{ route('chef.orders.complete', $order) }}" method="POST">
                @csrf
                <button type="submit" class="rounded-lg bg-green-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-600 focus:ring-offset-2">Tandai Selesai</button>
            </form>
        @else
            <span class="text-xs font-semibold text-green-700">Selesai</span>
        @endif
    </td>
</tr>
