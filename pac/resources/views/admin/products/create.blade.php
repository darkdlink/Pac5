@extends('layouts.admin')

@section('title', isset($product) ? 'Editar Produto' : 'Novo Produto')

@section('content')
    <div class="container mx-auto px-4">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">
                    {{ isset($product) ? 'Editar Produto' : 'Novo Produto' }}
                </h1>
                @if(isset($product))
                    <p class="text-sm text-gray-500 mt-1">Última atualização: {{ $product->updated_at->format('d/m/Y H:i') }}</p>
                @endif
            </div>
            <a
                href="{{ route('admin.products.index') }}"
                class="text-gray-600 hover:text-gray-900 flex items-center"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M7.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                Voltar para Produtos
            </a>
        </div>

        <!-- Form -->
        <form
            action="{{ isset($product) ? route('admin.products.update', $product) : route('admin.products.store') }}"
            method="POST"
            enctype="multipart/form-data"
            class="bg-white rounded-lg shadow-sm p-6"
        >
            @csrf
            @if(isset($product))
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Left Column - Basic Info -->
                <div class="md:col-span-2 space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                            Nome do Produto <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name', isset($product) ? $product->name : '') }}"
                            class="w-full rounded-md border-gray-300 focus:border-pink-500 focus:ring focus:ring-pink-200 @error('name') border-red-300 @enderror"
                            required
                        >
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="sku" class="block text-sm font-medium text-gray-700 mb-1">
                            SKU <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="sku"
                            name="sku"
                            value="{{ old('sku', isset($product) ? $product->sku : '') }}"
                            class="w-full rounded-md border-gray-300 focus:border-pink-500 focus:ring focus:ring-pink-200 @error('sku') border-red-300 @enderror"
                            required
                        >
                        @error('sku')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                            Descrição <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            id="description"
                            name="description"
                            rows="4"
                            class="w-full rounded-md border-gray-300 focus:border-pink-500 focus:ring focus:ring-pink-200 @error('description') border-red-300 @enderror"
                            required
                        >{{ old('description', isset($product) ? $product->description : '') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700 mb-1">
                                Preço (R$) <span class="text-red-500">*</span>
                            </label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">R$</span>
                                </div>
                                <input
                                    type="number"
                                    step="0.01"
                                    id="price"
                                    name="price"
                                    value="{{ old('price', isset($product) ? $product->price : '') }}"
                                    class="pl-10 w-full rounded-md border-gray-300 focus:border-pink-500 focus:ring focus:ring-pink-200 @error('price') border-red-300 @enderror"
                                    required
                                >
                            </div>
                            @error('price')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="compare_price" class="block text-sm font-medium text-gray-700 mb-1">
                                Preço Comparativo (R$)
                            </label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">R$</span>
                                </div>
                                <input
                                    type="number"
                                    step="0.01"
                                    id="compare_price"
                                    name="compare_price"
                                    value="{{ old('compare_price', isset($product) ? $product->compare_price : '') }}"
                                    class="pl-10 w-full rounded-md border-gray-300 focus:border-pink-500 focus:ring focus:ring-pink-200 @error('compare_price') border-red-300 @enderror"
                                >
                            </div>
                            <p class="text-sm text-gray-500 mt-1">Deixe em branco se não houver desconto</p>
                            @error('compare_price')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="stock" class="block text-sm font-medium text-gray-700 mb-1">
                                Estoque <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="number"
                                min="0"
                                id="stock"
                                name="stock"
                                value="{{ old('stock', isset($product) ? $product->stock : '') }}"
                                class="w-full rounded-md border-gray-300 focus:border-pink-500 focus:ring focus:ring-pink-200 @error('stock') border-red-300 @enderror"
                                required
                            >
                            @error('stock')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="weight" class="block text-sm font-medium text-gray-700 mb-1">
                                Peso (g)
                            </label>
                            <input
                                type="number"
                                min="0"
                                id="weight"
                                name="weight"
                                value="{{ old('weight', isset($product) ? $product->weight : '') }}"
                                class="w-full rounded-md border-gray-300 focus:border-pink-500 focus:ring focus:ring-pink-200 @error('weight') border-red-300 @enderror"
                            >
                            @error('weight')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Categorias <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 max-h-56 overflow-y-auto border border-gray-300 rounded-md p-3">
                            @foreach($categories as $category)
                                <div class="flex items-start">
                                    <input
                                        type="checkbox"
                                        id="category_{{ $category->id }}"
                                        name="categories[]"
                                        value="{{ $category->id }}"
                                        class="mt-1 rounded border-gray-300 text-pink-600 focus:ring-pink-500 focus:ring-opacity-50"
                                        {{ (isset($product) && $product->categories->contains($category->id)) || (is_array(old('categories')) && in_array($category->id, old('categories'))) ? 'checked' : '' }}
                                    >
                                    <label for="category_{{ $category->id }}" class="ml-2 block text-sm text-gray-700">
                                        {{ $category->name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @error('categories')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Right Column - Status and Images -->
                <div class="space-y-6">
                    <div class="bg-gray-50 p-4 rounded-md border">
                        <div class="mb-4">
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                                Status
                            </label>
                            <select
                                id="status"
                                name="status"
                                class="w-full rounded-md border-gray-300 focus:border-pink-500 focus:ring focus:ring-pink-200"
                            >
                                <option value="active" {{ old('status', isset($product) ? $product->status : '') == 'active' ? 'selected' : '' }}>
                                    Ativo
                                </option>
                                <option value="inactive" {{ old('status', isset($product) ? $product->status : '') == 'inactive' ? 'selected' : '' }}>
                                    Inativo
                                </option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="featured" class="flex items-center">
                                <input
                                    type="checkbox"
                                    id="featured"
                                    name="featured"
                                    value="1"
                                    class="rounded border-gray-300 text-pink-600 focus:ring-pink-500 focus:ring-opacity-50"
                                    {{ old('featured', isset($product) ? $product->featured : '') ? 'checked' : '' }}
                                >
                                <span class="ml-2 text-sm text-gray-700">Produto em destaque</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Imagem principal <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center justify-center border-2 border-dashed border-gray-300 rounded-md p-4 mb-2">
                            <div class="space-y-2 text-center">
                                @if(isset($product) && $product->thumbnail_url)
                                    <img src="{{ $product->thumbnail_url }}" alt="Thumbnail" class="mx-auto h-32 w-32 object-cover rounded-md">
                                    <p class="text-xs text-gray-500">Clique abaixo para substituir</p>
                                @else
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <p class="text-xs text-gray-500">PNG, JPG, GIF até 2MB</p>
                                @endif

                                <div class="flex justify-center text-sm">
                                    <label for="thumbnail" class="relative cursor-pointer bg-white rounded-md font-medium text-pink-600 hover:text-pink-500 focus-within:outline-none">
                                        <span>Selecionar arquivo</span>
                                        <input
                                            id="thumbnail"
                                            name="thumbnail"
                                            type="file"
                                            class="sr-only"
                                            accept="image/*"
                                            {{ isset($product) ? '' : 'required' }}
                                        >
                                    </label>
                                </div>
                            </div>
                        </div>
                        @error('thumbnail')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Imagens adicionais
                        </label>
                        <div class="border-2 border-dashed border-gray-300 rounded-md p-4">
                            <!-- Current Images (if editing) -->
                            @if(isset($product) && $product->images->count())
                                <div class="grid grid-cols-2 gap-2 mb-4">
                                    @foreach($product->images as $image)
                                        <div class="relative group">
                                            <img src="{{ $image->url }}" alt="Product Image" class="h-24 w-full object-cover rounded-md">
                                            <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                                <input
                                                    type="checkbox"
                                                    id="remove_image_{{ $image->id }}"
                                                    name="remove_images[]"
                                                    value="{{ $image->id }}"
                                                    class="rounded border-gray-300 text-red-600 focus:ring-red-500 focus:ring-opacity-50"
                                                >
                                                <label for="remove_image_{{ $image->id }}" class="ml-2 text-xs text-white">
                                                    Remover
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Upload New Images -->
                            <div class="flex flex-col items-center justify-center">
                                <svg class="mx-auto h-10 w-10 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <p class="text-sm text-gray-500 mb-2">Selecione até 5 imagens</p>
                                <div class="flex items-center">
                                    <label for="images" class="cursor-pointer bg-white py-1 px-3 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">
                                        <span>Selecionar arquivos</span>
                                        <input
                                            id="images"
                                            name="images[]"
                                            type="file"
                                            class="sr-only"
                                            accept="image/*"
                                            multiple
                                        >
                                    </label>
                                </div>
                            </div>
                            @error('images')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            @error('images.*')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Meta Data Section -->
            <div class="mt-8 pt-8 border-t border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Metadados para SEO (Opcional)</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="meta_title" class="block text-sm font-medium text-gray-700 mb-1">
                            Meta Título
                        </label>
                        <input
                            type="text"
                            id="meta_title"
                            name="meta_title"
                            value="{{ old('meta_title', isset($product) ? $product->meta_title : '') }}"
                            class="w-full rounded-md border-gray-300 focus:border-pink-500 focus:ring focus:ring-pink-200"
                        >
                        <p class="text-xs text-gray-500 mt-1">Se vazio, o nome do produto será usado</p>
                    </div>

                    <div>
                        <label for="meta_description" class="block text-sm font-medium text-gray-700 mb-1">
                            Meta Descrição
                        </label>
                        <textarea
                            id="meta_description"
                            name="meta_description"
                            rows="3"
                            class="w-full rounded-md border-gray-300 focus:border-pink-500 focus:ring focus:ring-pink-200"
                        >{{ old('meta_description', isset($product) ? $product->meta_description : '') }}</textarea>
                        <p class="text-xs text-gray-500 mt-1">Máximo de 160 caracteres</p>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="mt-8 pt-5 border-t border-gray-200 flex justify-end">
                <button
                    type="button"
                    onclick="history.back()"
                    class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 mr-3"
                >
                    Cancelar
                </button>
                <button
                    type="submit"
                    class="bg-pink-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500"
                >
                    {{ isset($product) ? 'Atualizar Produto' : 'Criar Produto' }}
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        // Preview upload images
        document.addEventListener('DOMContentLoaded', function() {
            // Preview thumbnail
            const thumbnailInput = document.getElementById('thumbnail');
            if (thumbnailInput) {
                thumbnailInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const container = thumbnailInput.closest('div').querySelector('div');
                            const existingImg = container.querySelector('img');
                            const existingText = container.querySelector('p');
                            const existingSvg = container.querySelector('svg');

                            if (existingImg) {
                                existingImg.src = e.target.result;
                            } else {
                                if (existingSvg) existingSvg.remove();
                                if (existingText) existingText.remove();

                                const img = document.createElement('img');
                                img.src = e.target.result;
                                img.className = 'mx-auto h-32 w-32 object-cover rounded-md';
                                img.alt = 'Thumbnail Preview';
                                container.prepend(img);

                                const text = document.createElement('p');
                                text.className = 'text-xs text-gray-500';
                                text.textContent = 'Clique abaixo para substituir';
                                img.insertAdjacentElement('afterend', text);
                            }
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
        });
    </script>
@endpush
