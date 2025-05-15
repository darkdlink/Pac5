<!-- resources/views/home.blade.php -->

@extends('layouts.app')

@section('title', 'Beauty Care - Estética e Skincare')

@section('content')
<div class="hero-section bg-gradient-primary">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 text-white fw-bold">Sua beleza merece cuidados especiais</h1>
                <p class="lead text-white-75">Produtos de skincare selecionados e tratamentos estéticos exclusivos para você</p>
                <div class="mt-4">
                    <a href="{{ route('products.index') }}" class="btn btn-light btn-lg me-2">Ver produtos</a>
                    <a href="{{ route('services.index') }}" class="btn btn-outline-light btn-lg">Conhecer serviços</a>
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block">
                <img src="{{ asset('images/hero-image.jpg') }}" alt="Tratamentos de beleza" class="img-fluid rounded-3 shadow">
            </div>
        </div>
    </div>
</div>

<!-- Produtos em Destaque -->
<section class="featured-products py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="section-title">Produtos em Destaque</h2>
            <a href="{{ route('products.index') }}" class="btn btn-link text-primary">Ver todos</a>
        </div>

        <div class="row">
            @foreach($featuredProducts as $product)
            <div class="col-md-3 mb-4">
                <div class="card product-card h-100">
                    <div class="position-relative">
                        <img src="{{ asset($product->image) }}" class="card-img-top" alt="{{ $product->name }}">
                        @if($product->discount > 0)
                        <div class="discount-badge">-{{ $product->discount }}%</div>
                        @endif
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">{{ $product->name }}</h5>
                        <div class="product-rating mb-2">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= $product->rating)
                                    <i class="fas fa-star text-warning"></i>
                                @else
                                    <i class="far fa-star text-warning"></i>
                                @endif
                            @endfor
                            <span class="text-muted ms-1">({{ $product->reviews_count }})</span>
                        </div>
                        <p class="card-text">{{ Str::limit($product->description, 80) }}</p>
                        <div class="d-flex justify-content-between align-items-center mt-auto">
                            <div class="product-price">
                                @if($product->discount > 0)
                                <span class="text-muted text-decoration-line-through">R$ {{ number_format($product->original_price, 2, ',', '.') }}</span>
                                @endif
                                <span class="fw-bold">R$ {{ number_format($product->price, 2, ',', '.') }}</span>
                            </div>
                            <button type="button" class="btn btn-primary add-to-cart-btn"
                                    data-product-id="{{ $product->id }}"
                                    data-product-name="{{ $product->name }}"
                                    data-product-price="{{ $product->price }}">
                                <i class="fas fa-shopping-cart"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Serviços Estéticos -->
<section class="aesthetic-services py-5 bg-light">
    <div class="container">
        <h2 class="section-title text-center mb-5">Nossos Serviços Estéticos</h2>

        <div class="row">
            @foreach($featuredServices as $service)
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card service-card h-100">
                    <img src="{{ asset($service->image) }}" class="card-img-top" alt="{{ $service->name }}">
                    <div class="card-body">
                        <h5 class="card-title">{{ $service->name }}</h5>
                        <p class="card-text">{{ Str::limit($service->description, 120) }}</p>
                        <div class="service-price mb-3">
                            <span class="fw-bold text-primary">R$ {{ number_format($service->price, 2, ',', '.') }}</span>
                            <span class="text-muted">/ sessão</span>
                        </div>
                        <a href="{{ route('services.show', $service) }}" class="btn btn-outline-primary">Saiba mais</a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="text-center mt-4">
            <a href="{{ route('services.index') }}" class="btn btn-primary">Ver todos os serviços</a>
        </div>
    </div>
</section>

<!-- Depoimentos -->
<section class="testimonials py-5">
    <div class="container">
        <h2 class="section-title text-center mb-5">O que nossos clientes dizem</h2>

        <div class="testimonial-slider">
            @foreach($testimonials as $testimonial)
            <div class="testimonial-item text-center p-4">
                <div class="avatar mb-3">
                    <img src="{{ asset($testimonial->avatar) }}" alt="{{ $testimonial->name }}" class="rounded-circle">
                </div>
                <p class="testimonial-text mb-3">"{{ $testimonial->content }}"</p>
                <h5 class="testimonial-name mb-1">{{ $testimonial->name }}</h5>
                <div class="testimonial-rating">
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= $testimonial->rating)
                            <i class="fas fa-star text-warning"></i>
                        @else
                            <i class="far fa-star text-warning"></i>
                        @endif
                    @endfor
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Instagram Feed -->
<section class="instagram-feed py-5 bg-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="section-title">Siga-nos no Instagram</h2>
            <a href="https://instagram.com/esteticista" target="_blank" class="btn btn-link text-primary">
                <i class="fab fa-instagram me-1"></i> @esteticista
            </a>
        </div>

        <div class="row instagram-posts">
            @foreach($instagramPosts as $post)
            <div class="col-lg-2 col-md-4 col-6 mb-4">
                <a href="{{ $post->permalink }}" target="_blank" class="instagram-post">
                    <img src="{{ $post->media_url }}" alt="Instagram post" class="img-fluid rounded">
                    <div class="instagram-overlay">
                        <div class="instagram-stats">
                            <span><i class="fas fa-heart"></i> {{ $post->likes_count }}</span>
                            <span><i class="fas fa-comment"></i> {{ $post->comments_count }}</span>
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Newsletter -->
<section class="newsletter-section py-5">
    <div class="container">
        <div class="newsletter-card bg-primary text-white rounded-3 p-4 p-md-5">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <h3>Receba nossas novidades</h3>
                    <p>Cadastre-se para receber ofertas exclusivas, dicas de skincare e novidades diretamente no seu e-mail.</p>
                </div>
                <div class="col-lg-6">
                    <form action="{{ route('newsletter.subscribe') }}" method="POST" class="newsletter-form">
                        @csrf
                        <div class="input-group">
                            <input type="email" class="form-control" name="email" placeholder="Seu melhor e-mail" required>
                            <button class="btn btn-light" type="submit">Inscrever-se</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contato Rápido -->
<section class="quick-contact py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Fale Conosco</h2>
            <p class="lead">Estamos aqui para tirar suas dúvidas e ajudar com o que precisar</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-4 mb-4 mb-md-0">
                <div class="contact-card text-center p-4 h-100">
                    <div class="contact-icon mb-3">
                        <i class="fas fa-map-marker-alt fa-2x text-primary"></i>
                    </div>
                    <h5 class="mb-3">Endereço</h5>
                    <p>Rua das Flores, 123<br>Centro - Joinville, SC</p>
                </div>
            </div>

            <div class="col-md-4 mb-4 mb-md-0">
                <div class="contact-card text-center p-4 h-100">
                    <div class="contact-icon mb-3">
                        <i class="fas fa-phone-alt fa-2x text-primary"></i>
                    </div>
                    <h5 class="mb-3">Telefone</h5>
                    <p>(47) 3456-7890<br>(47) 98765-4321</p>
                    <a href="https://wa.me/5547987654321" class="btn btn-outline-primary mt-2">
                        <i class="fab fa-whatsapp me-1"></i> Falar no WhatsApp
                    </a>
                </div>
            </div>

            <div class="col-md-4">
                <div class="contact-card text-center p-4 h-100">
                    <div class="contact-icon mb-3">
                        <i class="fas fa-envelope fa-2x text-primary"></i>
                    </div>
                    <h5 class="mb-3">E-mail</h5>
                    <p>contato@beautycare.com.br<br>atendimento@beautycare.com.br</p>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
    // Adicionar produto ao carrinho
    $('.add-to-cart-btn').on('click', function() {
        const productId = $(this).data('product-id');
        const productName = $(this).data('product-name');
        const productPrice = $(this).data('product-price');

        $.ajax({
            url: "{{ route('cart.add') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                product_id: productId,
                quantity: 1
            },
            success: function(response) {
                // Atualizar contador do carrinho
                $('#cart-count').text(response.cartCount);

                // Mostrar mensagem de sucesso
                Swal.fire({
                    title: 'Produto adicionado!',
                    text: `${productName} foi adicionado ao seu carrinho.`,
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonText: 'Ver carrinho',
                    cancelButtonText: 'Continuar comprando'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "{{ route('cart.index') }}";
                    }
                });
            },
            error: function(error) {
                console.error(error);
                Swal.fire({
                    title: 'Erro',
                    text: 'Não foi possível adicionar o produto ao carrinho.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        });
    });

    // Inicializar slider de depoimentos
    $('.testimonial-slider').slick({
        dots: true,
        infinite: true,
        speed: 300,
        slidesToShow: 3,
        slidesToScroll: 1,
        autoplay: true,
        autoplaySpeed: 5000,
        responsive: [
            {
                breakpoint: 992,
                settings: {
                    slidesToShow: 2
                }
            },
            {
                breakpoint: 576,
                settings: {
                    slidesToShow: 1
                }
            }
        ]
    });
</script>
@endpush

@push('styles')
<style>
    /* Estilos específicos da página inicial */
    .hero-section {
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, #8a2be2, #4169e1);
    }

    .product-card, .service-card, .contact-card {
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .product-card:hover, .service-card:hover, .contact-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .discount-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background-color: #dc3545;
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-weight: bold;
    }

    .instagram-post {
        position: relative;
        display: block;
        overflow: hidden;
    }

    .instagram-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .instagram-post:hover .instagram-overlay {
        opacity: 1;
    }

    .instagram-stats {
        color: white;
        display: flex;
        gap: 1rem;
    }

    .contact-icon {
        width: 60px;
        height: 60px;
        background-color: rgba(65, 105, 225, 0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
    }

    .section-title {
        position: relative;
        padding-bottom: 0.5rem;
        margin-bottom: 1rem;
    }

    .section-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 50px;
        height: 3px;
        background-color: #4169e1;
    }

    .text-center .section-title::after {
        left: 50%;
        transform: translateX(-50%);
    }
</style>
@endpush
