<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Cliente\ClienteController;
use App\Http\Controllers\Cliente\PedidoController;
use App\Http\Controllers\Cliente\AvaliacaoController;
use App\Http\Controllers\Cliente\CarrinhoController;
use App\Http\Controllers\Cliente\PagamentoController;
use App\Http\Controllers\Esteticista\ProdutoController;
use App\Http\Controllers\Esteticista\ServicoController;
use App\Http\Controllers\Esteticista\EstoqueController;
use App\Http\Controllers\Esteticista\DashboardController;
use App\Http\Controllers\Esteticista\PedidoAdminController;
use App\Http\Controllers\Esteticista\RelatorioController;
use App\Http\Controllers\HomeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Aqui é onde você pode registrar rotas web para seu aplicativo. Essas
| rotas são carregadas pelo RouteServiceProvider dentro de um grupo que
| contém o middleware "web". Agora crie algo incrível!
|
*/

// Rotas públicas
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/produtos', [HomeController::class, 'produtos'])->name('produtos');
Route::get('/produtos/{slug}', [HomeController::class, 'produtoDetalhe'])->name('produto.detalhe');
Route::get('/servicos', [HomeController::class, 'servicos'])->name('servicos');
Route::get('/servicos/{slug}', [HomeController::class, 'servicoDetalhe'])->name('servico.detalhe');
Route::get('/instagram-feed', [HomeController::class, 'instagramFeed'])->name('instagram.feed');

// Autenticação
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/cadastro', [AuthController::class, 'showRegistrationForm'])->name('cadastro');
Route::post('/cadastro', [AuthController::class, 'register']);
Route::get('/esqueci-senha', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/esqueci-senha', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/redefinir-senha/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('/redefinir-senha', [AuthController::class, 'resetPassword'])->name('password.update');

// Rotas de cliente (autenticado)
Route::middleware(['auth', 'role:cliente'])->prefix('cliente')->name('cliente.')->group(function () {
    // Perfil
    Route::get('/perfil', [ClienteController::class, 'profile'])->name('perfil');
    Route::put('/perfil', [ClienteController::class, 'updateProfile'])->name('perfil.atualizar');

    // Carrinho
    Route::get('/carrinho', [CarrinhoController::class, 'index'])->name('carrinho');
    Route::post('/carrinho/adicionar', [CarrinhoController::class, 'adicionar'])->name('carrinho.adicionar');
    Route::put('/carrinho/atualizar', [CarrinhoController::class, 'atualizar'])->name('carrinho.atualizar');
    Route::delete('/carrinho/remover/{id}', [CarrinhoController::class, 'remover'])->name('carrinho.remover');
    Route::post('/carrinho/limpar', [CarrinhoController::class, 'limpar'])->name('carrinho.limpar');

    // Pedidos
    Route::get('/pedidos', [PedidoController::class, 'index'])->name('pedidos');
    Route::get('/pedidos/{id}', [PedidoController::class, 'show'])->name('pedidos.detalhes');
    Route::post('/checkout', [PedidoController::class, 'checkout'])->name('checkout');

    // Pagamento
    Route::get('/pagamento/{pedido_id}', [PagamentoController::class, 'index'])->name('pagamento');
    Route::post('/pagamento/processar', [PagamentoController::class, 'processar'])->name('pagamento.processar');
    Route::get('/pagamento/confirmacao/{pedido_id}', [PagamentoController::class, 'confirmacao'])->name('pagamento.confirmacao');

    // Avaliações
    Route::post('/avaliacoes', [AvaliacaoController::class, 'store'])->name('avaliacoes.adicionar');
    Route::put('/avaliacoes/{id}', [AvaliacaoController::class, 'update'])->name('avaliacoes.atualizar');
    Route::delete('/avaliacoes/{id}', [AvaliacaoController::class, 'destroy'])->name('avaliacoes.remover');
});

// Rotas de administração (esteticista)
Route::middleware(['auth', 'role:esteticista'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Produtos
    Route::resource('produtos', ProdutoController::class);
    Route::post('/produtos/{id}/status', [ProdutoController::class, 'updateStatus'])->name('produtos.status');
    Route::post('/produtos/importar', [ProdutoController::class, 'import'])->name('produtos.importar');
    Route::get('/produtos/exportar', [ProdutoController::class, 'export'])->name('produtos.exportar');

    // Serviços
    Route::resource('servicos', ServicoController::class);
    Route::post('/servicos/{id}/status', [ServicoController::class, 'updateStatus'])->name('servicos.status');

    // Pedidos
    Route::get('/pedidos', [PedidoAdminController::class, 'index'])->name('pedidos');
    Route::get('/pedidos/{id}', [PedidoAdminController::class, 'show'])->name('pedidos.detalhes');
    Route::put('/pedidos/{id}/status', [PedidoAdminController::class, 'updateStatus'])->name('pedidos.status');

    // Estoque
    Route::get('/estoque', [EstoqueController::class, 'index'])->name('estoque');
    Route::put('/estoque/atualizar', [EstoqueController::class, 'atualizar'])->name('estoque.atualizar');
    Route::get('/estoque/alerta', [EstoqueController::class, 'alerta'])->name('estoque.alerta');

    // Relatórios
    Route::get('/relatorios/vendas', [RelatorioController::class, 'vendasIndex'])->name('relatorios.vendas');
    Route::get('/relatorios/vendas/exportar', [RelatorioController::class, 'vendasExport'])->name('relatorios.vendas.exportar');
    Route::get('/relatorios/produtos', [RelatorioController::class, 'produtosIndex'])->name('relatorios.produtos');
    Route::get('/relatorios/engajamento', [RelatorioController::class, 'engajamentoIndex'])->name('relatorios.engajamento');
    Route::get('/relatorios/clientes', [RelatorioController::class, 'clientesIndex'])->name('relatorios.clientes');
});
