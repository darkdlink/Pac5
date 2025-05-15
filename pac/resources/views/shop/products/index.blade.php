@extends('layouts.app')

@section('title', 'Produtos')

@section('content')
    <div class="container mx-auto px-4 py-12">
        <!-- Title and breadcrumbs -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Produtos para Skincare</h1>
            <nav class="text-sm text-gray-500">
                <ol class="flex items-center">
                    <li>
                        <a href="{{ route('home') }}" class="hover:text-pink-600">Home</a>
                    </li>
                    <li class="mx-2">/</li>
                    <li class="text-gray-700">Produtos</li>
                </ol>
            </nav>
        </div>

        <!-- Main content -->
        <div class="lg:flex">
            <!-- Sidebar / Filters -->
            <div class="lg:w-1/4 mb-8 lg:mb-0 lg:pr-8">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <form action="{{ route('shop.products.index') }}" method="GET">
                        <!-- Search -->
                        <div class="mb-6">
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                            <div class="relative">
                                <input
                                    type="text"
                                    id="search"
                                    name="search"
                                    value="{{ request('search') }}"
                                    placeholder="Nome ou descrição"
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:ring-pink-500 focus:border-pink-500"
                                >
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Categories -->
                        <div class="mb-6">
                            <h3 class="text-sm font-medium text-gray-700 mb-2">Categorias</h3>
                            <div class="space-y-2 max-h-48 overflow-y-auto">
                                @foreach($categories as $category)
                                    <div class="flex items-center">
                                        <input
                                            type="checkbox"
                                            id="category_{{ $category->id }}"
                                            name="categories[]"
                                            value="{{ $category->id }}"
                                            class="h-4 w-4 text-pink-600 border-gray-300 rounded focus:ring-pink-500"
                                            {{ in_array($category->id, request('categories', [])) ? 'checked' : '' }}
                                        >
                                        <label for="category_{{ $category->id }}" class="ml-2 text-sm text-gray-700">
                                            {{ $category->name }} <span class="text-gray-500">({{ $category->products_count }})</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Price Range -->
                        <div class="mb-6">
                            <h3 class="text-sm font-medium text-gray-700 mb-2">Faixa de Preço</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="price_min" class="sr-only">Preço Mínimo</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">R$</span>
                                        </div>
                                        <input
                                            type="number"
                                            id="price_min"
                                            name="price_min"
                                            value="{{ request('price_min') }}"
                                            placeholder="Mín"
                                            min="0"
                                            class="pl-10 pr-3 py-2 w-full border border-gray-300 rounded-md focus:ring-pink-500 focus:border-pink-500"
                                        >
                                    </div>
                                </div>
                                <div>
                                    <label for="price_max" class="sr-only">Preço Máximo</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">R$</span>
                                        </div>
                                        <input
                                            type="number"
                                            id="price_max"
                                            name="price_max"
                                            value="{{ request('price_max') }}"
                                            placeholder="Máx"
                                            min="0"
                                            class="pl-10 pr-3 py-2 w-full border border-gray-300 rounded-md focus:ring-pink-500 focus:border-pink-500"
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Rating -->
                        <div class="mb-6">
                            <h3 class="text-sm font-medium text-gray-700 mb-2">Avaliação</h3>
                            <div class="space-y-2">
                                @for($i = 5; $i >= 1; $i--)
                                    <div class="flex items-center">
                                        <input
                                            type="radio"
                                            id="rating_{{ $i }}"
                                            name="rating"
                                            value="{{ $i }}"
                                            class="h-4 w-4 text-pink-600 border-gray-300 focus:ring-pink-500"
                                            {{ request('rating') == $i ? 'checked' : '' }}
                                        >
                                        <label for="rating_{{ $i }}" class="ml-2 text-sm text-gray-700 flex items-center">
                                            <div class="flex items-center mr-1">
                                                @for($j = 1; $j <= 5; $j++)
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 {{ $j <= $i ? 'text-yellow-400' : 'text-gray-300' }}" viewBox="0 0 20 20" fill="currentColor">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                    </svg>
                                                @endfor
                                            </div>
                                            @if($i < 5)
                                                <span class="text-gray-500">ou superior</span>
                                            @endif
                                        </label>
                                    </div>
                                @endfor
                            </div>
                        </div>

                        <!-- Availability -->
                        <div class="mb-6">
                            <h3 class="text-sm font-medium text-gray-700 mb-2">Disponibilidade</h3>
                            <div class="space-y-2">
                                <div class="flex items-center">
                                    <input
                                        type="checkbox"
                                        id="in_stock"
                                        name="in_stock"
                                        value="1"
                                        class="h-4 w-4 text-pink-600 border-gray-300 rounded focus:ring-pink-500"
                                        {{ request('in_stock') ? 'checked' : '' }}
                                    >
                                    <label for="in_stock" class="ml-2 text-sm text-gray-700">
                                        Em estoque
                                    </label>
                                </div>
                                <div class="flex items-center">
                                    <input
                                        type="checkbox"
                                        id="on_sale"
                                        name="on_sale"
                                        value="1"
                                        class="h-4 w-4 text-pink-600 border-gray-300 rounded focus:ring-pink-500"
                                        {{ request('on_sale') ? 'checked' : '' }}
                                    >
                                    <label for="on_sale" class="ml-2 text-sm text-gray-700">
                                        Em promoção
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex space-x-3">
                            <button
                                type="submit"
                                class="flex-1 bg-pink-600 text-white py-2 px-4 rounded-md hover:bg-pink-700 transition-colors"
                            >
                                Aplicar Filtros
                            </button>
                            <a
                                href="{{ route('shop.products.index') }}"
                                class="flex-1 bg-gray-200 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-300 transition-colors text-center"
                            >
                                Limpar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="lg:w-3/4">
                <!-- Sorting and Count -->
                <div class="bg-white rounded-lg shadow-sm p-4 mb-6 flex flex-wrap items-center justify-between">
                    <div class="text-sm text-gray-600 mb-4 md:mb-0">
                        Exibindo {{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }} de {{ $products->total() }} produtos
                    </div>
                    <div class="flex items-center">
                        <span class="text-sm text-gray-600 mr-2">Ordenar por:</span>
                        <form action="{{ route('shop.products.index') }}" method="GET" id="sort-form">
                            @foreach(request()->except('sort', 'page') as $key => $value)
                                @if(is_array($value))
                                    @foreach($value as $item)
                                        <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                                    @endforeach
                                @else
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endif
                            @endforeach
                            <select
                                name="sort"
                                id="sort"
                                onchange="document.getElementById('sort-form').submit()"
                                class="border border-gray-300 rounded-md py-1.5 pl-3 pr-8 text-sm focus:outline-none focus:ring-pink-500 focus:border-pink-500"
                            >
                                <option value="relevance" {{ request('sort') == 'relevance' ? 'selected' : '' }}>Relevância</option>
                                <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Preço: Menor para Maior</option>
                                <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Preço: Maior para Menor</option>
                                <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Nome: A-Z</option>
                                <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Nome: Z-A</option>
                                <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Mais Recentes</option>
                                <option value="rating" {{ request('sort') == 'rating' ? 'selected' : '' }}>Melhor Avaliados</option>
                            </select>
                        </form>
                    </div>
                </div>

                <!-- Products List -->
                @if($products->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($products as $product)
                            <div class="bg-white rounded-lg shadow-sm overflow-hidden transition-transform hover:-translate-y-1">
                                <a href="{{ route('shop.products.show', $product) }}" class="block">
                                    <div class="relative h-64 overflow-hidden">
                                        <img
                                            src="{{ $product->thumbnail_url }}"
                                            alt="{{ $product->name }}"
                                            class="w-full h-full object-cover transition-transform hover:scale-105"
                                        >
                                        @if($product->compare_price && $product->compare_price > $product->price)
                                            <div class="absolute top-2 left-2 bg-pink-600 text-white text-xs px-2 py-1 rounded">
                                                -{{ round((($product->compare_price - $product->price) / $product->compare_price) * 100) }}%
                                            </div>
                                        @endif
                                        @if($product->stock <= 0)
                                            <div class="absolute top-2 right-2 bg-gray-800 text-white text-xs px-2 py-1 rounded">
                                                Esgotado
                                            </div>
                                        @endif
                                    </div>
                                </a>
                                <div class="p-4">
                                    @foreach($product->categories as $category)
                                        <span class="text-xs text-gray-500">{{ $category->name }}</span>
                                        @if(!$loop->last)
                                            <span class="text-xs text-gray-500 mx-1">•</span>
                                        @endif
                                    @endforeach

                                    <a href="{{ route('shop.products.show', $product) }}" class="block mt-2">
                                        <h3 class="text-lg font-medium text-gray-800 hover:text-pink-600">{{ $product->name }}</h3>
                                    </a>

                                    <div class="mt-2 flex items-center">
                                        <div class="flex items-center">
                                            @for($i = 1; $i <= 5; $i++)
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 {{ $i <= $product->rating_avg ? 'text-yellow-400' : 'text-gray-300' }}" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                </svg>
                                            @endfor
                                        </div>
                                        <span class="text-xs text-gray-500 ml-2">({{ $product->reviews_count }})</span>
                                    </div>

                                    <div class="mt-3 flex justify-between items-center">
                                        <div>
                                            @if($product->compare_price)
                                                <span class="text-sm text-gray-500 line-through">R$ {{ number_format($product->compare_price, 2, ',', '.') }}</span>
                                            @endif
                                            <span class="text-lg font-bold text-gray-800 block">R$ {{ number_format($product->price, 2, ',', '.') }}</span>
                                        </div>

                                        @if($product->stock > 0)
                                            <form action="{{ route('shop.cart.add') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                <input type="hidden" name="quantity" value="1">
                                                <button type="submit" class="bg-pink-600 text-white p-2.5 rounded-full hover:bg-pink-700 transition-colors">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @else
                                            <button disabled class="bg-gray-300 text-gray-500 p-2.5 rounded-full cursor-not-allowed">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="mt-8">
                        {{ $products->withQueryString()->links() }}
                    </div>
                @else
                    <div class="bg-white rounded-lg shadow-sm p-8 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum produto encontrado</h3>
                        <p class="text-gray-500 mb-6">Não encontramos produtos que correspondam aos filtros selecionados.</p>
                        <a href="{{ route('shop.products.index') }}" class="inline-flex items-center px-4 py-2 bg-pink-600 text-white rounded-md hover:bg-pink-700 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                            </svg>
                            Limpar filtros
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
