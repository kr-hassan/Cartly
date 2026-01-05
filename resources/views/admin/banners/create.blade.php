@extends('layouts.admin')

@section('page-title', 'Create Banner')

@section('content')
<div class="max-w-4xl">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Create New Banner</h1>
        <a href="{{ route('admin.banners.index') }}" class="text-blue-600 hover:text-blue-800">← Back to Banners</a>
    </div>

    <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data" class="card">
        @csrf

        <div class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Title *</label>
                <input type="text" name="title" value="{{ old('title') }}" required
                       class="form-input" placeholder="e.g., 10% Instant Cashback">
                @error('title')<span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Subtitle</label>
                <textarea name="subtitle" rows="2" class="form-input" 
                          placeholder="e.g., Shop now and get instant cashback on all purchases!">{{ old('subtitle') }}</textarea>
                @error('subtitle')<span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>@enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Button Text</label>
                    <input type="text" name="button_text" value="{{ old('button_text', 'Shop Now') }}"
                           class="form-input" placeholder="Shop Now">
                    @error('button_text')<span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Button Link</label>
                    <input type="text" name="button_link" value="{{ old('button_link', route('products.index')) }}"
                           class="form-input" placeholder="{{ route('products.index') }}">
                    @error('button_link')<span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>@enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Images * (Multiple images will create a slider)</label>
                <input type="file" name="images[]" multiple accept="image/*" required
                       class="form-input" id="imageInput">
                <p class="text-sm text-gray-500 mt-1">You can select multiple images. First image will be shown by default.</p>
                @error('images')<span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>@enderror
                @error('images.*')<span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>@enderror
                
                <div id="imagePreview" class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4"></div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Background Color (Tailwind classes)</label>
                <input type="text" name="background_color" value="{{ old('background_color', 'from-pink-500 via-pink-600 to-rose-600') }}"
                       class="form-input" placeholder="from-pink-500 via-pink-600 to-rose-600">
                <p class="text-sm text-gray-500 mt-1">Use Tailwind gradient classes like: from-blue-500 via-blue-600 to-blue-700</p>
                @error('background_color')<span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>@enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sort Order</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                           class="form-input">
                    <p class="text-sm text-gray-500 mt-1">Lower numbers appear first</p>
                    @error('sort_order')<span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>@enderror
                </div>

                <div>
                    <label class="flex items-center cursor-pointer group mt-6">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                        <span class="ml-3 text-sm text-gray-700 group-hover:text-gray-900">Active</span>
                    </label>
                </div>
            </div>

            <div class="flex space-x-4 pt-4 border-t">
                <button type="submit" class="btn-primary">Create Banner</button>
                <a href="{{ route('admin.banners.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.getElementById('imageInput').addEventListener('change', function(e) {
        const preview = document.getElementById('imagePreview');
        preview.innerHTML = '';
        
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


