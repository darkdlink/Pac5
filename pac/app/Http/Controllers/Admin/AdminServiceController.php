<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Service\StoreServiceRequest;
use App\Http\Requests\Service\UpdateServiceRequest;
use App\Models\Category;
use App\Models\Service;
use App\Repositories\ServiceRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller
{
    protected $serviceRepository;

    /**
     * Create a new controller instance.
     *
     * @param ServiceRepository $serviceRepository
     */
    public function __construct(ServiceRepository $serviceRepository)
    {
        $this->middleware('admin.access');
        $this->serviceRepository = $serviceRepository;
    }

    /**
     * Display a listing of the services.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = $request->get('query');
        $category = $request->get('category');

        $services = $this->serviceRepository->getAllServices($query, $category, 15);
        $categories = Category::all();

        return view('admin.services.index', compact('services', 'categories', 'query', 'category'));
    }

    /**
     * Show the form for creating a new service.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $categories = Category::all();
        return view('admin.services.create', compact('categories'));
    }

    /**
     * Store a newly created service in storage.
     *
     * @param StoreServiceRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreServiceRequest $request)
    {
        $data = $request->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('services', 'public');
            $data['image'] = $path;
        }

        $service = $this->serviceRepository->createService($data);

        return redirect()->route('admin.services.index')
            ->with('success', 'Serviço criado com sucesso.');
    }

    /**
     * Display the specified service.
     *
     * @param Service $service
     * @return \Illuminate\View\View
     */
    public function show(Service $service)
    {
        $service->load('category');
        return view('admin.services.show', compact('service'));
    }

    /**
     * Show the form for editing the specified service.
     *
     * @param Service $service
     * @return \Illuminate\View\View
     */
    public function edit(Service $service)
    {
        $categories = Category::all();
        return view('admin.services.edit', compact('service', 'categories'));
    }

    /**
     * Update the specified service in storage.
     *
     * @param UpdateServiceRequest $request
     * @param Service $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateServiceRequest $request, Service $service)
    {
        $data = $request->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($service->image) {
                Storage::disk('public')->delete($service->image);
            }

            $path = $request->file('image')->store('services', 'public');
            $data['image'] = $path;
        }

        $this->serviceRepository->updateService($service, $data);

        return redirect()->route('admin.services.index')
            ->with('success', 'Serviço atualizado com sucesso.');
    }

    /**
     * Remove the specified service from storage.
     *
     * @param Service $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Service $service)
    {
        try {
            // Delete service image if exists
            if ($service->image) {
                Storage::disk('public')->delete($service->image);
            }

            $this->serviceRepository->deleteService($service);

            return redirect()->route('admin.services.index')
                ->with('success', 'Serviço excluído com sucesso.');
        } catch (\Exception $e) {
            return redirect()->route('admin.services.index')
                ->with('error', 'Erro ao excluir o serviço.');
        }
    }

    /**
     * Toggle service featured status.
     *
     * @param Service $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleFeatured(Service $service)
    {
        $service->featured = !$service->featured;
        $service->save();

        $status = $service->featured ? 'destacado' : 'removido dos destaques';

        return redirect()->route('admin.services.index')
            ->with('success', "Serviço {$status} com sucesso.");
    }

    /**
     * Toggle service availability status.
     *
     * @param Service $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleAvailability(Service $service)
    {
        $service->available = !$service->available;
        $service->save();

        $status = $service->available ? 'disponível' : 'indisponível';

        return redirect()->route('admin.services.index')
            ->with('success', "Serviço definido como {$status} com sucesso.");
    }
}
