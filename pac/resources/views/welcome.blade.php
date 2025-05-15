<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Estética & Skincare') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }

        .hero-section {
            background: linear-gradient(135deg, #f8bbd0 0%, #e8eaf6 100%);
            padding: 120px 0 80px;
            position: relative;
            overflow: hidden;
        }

        .hero-pattern {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0.05;
            background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%239C92AC' fill-opacity='0.4' fill-rule='evenodd'/%3E%3C/svg%3E");
        }

        .btn-primary {
            background-color: #9c27b0;
            border-color: #9c27b0;
            padding: 10px 24px;
            font-weight: 500;
            border-radius: 30px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #7b1fa2;
            border-color: #7b1fa2;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .btn-outline-primary {
            color: #9c27b0;
            border-color: #9c27b0;
            padding: 10px 24px;
            font-weight: 500;
            border-radius: 30px;
            transition: all 0.3s ease;
        }

        .btn-outline-primary:hover {
            background-color: #9c27b0;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .service-card {
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .service-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .product-card {
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        .product-card img {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }

        .testimonial-card {
            border-radius: 12px;
            padding: 25px;
            background-color: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            position: relative;
        }

        .testimonial-card::before {
            content: """;
            position: absolute;
            top: 10px;
            left: 15px;
            font-size: 60px;
            color: #f8bbd0;
            opacity: 0.3;
            font-family: serif;
        }

        .overlay-text {
            position: relative;
            z-index: 2;
        }

        .social-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #f8bbd0;
            color: #9c27b0;
            margin-right: 10px;
            transition: all 0.3s ease;
        }

        .social-icon:hover {
            background-color: #9c27b0;
            color: white;
            transform: translateY(-3px);
        }

        .footer {
            background-color: #343a40;
            color: white;
            padding: 50px 0 20px;
        }

        .footer a {
            color: #adb5bd;
            transition: color 0.3s ease;
        }

        .footer a:hover {
            color: white;
            text-decoration: none;
        }

        .section-title {
            position: relative;
            margin-bottom: 40px;
            padding-bottom: 15px;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background-color: #9c27b0;
        }

        .section-title.text-center::after {
            left: 50%;
            transform: translateX(-50%);
        }

        .instagram-feed {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }

        .instagram-item {
            position: relative;
            overflow: hidden;
            border-radius: 8px;
            aspect-ratio: 1 / 1;
        }

        .instagram-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .instagram-item:hover img {
            transform: scale(1.1);
        }

        @media (max-width: 768px) {
            .hero-section {
                padding: 80px 0 50px;
            }

            .instagram-feed {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm fixed-top">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                <img src="{{ asset('img/logo.png') }}" alt="Logo" height="40">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <!-- Left Side Of Navbar -->
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#services">Serviços</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#products">Produtos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">Sobre</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#testimonials">Depoimentos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contato</a>
                    </li>
                </ul>

                <!-- Right Side Of Navbar -->
                <ul class="navbar-nav ms-auto">
                    <!-- Shopping Cart -->
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('cart.index') }}">
                            <i class="bi bi-telephone me-2"></i> (47) 9999-9999</li>
                        <li class="mb-2"><i class="bi bi-envelope me-2"></i> contato@esteticaskincare.com.br</li>
                        <li><i class="bi bi-clock me-2"></i> Segunda a Sexta: 9h às 19h<br>Sábado: 9h às 14h</li>
                    </ul>
                </div>
            </div>
            <hr class="my-4 bg-secondary">
            <div class="row">
                <div class="col-md-6 text-center text-md-start">
                    <p class="small text-muted mb-0">
                        &copy; {{ date('Y') }} Estética & Skincare. Todos os direitos reservados.
                    </p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <p class="small text-muted mb-0">
                        <a href="{{ route('terms') }}" class="text-muted me-3">Termos de Uso</a>
                        <a href="{{ route('privacy') }}" class="text-muted">Política de Privacidade</a>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Custom JavaScript -->
    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();

                const target = document.querySelector(this.getAttribute('href'));
                if (!target) return;

                window.scrollTo({
                    top: target.offsetTop - 80, // Adjust for fixed header
                    behavior: 'smooth'
                });
            });
        });

        // Add padding to body to account for fixed navbar
        document.addEventListener('DOMContentLoaded', function() {
            document.body.style.paddingTop = document.querySelector('.navbar').offsetHeight + 'px';
        });
    </script>
</body>
</html>cart"></i>
                            <span class="badge rounded-pill bg-primary">{{ Cart::count() }}</span>
                        </a>
                    </li>

                    <!-- Authentication Links -->
                    @guest
                        @if (Route::has('login'))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                            </li>
                        @endif

                        @if (Route::has('register'))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('register') }}">{{ __('Cadastrar') }}</a>
                            </li>
                        @endif
                    @else
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                {{ Auth::user()->name }}
                            </a>

                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="{{ route('user.profile') }}">
                                    Meu Perfil
                                </a>

                                <a class="dropdown-item" href="{{ route('user.orders') }}">
                                    Meus Pedidos
                                </a>

                                <a class="dropdown-item" href="{{ route('user.appointments') }}">
                                    Meus Agendamentos
                                </a>

                                <div class="dropdown-divider"></div>

                                <a class="dropdown-item" href="{{ route('logout') }}"
                                   onclick="event.preventDefault();
                                                 document.getElementById('logout-form').submit();">
                                    {{ __('Sair') }}
                                </a>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </div>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-pattern"></div>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 overlay-text">
                    <h1 class="mb-4 fw-bold" style="font-size: 3rem;">Transforme Sua Pele, <br> Transforme Sua Vida</h1>
                    <p class="lead mb-4">Descubra os melhores tratamentos estéticos e produtos de skincare personalizados para suas necessidades.</p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="#services" class="btn btn-primary me-3 mb-2">Agendar Consulta</a>
                        <a href="#products" class="btn btn-outline-primary mb-2">Ver Produtos</a>
                    </div>
                </div>
                <div class="col-lg-6 d-none d-lg-block">
                    <img src="{{ asset('img/hero-image.png') }}" alt="Tratamento Facial" class="img-fluid rounded-3">
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="py-5">
        <div class="container">
            <h2 class="section-title text-center">Nossos Serviços</h2>
            <p class="text-center mb-5">Conheça os tratamentos estéticos exclusivos que oferecemos para cuidar da sua pele</p>

            <div class="row">
                @foreach($services as $service)
                <div class="col-md-4">
                    <div class="service-card">
                        <img src="{{ asset('img/services/' . $service->image) }}" alt="{{ $service->name }}">
                        <div class="card-body">
                            <h5 class="card-title">{{ $service->name }}</h5>
                            <p class="card-text">{{ Str::limit($service->description, 100) }}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-primary">R$ {{ number_format($service->price, 2, ',', '.') }}</span>
                                <a href="{{ route('services.show', $service->id) }}" class="btn btn-sm btn-outline-primary">Saiba Mais</a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="text-center mt-4">
                <a href="{{ route('services.index') }}" class="btn btn-outline-primary">Ver Todos os Serviços</a>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section id="products" class="py-5 bg-light">
        <div class="container">
            <h2 class="section-title text-center">Produtos em Destaque</h2>
            <p class="text-center mb-5">Produtos selecionados de alta qualidade para complementar seu tratamento</p>

            <div class="row">
                @foreach($featuredProducts as $product)
                <div class="col-md-3">
                    <div class="product-card bg-white">
                        <img src="{{ asset('img/products/' . $product->image) }}" alt="{{ $product->name }}">
                        <div class="card-body">
                            <h5 class="card-title">{{ $product->name }}</h5>
                            <p class="card-text small">{{ Str::limit($product->description, 80) }}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-primary">R$ {{ number_format($product->price, 2, ',', '.') }}</span>
                                <form action="{{ route('cart.add') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <i class="bi bi-cart-plus"></i> Adicionar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="text-center mt-4">
                <a href="{{ route('products.index') }}" class="btn btn-outline-primary">Ver Todos os Produtos</a>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <img src="{{ asset('img/about.jpg') }}" alt="Sobre Nós" class="img-fluid rounded-3 shadow">
                </div>
                <div class="col-lg-6">
                    <h2 class="section-title">Sobre Nós</h2>
                    <p>Com mais de 10 anos de experiência, nossa clínica estética se dedica a oferecer os melhores tratamentos e produtos para a saúde da sua pele.</p>
                    <p>Trabalhamos com as técnicas mais avançadas e produtos de alta qualidade, garantindo resultados reais e duradouros para todos os nossos clientes.</p>
                    <p>Nossa equipe de profissionais é altamente qualificada e está em constante atualização para proporcionar o melhor atendimento e cuidado personalizado.</p>
                    <div class="mt-4">
                        <a href="{{ route('about') }}" class="btn btn-outline-primary">Conheça Nossa História</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Instagram Feed Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="section-title text-center">Nosso Instagram</h2>
            <p class="text-center mb-5">Acompanhe nosso trabalho e fique por dentro das novidades</p>

            <div class="instagram-feed">
                @foreach($instagramPosts as $post)
                <a href="{{ $post->url }}" target="_blank" class="instagram-item">
                    <img src="{{ $post->image_url }}" alt="Instagram Post">
                </a>
                @endforeach
            </div>

            <div class="text-center mt-4">
                <a href="{{ $instagramUrl }}" target="_blank" class="btn btn-outline-primary">
                    <i class="bi bi-instagram me-2"></i> Seguir no Instagram
                </a>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials" class="py-5">
        <div class="container">
            <h2 class="section-title text-center">Depoimentos</h2>
            <p class="text-center mb-5">Veja o que nossos clientes dizem sobre nós</p>

            <div class="row">
                @foreach($testimonials as $testimonial)
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <p class="mb-3">"{{ $testimonial->content }}"</p>
                        <div class="d-flex align-items-center">
                            <img src="{{ asset('img/testimonials/' . $testimonial->avatar) }}" alt="{{ $testimonial->name }}" class="rounded-circle me-3" width="50" height="50">
                            <div>
                                <h6 class="mb-0">{{ $testimonial->name }}</h6>
                                <small class="text-muted">{{ $testimonial->title }}</small>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5 bg-primary text-white">
        <div class="container text-center">
            <h2 class="mb-4">Pronta para transformar sua pele?</h2>
            <p class="lead mb-4">Agende agora mesmo uma consulta e descubra o tratamento ideal para você</p>
            <a href="{{ route('services.index') }}" class="btn btn-light btn-lg px-4">Agendar Consulta</a>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <h2 class="section-title">Entre em Contato</h2>
                    <p class="mb-4">Tem alguma dúvida? Preencha o formulário e entraremos em contato o mais breve possível.</p>

                    <form action="{{ route('contact.send') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="subject" class="form-label">Assunto</label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Mensagem</label>
                            <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Enviar Mensagem</button>
                    </form>
                </div>
                <div class="col-lg-6 mt-4 mt-lg-0">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">Informações de Contato</h5>
                            <ul class="list-unstyled">
                                <li class="mb-3">
                                    <i class="bi bi-geo-alt me-2 text-primary"></i>
                                    Av. Brasil, 123 - Centro, Joinville - SC
                                </li>
                                <li class="mb-3">
                                    <i class="bi bi-telephone me-2 text-primary"></i>
                                    (47) 9999-9999
                                </li>
                                <li class="mb-3">
                                    <i class="bi bi-envelope me-2 text-primary"></i>
                                    contato@esteticaskincare.com.br
                                </li>
                                <li>
                                    <i class="bi bi-clock me-2 text-primary"></i>
                                    Segunda a Sexta: 9h às 19h<br>
                                    Sábado: 9h às 14h
                                </li>
                            </ul>

                            <h5 class="card-title mt-4">Siga-nos</h5>
                            <div class="social-links">
                                <a href="#" class="social-icon"><i class="bi bi-instagram"></i></a>
                                <a href="#" class="social-icon"><i class="bi bi-facebook"></i></a>
                                <a href="#" class="social-icon"><i class="bi bi-whatsapp"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- WhatsApp Float Button -->
    <a href="https://wa.me/5547999999999" class="position-fixed bottom-0 end-0 m-4 btn btn-success rounded-circle" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
        <i class="bi bi-whatsapp" style="font-size: 1.5rem;"></i>
    </a>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <h5 class="text-white mb-4">Estética & Skincare</h5>
                    <p class="text-muted">Oferecemos os melhores tratamentos estéticos e produtos de skincare para cuidar da sua beleza e saúde da pele.</p>
                    <div class="social-links mt-3">
                        <a href="#" class="social-icon"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="social-icon"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="social-icon"><i class="bi bi-whatsapp"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <h5 class="text-white mb-4">Links Rápidos</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#services">Serviços</a></li>
                        <li class="mb-2"><a href="#products">Produtos</a></li>
                        <li class="mb-2"><a href="#about">Sobre Nós</a></li>
                        <li class="mb-2"><a href="#testimonials">Depoimentos</a></li>
                        <li><a href="#contact">Contato</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <h5 class="text-white mb-4">Serviços</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#">Limpeza de Pele</a></li>
                        <li class="mb-2"><a href="#">Microagulhamento</a></li>
                        <li class="mb-2"><a href="#">Peeling Químico</a></li>
                        <li class="mb-2"><a href="#">Hidratação Facial</a></li>
                        <li><a href="#">Tratamento de Acne</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5 class="text-white mb-4">Contato</h5>
                    <ul class="list-unstyled text-muted">
                        <li class="mb-2"><i class="bi bi-geo-alt me-2"></i> Av. Brasil, 123 - Centro, Joinville - SC</li>
                        <li class="mb-2"><i class="bi bi-
