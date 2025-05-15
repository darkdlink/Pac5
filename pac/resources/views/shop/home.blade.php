@extends('layouts.app')

@section('title', 'Home')

@section('content')
    <!-- Hero Section -->
    <section class="relative bg-pink-50">
        <div class="container mx-auto px-4 py-16 flex flex-col md:flex-row items-center">
            <!-- Text Content -->
            <div class="md:w-1/2 md:pr-12 mb-10 md:mb-0">
                <h1 class="text-4xl md:text-5xl font-bold text-gray-800 leading-tight mb-6">
                    Descubra a Beleza Natural da Sua Pele
                </h1>
                <p class="text-lg text-gray-600 mb-8">
                    Cuide da sua pele com os melhores produtos e tratamentos estéticos. Realce sua beleza natural com nossa linha de skincare profissional.
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('shop.products.index') }}" class="bg-pink-600 text-white px-8 py-3 rounded-md hover:bg-pink-700 transition-colors text-lg font-medium">
                        Ver Produtos
                    </a>
                    <a href="{{ route('shop.services.index') }}" class="bg-white text-pink-600 border border-pink-600 px-8 py-3 rounded-md hover:bg-pink-50 transition-colors text-lg font-medium">
                        Nossos Serviços
                    </a>
                </div>
            </div>

            <!-- Hero Image -->
            <div class="md:w-1/2">
                <img
                    src="{{ asset('images/hero-skincare.jpg') }}"
                    alt="BeautySpace Skincare"
                    class="rounded-lg shadow-xl"
                >
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center mb-10">
                <h2 class="text-3xl font-bold text-gray-800">Produtos em Destaque</h2>
                <a href="{{ route('shop.products.index') }}" class="text-pink-600 hover:text-pink-700 font-medium flex items-center">
                    Ver todos
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
                @foreach($featuredProducts as $product)
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden transition-transform hover:-translate-y-1">
                        <a href="{{ route('shop.products.show', $product) }}" class="block">
                            <div class="h-64 overflow-hidden">
                                <img
                                    src="{{ $product->thumbnail_url }}"
                                    alt="{{ $product->name }}"
                                    class="w-full h-full object-cover transition-transform hover:scale-105"
                                >
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
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">Nossos Serviços Estéticos</h2>
                <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                    Oferecemos uma variedade de tratamentos estéticos personalizados para realçar sua beleza natural e cuidar da saúde da sua pele.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach($featuredServices as $service)
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden transition-transform hover:-translate-y-1">
                        <div class="h-48 overflow-hidden">
                            <img
                                src="{{ $service->image_url }}"
                                alt="{{ $service->name }}"
                                class="w-full h-full object-cover transition-transform hover:scale-105"
                            >
                        </div>

                        <div class="p-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-2">{{ $service->name }}</h3>
                            <p class="text-gray-600 mb-4">{{ Str::limit($service->description, 100) }}</p>
                            <div class="flex justify-between items-center">
                                <span class="text-xl font-bold text-gray-800">R$ {{ number_format($service->price, 2, ',', '.') }}</span>
                                <a href="{{ route('shop.services.show', $service) }}" class="text-pink-600 hover:text-pink-700 font-medium">
                                    Saiba mais
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="text-center mt-10">
                <a href="{{ route('shop.services.index') }}" class="inline-block bg-white text-pink-600 border border-pink-600 px-8 py-3 rounded-md hover:bg-pink-50 transition-colors text-lg font-medium">
                    Ver Todos os Serviços
                </a>
            </div>
        </div>
    </section>

    <!-- Instagram Feed -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">Siga-nos no Instagram</h2>
                <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                    Acompanhe nosso trabalho e fique por dentro das novidades em tratamentos e produtos para skincare.
                </p>
            </div>

            <div class="instagram-feed grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach($instagramFeed as $post)
                    <a href="{{ $post->permalink }}" target="_blank" rel="noopener noreferrer" class="block relative group overflow-hidden aspect-square">
                        <img
                            src="{{ $post->media_url }}"
                            alt="Instagram Post"
                            class="w-full h-full object-cover transform transition-transform group-hover:scale-110"
                        >
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-3">
                            <span class="text-white text-sm truncate">{{ Str::limit($post->caption, 50) }}</span>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="text-center mt-10">
                <a href="https://instagram.com/beautyspace" target="_blank" rel="noopener noreferrer" class="inline-flex items-center text-pink-600 hover:text-pink-700 font-medium">
                    <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                    </svg>
                    Veja mais no nosso Instagram
                </a>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="py-16 bg-pink-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">O Que Nossos Clientes Dizem</h2>
                <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                    Veja a experiência de quem já utilizou nossos produtos e serviços.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach($testimonials as $testimonial)
                    <div class="bg-white p-6 rounded-lg shadow-sm">
                        <div class="flex items-center mb-4">
                            <div class="flex items-center mr-3">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 {{ $i <= $testimonial->rating ? 'text-yellow-400' : 'text-gray-300' }}" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                @endfor
                            </div>
                            <span class="text-gray-600 text-sm">{{ $testimonial->created_at->format('d/m/Y') }}</span>
                        </div>
                        <blockquote class="text-gray-700 mb-4">{{ $testimonial->content }}</blockquote>
                        <div class="flex items-center">
                            <img
                                src="{{ $testimonial->user->profile_photo_url }}"
                                alt="{{ $testimonial->user->name }}"
                                class="h-10 w-10 rounded-full mr-3 object-cover"
                            >
                            <div>
                                <div class="font-medium text-gray-800">{{ $testimonial->user->name }}</div>
                                <div class="text-sm text-gray-500">{{ $testimonial->user->city }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Newsletter -->
    <section class="bg-pink-600 py-16">
        <div class="container mx-auto px-4">
            <div class="max-w-3xl mx-auto text-center">
                <h2 class="text-3xl font-bold text-white mb-4">Inscreva-se em Nossa Newsletter</h2>
                <p class="text-lg text-pink-100 mb-8">
                    Receba dicas de skincare, novidades sobre produtos e ofertas exclusivas diretamente no seu e-mail.
                </p>
                <form action="{{ route('newsletter.subscribe') }}" method="POST" class="flex flex-col md:flex-row gap-3 max-w-lg mx-auto">
                    @csrf
                    <input
                        type="email"
                        name="email"
                        placeholder="Seu melhor e-mail"
                        class="px-4 py-3 rounded-md flex-1 focus:outline-none focus:ring-2 focus:ring-pink-300"
                        required
                    >
                    <button
                        type="submit"
                        class="bg-gray-800 text-white px-6 py-3 rounded-md hover:bg-gray-700 transition-colors font-medium"
                    >
                        Inscrever-se
                    </button>
                </form>
            </div>
        </div>
    </section>
@endsection
