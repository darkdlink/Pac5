@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="container mx-auto px-4">
        <!-- Page heading -->
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-2xl font-semibold text-gray-800">Dashboard</h1>
            <div class="flex space-x-4">
                <div class="relative">
                    <select class="appearance-none bg-white border border-gray-300 rounded-md pl-3 pr-10 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500 text-gray-600 cursor-pointer">
                        <option value="today">Hoje</option>
                        <option value="week">Esta semana</option>
                        <option value="month" selected>Este mês</option>
                        <option value="year">Este ano</option>
                        <option value="custom">Personalizado</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-400">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>
                <button class="bg-pink-600 text-white px-4 py-2 rounded-md hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-pink-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Exportar
                </button>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Revenue -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm font-medium text-gray-500">Receita Total</h3>
                    <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">+{{ $revenueGrowth }}%</span>
                </div>
                <div class="flex items-center">
                    <span class="text-2xl font-bold text-gray-800">R$ {{ number_format($totalRevenue, 2, ',', '.') }}</span>
                </div>
                <div class="mt-2 text-sm text-gray-600">
                    Comparado a <span class="text-gray-900 font-medium">R$ {{ number_format($lastPeriodRevenue, 2, ',', '.') }}</span> no período anterior
                </div>
                <div class="mt-4 h-1 w-full bg-gray-200 rounded-full overflow-hidden">
                    <div class="bg-green-500 h-1 rounded-full" style="width: {{ min($revenueGrowth, 100) }}%"></div>
                </div>
            </div>

            <!-- Orders -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm font-medium text-gray-500">Total de Pedidos</h3>
                    <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">+{{ $ordersGrowth }}%</span>
                </div>
                <div class="flex items-center">
                    <span class="text-2xl font-bold text-gray-800">{{ $totalOrders }}</span>
                </div>
                <div class="mt-2 text-sm text-gray-600">
                    Comparado a <span class="text-gray-900 font-medium">{{ $lastPeriodOrders }}</span> no período anterior
                </div>
                <div class="mt-4 h-1 w-full bg-gray-200 rounded-full overflow-hidden">
                    <div class="bg-blue-500 h-1 rounded-full" style="width: {{ min($ordersGrowth, 100) }}%"></div>
                </div>
            </div>

            <!-- Average Order Value -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm font-medium text-gray-500">Ticket Médio</h3>
                    <span class="bg-amber-100 text-amber-800 text-xs px-2 py-1 rounded-full">+{{ $aovGrowth }}%</span>
                </div>
                <div class="flex items-center">
                    <span class="text-2xl font-bold text-gray-800">R$ {{ number_format($averageOrderValue, 2, ',', '.') }}</span>
                </div>
                <div class="mt-2 text-sm text-gray-600">
                    Comparado a <span class="text-gray-900 font-medium">R$ {{ number_format($lastPeriodAOV, 2, ',', '.') }}</span> no período anterior
                </div>
                <div class="mt-4 h-1 w-full bg-gray-200 rounded-full overflow-hidden">
                    <div class="bg-amber-500 h-1 rounded-full" style="width: {{ min($aovGrowth, 100) }}%"></div>
                </div>
            </div>

            <!-- New Customers -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm font-medium text-gray-500">Novos Clientes</h3>
                    <span class="bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded-full">+{{ $customersGrowth }}%</span>
                </div>
                <div class="flex items-center">
                    <span class="text-2xl font-bold text-gray-800">{{ $newCustomers }}</span>
                </div>
                <div class="mt-2 text-sm text-gray-600">
                    Comparado a <span class="text-gray-900 font-medium">{{ $lastPeriodCustomers }}</span> no período anterior
                </div>
                <div class="mt-4 h-1 w-full bg-gray-200 rounded-full overflow-hidden">
                    <div class="bg-purple-500 h-1 rounded-full" style="width: {{ min($customersGrowth, 100) }}%"></div>
                </div>
            </div>
        </div>

        <!-- Charts and tables section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Revenue Chart -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-medium text-gray-800">Receita (últimos 30 dias)</h3>
                    <div class="relative">
                        <select class="appearance-none bg-white border border-gray-300 rounded-md pl-3 pr-8 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-pink-500 text-gray-600 cursor-pointer">
                            <option value="daily">Diário</option>
                            <option value="weekly">Semanal</option>
                            <option value="monthly">Mensal</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-400">
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="h-64">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <!-- Top Products -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-medium text-gray-800">Produtos Mais Vendidos</h3>
                    <a href="{{ route('admin.reports.products') }}" class="text-pink-600 hover:text-pink-700 text-sm">Ver todos</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-3 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produto</th>
                                <th class="px-3 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Vendas</th>
                                <th class="px-3 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Receita</th>
                                <th class="px-3 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Estoque</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($topProducts as $product)
                                <tr>
                                    <td class="px-3 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <img class="h-10 w-10 rounded-md object-cover" src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}">
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                                <div class="text-sm text-gray-500">SKU: {{ $product->sku }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap text-right text-sm font-medium">{{ $product->total_sales }}</td>
                                    <td class="px-3 py-4 whitespace-nowrap text-right text-sm font-medium">R$ {{ number_format($product->revenue, 2, ',', '.') }}</td>
                                    <td class="px-3 py-4 whitespace-nowrap text-right">
                                        @if($product->stock > 10)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                {{ $product->stock }} unid.
                                            </span>
                                        @elseif($product->stock > 0)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                {{ $product->stock }} unid.
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Esgotado
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Orders and Low Stock -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recent Orders -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-medium text-gray-800">Pedidos Recentes</h3>
                    <a href="{{ route('admin.orders.index') }}" class="text-pink-600 hover:text-pink-700 text-sm">Ver todos</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-3 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pedido</th>
                                <th class="px-3 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                <th class="px-3 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th class="px-3 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($recentOrders as $order)
                                <tr>
                                    <td class="px-3 py-4 whitespace-nowrap">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="text-pink-600 hover:text-pink-700">
                                            #{{ $order->id }}
                                        </a>
                                        <div class="text-xs text-gray-500">{{ $order->created_at->format('d/m/Y H:i') }}</div>
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $order->user->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $order->user->email }}</div>
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        R$ {{ number_format($order->total, 2, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap text-right">
                                        @if($order->status === 'pending')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Pendente
                                            </span>
                                        @elseif($order->status === 'processing')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                Em processamento
                                            </span>
                                        @elseif($order->status === 'completed')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Concluído
                                            </span>
                                        @elseif($order->status === 'cancelled')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Cancelado
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Low Stock Products -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-medium text-gray-800">Produtos com Estoque Baixo</h3>
                    <a href="{{ route('admin.inventory.index') }}" class="text-pink-600 hover:text-pink-700 text-sm">Gerenciar Estoque</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-3 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produto</th>
                                <th class="px-3 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                <th class="px-3 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Estoque</th>
                                <th class="px-3 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($lowStockProducts as $product)
                                <tr>
                                    <td class="px-3 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <img class="h-10 w-10 rounded-md object-cover" src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}">
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap text-right text-sm font-medium">{{ $product->sku }}</td>
                                    <td class="px-3 py-4 whitespace-nowrap text-right">
                                        @if($product->stock > 0)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                {{ $product->stock }} unid.
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Esgotado
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap text-right text-sm">
                                        <a href="{{ route('admin.inventory.edit', $product) }}" class="text-blue-600 hover:text-blue-900 mr-3">Atualizar</a>
                                        <a href="{{ route('admin.products.edit', $product) }}" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($revenueChartLabels) !!},
                datasets: [{
                    label: 'Receita',
                    data: {!! json_encode($revenueChartData) !!},
                    backgroundColor: 'rgba(236, 72, 153, 0.1)',
                    borderColor: 'rgba(236, 72, 153, 1)',
                    borderWidth: 2,
                    tension: 0.3,
                    pointBackgroundColor: 'rgba(236, 72, 153, 1)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 1,
                    pointRadius: 3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('pt-BR', {
                                        style: 'currency',
                                        currency: 'BRL'
                                    }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('pt-BR', {
                                    style: 'currency',
                                    currency: 'BRL',
                                    maximumFractionDigits: 0
                                }).format(value);
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endpush
