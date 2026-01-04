@extends('layouts.admin')

@section('page-title', 'Coupons')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Coupons</h1>
    <a href="{{ route('admin.coupons.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
        Add New Coupon
    </a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Value</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usage</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach($coupons as $coupon)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap font-mono font-semibold">{{ $coupon->code }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $coupon->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ ucfirst($coupon->type) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        @if($coupon->type === 'percentage')
                            {{ $coupon->value }}%
                        @else
                            ${{ number_format($coupon->value, 2) }}
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        {{ $coupon->used_count }}/{{ $coupon->usage_limit ?? '∞' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($coupon->isValid())
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Valid</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Invalid</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{ route('admin.coupons.edit', $coupon) }}" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                        <form action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $coupons->links() }}
</div>
@endsection

