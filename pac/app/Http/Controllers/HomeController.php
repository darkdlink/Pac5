<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Service;
use App\Repositories\ProductRepository;
use App\Repositories\ServiceRepository;
use App\Services\Instagram\InstagramService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    protected $productRepository;
    protected $serviceRepository;
    protected $instagramService;

    /**
     * Create a new controller instance.
     *
     * @param ProductRepository $productRepository
     * @param ServiceRepository $serviceRepository
     * @param InstagramService $instagramService
     */
    public function __construct(
        ProductRepository $productRepository,
        ServiceRepository $serviceRepository,
        InstagramService $instagramService
    ) {
        $this->productRepository = $productRepository;
        $this->serviceRepository = $serviceRepository;
        $this->instagramService = $instagramService;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Get featured products
        $featuredProducts = $this->productRepository->getFeaturedProducts(4);

        // Get featured services
        $featuredServices = $this->serviceRepository->getFeaturedServices(3);

        // Get latest products
        $latestProducts = $this->productRepository->getLatestProducts(8);

        // Get customer testimonials
        $testimonials = $this->serviceRepository->getServiceTestimonials(3);

        // Get Instagram feed
        $instagramPosts = $this->instagramService->getRecentPosts(6);

        return view('shop.home', compact(
            'featuredProducts',
            'featuredServices',
            'latestProducts',
            'testimonials',
            'instagramPosts'
        ));
    }

    /**
     * Show the about us page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function about()
    {
        // Get testimonials for about page
        $testimonials = $this->serviceRepository->getServiceTestimonials(4);

        // Get Instagram posts
        $instagramPosts = $this->instagramService->getRecentPosts(4);

        return view('shop.about', compact('testimonials', 'instagramPosts'));
    }

    /**
     * Show the contact page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function contact()
    {
        return view('shop.contact');
    }

    /**
     * Process contact form submission.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submitContact(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'g-recaptcha-response' => 'required|captcha'
        ]);

        // Send email notification
        try {
            \Mail::to(config('shop.contact_email'))
                ->send(new \App\Mail\ContactForm(
                    $request->name,
                    $request->email,
                    $request->subject,
                    $request->message
                ));

            return redirect()->route('contact')
                ->with('success', 'Mensagem enviada com sucesso! Entraremos em contato em breve.');
        } catch (\Exception $e) {
            return redirect()->route('contact')
                ->with('error', 'Erro ao enviar mensagem. Por favor, tente novamente mais tarde.')
                ->withInput();
        }
    }

    /**
     * Show the FAQ page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function faq()
    {
        // Get FAQ categories and questions from database or config
        $faqCategories = config('shop.faq_categories');

        return view('shop.faq', compact('faqCategories'));
    }

    /**
     * Show the terms and conditions page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function terms()
    {
        return view('shop.terms');
    }

    /**
     * Show the privacy policy page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function privacy()
    {
        return view('shop.privacy');
    }

    /**
     * Global search function.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:3|max:100',
        ]);

        $query = $request->input('query');

        // Search products
        $products = Product::where('name', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->where('active', true)
            ->take(8)
            ->get();

        // Search services
        $services = Service::where('name', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->where('active', true)
            ->take(5)
            ->get();

        return view('shop.search', compact('query', 'products', 'services'));
    }

    /**
     * Generate the sitemap
     *
     * @return \Illuminate\Http\Response
     */
    public function sitemap()
    {
        // Get all active products
        $products = Product::where('active', true)->get();

        // Get all active services
        $services = Service::where('active', true)->get();

        $content = view('shop.sitemap', compact('products', 'services'));

        return response($content, 200)
            ->header('Content-Type', 'text/xml');
    }

    /**
     * Display maintenance page
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function maintenance()
    {
        return view('shop.maintenance');
    }
}
