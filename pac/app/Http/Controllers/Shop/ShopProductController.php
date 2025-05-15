<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\ProductReviewRequest;
use App\Models\Category;
use App\Models\Product;
use App\Repositories\ProductRepository;
use App\Services\Instagram\InstagramService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $productRepository;
    protected $instagramService;

    /**
     * Create a new controller instance.
     *
     * @param ProductRepository $productRepository
     * @param InstagramService $instagramService
     */
    public function __construct(
        ProductRepository $productRepository,
        InstagramService $instagramService
    ) {
        $this->productRepository = $productRepository;
        $this->instagramService = $instagramService;
    }

    /**
     * Display a listing of the products.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = $request->get('query');
        $category = $request->get('category');
        $minPrice = $request->get('min_price');
        $maxPrice = $request->get('max_price');
        $sort = $request->get('sort', 'newest');

        $products = $this->productRepository->getActiveProducts(
            $query,
            $category,
            $minPrice,
            $maxPrice,
            $sort,
            12
        );

        $categories = Category::where('type', 'product')->get();
        $minProductPrice = Product::min('price');
        $maxProductPrice = Product::max('price');

        // Get recent Instagram posts
        $instagramPosts = $this->instagramService->getRecentPosts(4);

        return view('shop.products.index', compact(
            'products',
            'categories',
            'query',
            'category',
            'minPrice',
            'maxPrice',
            'sort',
            'minProductPrice',
            'maxProductPrice',
            'instagramPosts'
        ));
    }

    /**
     * Display the specified product.
     *
     * @param Product $product
     * @return \Illuminate\View\View
     */
    public function show(Product $product)
    {
        // Don't show inactive products
        if (!$product->active) {
            abort(404);
        }

        $product->load(['category', 'reviews.user']);

        // Get related products
        $relatedProducts = $this->productRepository->getRelatedProducts($product, 4);

        // Get Instagram posts related to this product's category (if any)
        $instagramPosts = [];
        if ($product->category) {
            $instagramPosts = $this->instagramService->getPostsByHashtag($product->category->name, 2);
        }

        // Check if product is in stock
        $inStock = $product->stock > 0;

        return view('shop.products.show', compact(
            'product',
            'relatedProducts',
            'instagramPosts',
            'inStock'
        ));
    }

    /**
     * Store a product review.
     *
     * @param ProductReviewRequest $request
     * @param Product $product
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeReview(ProductReviewRequest $request, Product $product)
    {
        // Check if user has purchased this product
        $hasPurchased = $this->productRepository->hasUserPurchasedProduct(
            auth()->id(),
            $product->id
        );

        if (!$hasPurchased) {
            return redirect()->route('shop.products.show', $product->id)
                ->with('error', 'Você precisa comprar este produto para avaliá-lo.');
        }

        // Check if user has already reviewed this product
        $hasReviewed = $product->reviews()->where('user_id', auth()->id())->exists();

        if ($hasReviewed) {
            return redirect()->route('shop.products.show', $product->id)
                ->with('error', 'Você já avaliou este produto.');
        }

        // Create review
        $product->reviews()->create([
            'user_id' => auth()->id(),
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        // Update product average rating
        $this->productRepository->updateProductRating($product);

        return redirect()->route('shop.products.show', $product->id)
            ->with('success', 'Sua avaliação foi enviada com sucesso.');
    }

    /**
     * Display products by category.
     *
     * @param Category $category
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function category(Category $category, Request $request)
    {
        // Ensure this is a product category
        if ($category->type !== 'product') {
            abort(404);
        }

        $minPrice = $request->get('min_price');
        $maxPrice = $request->get('max_price');
        $sort = $request->get('sort', 'newest');

        $products = $this->productRepository->getProductsByCategory(
            $category->id,
            $minPrice,
            $maxPrice,
            $sort,
            12
        );

        $categories = Category::where('type', 'product')->get();
        $minProductPrice = Product::min('price');
        $maxProductPrice = Product::max('price');

        // Get Instagram posts related to this category
        $instagramPosts = $this->instagramService->getPostsByHashtag($category->name, 4);

        return view('shop.products.category', compact(
            'products',
            'category',
            'categories',
            'minPrice',
            'maxPrice',
            'sort',
            'minProductPrice',
            'maxProductPrice',
            'instagramPosts'
        ));
    }

    /**
     * Display featured products.
     *
     * @return \Illuminate\View\View
     */
    public function featured()
    {
        $featuredProducts = $this->productRepository->getFeaturedProducts(8);

        // Get recent Instagram posts
        $instagramPosts = $this->instagramService->getRecentPosts(4);

        return view('shop.products.featured', compact(
            'featuredProducts',
            'instagramPosts'
        ));
    }
}
