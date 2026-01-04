@extends('layouts.admin')

@section('page-title', 'Banners')
@section('page-subtitle', 'Manage Homepage Banners')

@section('content')
<div class="card">
    <div class="p-6 flex justify-between items-center border-b border-gray-200">
        <h2 class="text-xl font-bold text-gray-900">All Banners</h2>
        <a href="{{ route('admin.banners.create') }}" class="btn-primary">
            <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Create Banner
        </a>
    </div>

    @if($banners->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Images</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sort Order</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($banners as $banner)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $banner->title }}</div>
                                @if($banner->subtitle)
                                    <div class="text-sm text-gray-500">{{ Str::limit($banner->subtitle, 50) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex space-x-2">
                                    @if($banner->images && count($banner->images) > 0)
                                        @foreach(array_slice($banner->images, 0, 3) as $image)
                                            <img src="{{ asset('storage/' . $image) }}" 
                                                 alt="Banner image" 
                                                 class="w-16 h-16 object-cover rounded border border-gray-200">
                                        @endforeach
                                        @if(count($banner->images) > 3)
                                            <div class="w-16 h-16 bg-gray-100 rounded border border-gray-200 flex items-center justify-center text-xs text-gray-500">
                                                +{{ count($banner->images) - 3 }}
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-sm text-gray-400">No images</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $banner->sort_order }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($banner->is_active)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Inactive</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="{{ route('admin.banners.edit', $banner) }}" 
                                       class="text-blue-600 hover:text-blue-900">Edit</a>
                                    <form method="POST" action="{{ route('admin.banners.destroy', $banner) }}" 
                                          class="inline"
                                          onsubmit="return confirm('Are you sure you want to delete this banner?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="p-12 text-center">
            <svg class="w-24 h-24 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No banners found</h3>
            <p class="text-gray-600 mb-6">Create your first banner to display on the homepage.</p>
            <a href="{{ route('admin.banners.create') }}" class="btn-primary inline-block">
                Create Banner
            </a>
        </div>
    @endif
</div>
@endsection

