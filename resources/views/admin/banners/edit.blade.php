@extends('layouts.admin')

@section('page-title', 'Edit Banner')

@section('content')
<div class="max-w-4xl">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Edit Banner</h1>
        <a href="{{ route('admin.banners.index') }}" class="text-blue-600 hover:text-blue-800">← Back to Banners</a>
    </div>

    <form action="{{ route('admin.banners.update', $banner) }}" method="POST" enctype="multipart/form-data" class="card">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Title *</label>
                <input type="text" name="title" value="{{ old('title', $banner->title) }}" required
                       class="form-input">
                @error('title')<span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Subtitle</label>
                <textarea name="subtitle" rows="2" class="form-input">{{ old('subtitle', $banner->subtitle) }}</textarea>
                @error('subtitle')<span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>@enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Button Text</label>
                    <input type="text" name="button_text" value="{{ old('button_text', $banner->button_text) }}"
                           class="form-input">
                    @error('button_text')<span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Button Link</label>
                    <input type="text" name="button_link" value="{{ old('button_link', $banner->button_link) }}"
                           class="form-input">
                    @error('button_link')<span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>@enderror
                </div>
            </div>

            <!-- Existing Images -->
            @if($banner->images && count($banner->images) > 0)
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Existing Images</label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach($banner->images as $index => $image)
                        <div class="relative group">
                            <img src="{{ asset('storage/' . $image) }}" 
                                 alt="Banner image {{ $index + 1 }}" 
                                 class="w-full h-32 object-cover rounded border border-gray-200">
                            <input type="hidden" name="existing_images[]" value="{{ $image }}">
                            <button type="button" 
                                    onclick="removeImage(this, '{{ $image }}')"
                                    class="absolute top-2 right-2 bg-red-500 text-white p-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- New Images -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Add More Images</label>
                <input type="file" name="images[]" multiple accept="image/*"
                       class="form-input" id="imageInput">
                <p class="text-sm text-gray-500 mt-1">Select additional images to add to the slider</p>
                @error('images')<span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>@enderror
                @error('images.*')<span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>@enderror
                
                <div id="imagePreview" class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4"></div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Background Color (Tailwind classes)</label>
                <input type="text" name="background_color" value="{{ old('background_color', $banner->background_color) }}"
                       class="form-input">
                <p class="text-sm text-gray-500 mt-1">Use Tailwind gradient classes like: from-blue-500 via-blue-600 to-blue-700</p>
                @error('background_color')<span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>@enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sort Order</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $banner->sort_order) }}" min="0"
                           class="form-input">
                    @error('sort_order')<span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>@enderror
                </div>

                <div>
                    <label class="flex items-center cursor-pointer group mt-6">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $banner->is_active) ? 'checked' : '' }}
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                        <span class="ml-3 text-sm text-gray-700 group-hover:text-gray-900">Active</span>
                    </label>
                </div>
            </div>

            <div class="flex space-x-4 pt-4 border-t">
                <button type="submit" class="btn-primary">Update Banner</button>
                <a href="{{ route('admin.banners.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    const deleteImages = [];

    function removeImage(button, imagePath) {
        if (confirm('Are you sure you want to remove this image?')) {
            deleteImages.push(imagePath);
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'delete_images[]';
            hiddenInput.value = imagePath;
            document.querySelector('form').appendChild(hiddenInput);
            
            button.closest('.relative').remove();
        }
    }

    document.getElementById('imageInput').addEventListener('change', function(e) {
        const preview = document.getElementById('imagePreview');
        
        if (e.target.files) {
            Array.from(e.target.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'relative';
                    div.innerHTML = `
                        <img src="${e.target.result}" class="w-full h-32 object-cover rounded border border-gray-200">
                        <p class="text-xs text-gray-500 mt-1 truncate">${file.name}</p>
                    `;
                    preview.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
        }
    });
</script>
@endpush
@endsection


