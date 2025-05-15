<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProdutoController;
use App\Http\Controllers\Api\ServicoController;
use App\Http\Controllers\Api\CarrinhoController;
use App\Http\Controllers\Api\PedidoController;
use App\Http\Controllers\Api\PagamentoController;
use App\Http\Controllers\Api\AvaliacaoController;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\EstoqueController;
use App\Http\Controllers\Api\RelatorioController;
use App\Http\Controllers\Api\InstagramController;
use App\Http\Controllers\Api\WebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Aqui é onde você pode registrar rotas de API para seu aplicativo. Essas
| rotas são carregadas pelo RouteServiceProvider dentro de um grupo que
| recebe o prefixo "api" de URI. Aproveite para construir sua API!
|
*/

// API pública - Não necessita autenticação
Route::prefix('v1')->group(function () {
    // Autenticação
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/cadastro', [AuthController::class, 'register']);
    Route::post('/esqueci-senha', [AuthController::class, 'forgotPassword']);
    Route::post('/resetar-senha', [AuthController::class, 'resetPassword']);

    // Catálogo público
    Route::get('/produtos', [ProdutoController::class, 'index']);
    Route::get('/produtos/{id}', [ProdutoController::class, 'show']);
    Route::get('/produtos/categoria/{categoria}', [ProdutoController::class, 'porCategoria']);
    Route::get('/servicos', [ServicoController::class, 'index']);
    Route::get('/servicos/{id}', [ServicoController::class, 'show']);

    // Instagram Feed
    Route::get('/instagram/feed', [InstagramController::class, 'getFeed']);

    // Webhooks (pagamentos e outros serviços externos)
    Route::post('/webhook/pagamento', [WebhookController::class, 'handlePayment']);
});

// API Autenticada
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Rota para verificar token/usuário atual
    Route::get('/user', [UsuarioController::class, 'getCurrentUser']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Perfil do Usuário
    Route::put('/usuario/perfil', [UsuarioController::class, 'updateProfile']);
    Route::put('/usuario/senha', [UsuarioController::class, 'updatePassword']);

    // Rotas para Clientes
    Route::middleware('role:cliente')->group(function () {
        // Carrinho
        Route::get('/carrinho', [CarrinhoController::class, 'index']);
        Route::post('/carrinho', [CarrinhoController::class, 'adicionar']);
        Route::put('/carrinho/{id}', [CarrinhoController::class, 'atualizar']);
        Route::delete('/carrinho/{id}', [CarrinhoController::class, 'remover']);
        Route::delete('/carrinho', [CarrinhoController::class, 'limpar']);

        // Pedidos do Cliente
        Route::get('/pedidos', [PedidoController::class, 'index']);
        Route::get('/pedidos/{id}', [PedidoController::class, 'show']);
        Route::post('/pedidos', [PedidoController::class, 'store']);

        // Pagamento
        Route::post('/pagamento/iniciar', [PagamentoController::class, 'iniciar']);
        Route::get('/pagamento/status/{pedido_id}', [PagamentoController::class, 'status']);

        // Avaliações
        Route::post('/avaliacoes', [AvaliacaoController::class, 'store']);
        Route::put('/avaliacoes/{id}', [AvaliacaoController::class, 'update']);
        Route::delete('/avaliacoes/{id}', [AvaliacaoController::class, 'destroy']);
    });

    // Rotas para Esteticista (Admin)
    Route::middleware('role:esteticista')->group(function () {
        // Gerenciamento de Produtos
        Route::post('/produtos', [ProdutoController::class, 'store']);
        Route::put('/produtos/{id}', [ProdutoController::class, 'update']);
        Route::delete('/produtos/{id}', [ProdutoController::class, 'destroy']);
        Route::post('/produtos/importar', [ProdutoController::class, 'import']);

        // Gerenciamento de Serviços
        Route::post('/servicos', [ServicoController::class, 'store']);
        Route::put('/servicos/{id}', [ServicoController::class, 'update']);
        Route::delete('/servicos/{id}', [ServicoController::class, 'destroy']);

        // Gerenciamento de Pedidos
        Route::get('/admin/pedidos', [PedidoController::class, 'adminIndex']);
        Route::get('/admin/pedidos/{id}', [PedidoController::class, 'adminShow']);
        Route::put('/admin/pedidos/{id}/status', [PedidoController::class, 'updateStatus']);

        // Estoque
        Route::get('/estoque', [EstoqueController::class, 'index']);
        Route::put('/estoque/{produto_id}', [EstoqueController::class, 'update']);
        Route::get('/estoque/alerta', [EstoqueController::class, 'alertaBaixoEstoque']);

        // Relatórios
        Route::get('/relatorios/vendas', [RelatorioController::class, 'vendas']);
        Route::get('/relatorios/produtos', [RelatorioController::class, 'produtos']);
        Route::get('/relatorios/clientes', [RelatorioController::class, 'clientes']);
        Route::get('/relatorios/engajamento', [RelatorioController::class, 'engajamento']);
        Route::post('/relatorios/exportar', [RelatorioController::class, 'exportar']);

        // Dashboard
        Route::get('/dashboard/resumo', [RelatorioController::class, 'dashboardResumo']);
        Route::get('/dashboard/vendas-recentes', [RelatorioController::class, 'vendasRecentes']);
        Route::get('/dashboard/estoque-baixo', [EstoqueController::class, 'resumoEstoqueBaixo']);
    });
});
