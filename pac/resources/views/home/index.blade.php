@extends('layouts.app')

@section('content')
<div class="hero-section">
    <div class="container">
        <div class="hero-content">
            <h1>Cuidados para sua pele</h1>
            <p>Produtos e tratamentos exclusivos para realçar sua beleza natural.</p>
            <div class="hero-buttons">
                <a href="{{ route('products.index') }}" class="btn btn-primary">Ver Produtos</a>
                <a href="{{ route('appointments.create') }}" class="btn btn-outline">Agendar</a>
            </div>
        </div>
    </div>
</div>

<div class="featured-products">
    <div class="container">
        <h2>Produtos em Destaque</h2>
        <div class="products-grid">
            @foreach($featuredProducts as $product)
            <div class="product-card">
                <div class="product-image">
                    @if($product->thumbnail)
                        <img src="{{ asset('storage/' . $product->thumbnail) }}" alt="{{ $product->name }}">
                    @else
                        <div class="placeholder-image"></div>
                    @endif
                </div>
                <div class="product-info">
                    <h3>{{ $product->name }}</h3>
                    <p>{{ $product->short_description }}</p>
                    <div class="product-price">
                        <span>R$ {{ number_format($product->price, 2, ',', '.') }}</span>
                    </div>
                    <form action="{{ route('cart.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="btn btn-sm btn-add-cart">Adicionar ao Carrinho</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<div class="instagram-section">
    <div class="container">
        <p>Siga-nos no Instagram</p>
        <div class="instagram-feed">
            <!-- Aqui será implementada a integração com o Instagram -->
        </div>
    </div>
</div>
@endsection
