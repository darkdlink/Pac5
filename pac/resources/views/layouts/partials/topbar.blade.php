<!-- Top navigation for admin panel -->
<header class="bg-white shadow-sm">
    <div class="flex justify-between items-center py-4 px-6">
        <!-- Left side: Mobile menu button -->
        <div class="flex items-center">
            <button
                @click="sidebarOpen = !sidebarOpen"
                class="text-gray-500 focus:outline-none md:hidden"
                x-data="{ sidebarOpen: false }"
            >
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4 6H20M4 12H20M4 18H11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </button>

            <span class="text-xl font-semibold text-gray-800 ml-2 md:hidden">
                Beauty<span class="text-pink-500">Space</span>
            </span>
        </div>

        <!-- Right side: User profile and notifications -->
        <div class="flex items-center space-x-6">
            <!-- Search -->
            <div class="relative hidden md:block">
                <input
                    type="text"
                    placeholder="Buscar..."
                    class="bg-gray-100 text-gray-700 rounded-lg pl-10 pr-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500 w-64"
                >
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-500" viewBox="0 0 20 20" fill="none" stroke="currentColor">
                        <path d="M8 4a4 4 0 100 8 4 4 0 000-8z"></path>
                        <path d="M12.5 7.5l3-3"></path>
                    </svg>
                </div>
            </div>

            <!-- Notifications -->
            <button class="p-1 rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-pink-500 relative">
                <span class="sr-only">Notificações</span>
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>

                @if($newNotifications = Auth::user()->unreadNotifications->count())
                    <span class="absolute top-0 right-0 block h-5 w-5 rounded-full bg-pink-500 text-white text-xs text-center leading-5">
                        {{ $newNotifications > 9 ? '9+' : $newNotifications }}
                    </span>
                @endif
            </button>

            <!-- Profile dropdown -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center space-x-2 focus:outline-none">
                    <img src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" class="h-8 w-8 rounded-full">
                    <div class="hidden md:block text-left">
                        <span class="block text-sm font-medium text-gray-700">{{ Auth::user()->name }}</span>
                        <span class="block text-xs text-gray-500">Administrador</span>
                    </div>
                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>

                <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                    <a href="{{ route('admin.profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        Meu Perfil
                    </a>
                    <a href="{{ route('admin.settings.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        Configurações
                    </a>
                    <div class="border-t border-gray-100 my-1"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            Sair
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
