<!-- Navbar -->
<nav class="bg-white shadow-sm">
    <div class="container mx-auto px-4 py-2">
        <div class="flex justify-between items-center">
            <!-- Logo and nav links -->
            <div class="flex items-center space-x-8">
                <a href="{{ route('home') }}" class="flex items-center">
                    <img src="{{ asset('images/logo.png') }}" alt="Esteticista Logo" class="h-10">
                    <span class="ml-2 text-lg font-semibold text-pink-600">BeautySpace</span>
                </a>

                <div class="hidden md:flex space-x-6">
                    <a href="{{ route('shop.products.index') }}" class="text-gray-700 hover:text-pink-600 transition">Produtos</a>
                    <a href="{{ route('shop.services.index') }}" class="text-gray-700 hover:text-pink-600 transition">Serviços</a>
                    <a href="#about" class="text-gray-700 hover:text-pink-600 transition">Sobre</a>
                    <a href="#contact" class="text-gray-700 hover:text-pink-600 transition">Contato</a>
                </div>
            </div>

            <!-- Right side navigation -->
            <div class="flex items-center space-x-4">
                <!-- Search -->
                <div class="hidden md:block relative">
                    <input
                        type="text"
                        placeholder="Buscar produtos..."
                        class="py-1 pl-3 pr-10 rounded-full border focus:outline-none focus:ring-2 focus:ring-pink-300 focus:border-pink-300 w-40 lg:w-64"
                    >
                    <button class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                </div>

                <!-- Cart -->
                <a href="{{ route('shop.cart') }}" class="text-gray-700 hover:text-pink-600 relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span class="absolute -top-2 -right-2 bg-pink-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                        {{ Cart::count() }}
                    </span>
                </a>

                <!-- User menu -->
                @guest
                    <a href="{{ route('login') }}" class="text-gray-700 hover:text-pink-600 ml-4">Entrar</a>
                    <a href="{{ route('register') }}" class="hidden md:inline-block ml-4 px-4 py-2 bg-pink-600 text-white rounded-md hover:bg-pink-700 transition">Cadastrar</a>
                @else
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center space-x-1 text-gray-700 hover:text-pink-600 focus:outline-none">
                            <img src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" class="h-8 w-8 rounded-full">
                            <span class="hidden md:inline-block">{{ Auth::user()->name }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10">
                            <a href="{{ route('shop.profile.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Meu Perfil</a>
                            <a href="{{ route('shop.orders.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Meus Pedidos</a>

                            @if(Auth::user()->is_admin)
                                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Painel Admin</a>
                            @endif

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Sair</button>
                            </form>
                        </div>
                    </div>
                @endguest

                <!-- Mobile menu button -->
                <button class="md:hidden text-gray-500 hover:text-gray-600 focus:outline-none" x-data="{}" @click="$dispatch('toggle-mobile-menu')">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile menu -->
    <div class="md:hidden hidden" x-data="{ open: false }" x-show="open" @toggle-mobile-menu.window="open = !open">
        <div class="px-2 pt-2 pb-3 space-y-1">
            <a href="{{ route('shop.products.index') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-pink-600 hover:bg-gray-50">Produtos</a>
            <a href="{{ route('shop.services.index') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-pink-600 hover:bg-gray-50">Serviços</a>
            <a href="#about" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-pink-600 hover:bg-gray-50">Sobre</a>
            <a href="#contact" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-pink-600 hover:bg-gray-50">Contato</a>

            <!-- Mobile search -->
            <div class="px-3 py-2">
                <input
                    type="text"
                    placeholder="Buscar produtos..."
                    class="w-full py-2 pl-3 pr-10 rounded-md border focus:outline-none focus:ring-2 focus:ring-pink-300 focus:border-pink-300"
                >
            </div>
        </div>
    </div>
</nav>
