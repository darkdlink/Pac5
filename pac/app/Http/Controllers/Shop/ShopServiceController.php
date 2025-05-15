<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Service;
use App\Repositories\ServiceRepository;
use App\Services\Instagram\InstagramService;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    protected $serviceRepository;
    protected $instagramService;

    /**
     * Create a new controller instance.
     *
     * @param ServiceRepository $serviceRepository
     * @param InstagramService $instagramService
     */
    public function __construct(
        ServiceRepository $serviceRepository,
        InstagramService $instagramService
    ) {
        $this->serviceRepository = $serviceRepository;
        $this->instagramService = $instagramService;
    }

    /**
     * Display a listing of the services.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $category = $request->get('category');
        $query = $request->get('query');

        $services = $this->serviceRepository->getActiveServices($query, $category, 12);
        $categories = Category::where('type', 'service')->get();

        // Get relevant Instagram posts
        $instagramPosts = $this->instagramService->getPostsByHashtag('estetica', 4);

        return view('shop.services.index', compact(
            'services',
            'categories',
            'category',
            'query',
            'instagramPosts'
        ));
    }

    /**
     * Display the specified service.
     *
     * @param Service $service
     * @return \Illuminate\View\View
     */
    public function show(Service $service)
    {
        // Don't show inactive services
        if (!$service->active || !$service->available) {
            abort(404);
        }

        $service->load('category');

        // Get related services
        $relatedServices = $this->serviceRepository->getRelatedServices($service, 3);

        // Get Instagram posts for this type of service
        $instagramPosts = [];
        if ($service->category) {
            $instagramPosts = $this->instagramService->getPostsByHashtag($service->category->name, 4);
        }

        // Get available dates/times for booking (if applicable)
        $availableTimes = [];
        if ($service->requires_booking) {
            $availableTimes = $this->serviceRepository->getAvailableBookingTimes($service);
        }

        return view('shop.services.show', compact(
            'service',
            'relatedServices',
            'instagramPosts',
            'availableTimes'
        ));
    }

    /**
     * Display services by category.
     *
     * @param Category $category
     * @return \Illuminate\View\View
     */
    public function category(Category $category)
    {
        // Ensure this is a service category
        if ($category->type !== 'service') {
            abort(404);
        }

        $services = $this->serviceRepository->getServicesByCategory($category->id, 12);
        $categories = Category::where('type', 'service')->get();

        // Get Instagram posts related to this category
        $instagramPosts = $this->instagramService->getPostsByHashtag($category->name, 4);

        return view('shop.services.category', compact(
            'services',
            'category',
            'categories',
            'instagramPosts'
        ));
    }

    /**
     * Display featured services.
     *
     * @return \Illuminate\View\View
     */
    public function featured()
    {
        $featuredServices = $this->serviceRepository->getFeaturedServices(6);

        // Get recent Instagram posts
        $instagramPosts = $this->instagramService->getRecentPosts(4);

        // Get testimonials
        $testimonials = $this->serviceRepository->getServiceTestimonials(3);

        return view('shop.services.featured', compact(
            'featuredServices',
            'instagramPosts',
            'testimonials'
        ));
    }

    /**
     * Request booking for a service.
     *
     * @param Request $request
     * @param Service $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function requestBooking(Request $request, Service $service)
    {
        $request->validate([
            'booking_date' => 'required|date|after:today',
            'booking_time' => 'required',
            'notes' => 'nullable|string|max:500',
        ]);

        // Check if user is authenticated
        if (!auth()->check()) {
            // Store booking details in session and redirect to login
            session(['booking_details' => [
                'service_id' => $service->id,
                'booking_date' => $request->booking_date,
                'booking_time' => $request->booking_time,
                'notes' => $request->notes,
            ]]);

            return redirect()->route('login')
                ->with('message', 'Por favor, faça login para continuar com sua reserva.');
        }

        // Create booking
        $booking = $this->serviceRepository->createServiceBooking(
            auth()->id(),
            $service->id,
            $request->booking_date,
            $request->booking_time,
            $request->notes
        );

        if ($booking) {
            return redirect()->route('shop.bookings.show', $booking->id)
                ->with('success', 'Solicitação de reserva enviada com sucesso! Aguarde a confirmação.');
        }

        return redirect()->route('shop.services.show', $service->id)
            ->with('error', 'Ocorreu um erro ao solicitar a reserva. Por favor, tente novamente.');
    }
}
