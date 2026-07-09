<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Application\Contracts\CommandBusInterface;
use App\Application\DTO\CreateBrandDto;
use App\Application\DTO\DeleteBrandDto;
use App\Application\DTO\UpdateBrandDto;
use App\Application\Exceptions\ValidationException;
use App\Domain\Repositories\BrandRepositoryInterface;
use App\Domain\ValueObjects\Slug;
use App\Http\Controllers\Controller;
use App\Http\DataGrids\BrandDataGrid;
use App\Http\Requests\BrandRequest;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function __construct(
        protected CommandBusInterface $commandBus,
        protected BrandRepositoryInterface $brandRepository
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(BrandDataGrid::class)->process();
        }

        return view('admin.catalog.brands.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.catalog.brands.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BrandRequest $request)
    {
        try {
            $response = $this->commandBus->dispatch(new CreateBrandDto(
                $request->input('name'),
                new Slug($request->input('slug')),
                $request->file('logo_light'),
                $request->file('logo_dark'),
                $request->input('website_url'),
                (bool) $request->input('status', true),
                $request->input('description'),
                $request->input('meta_title'),
                $request->input('meta_keywords'),
                $request->input('meta_description'),
                (int) $request->input('sort_order', 0)
            ));

            session()->flash('success', $response->getMessage());

            return redirect()->route('admin.catalog.brands.index');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id)
    {
        $brand = $this->brandRepository->find($id);

        if (! $brand) {
            abort(404);
        }

        return view('admin.catalog.brands.edit', compact('brand'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BrandRequest $request, int $id)
    {
        try {
            $response = $this->commandBus->dispatch(new UpdateBrandDto(
                $id,
                $request->input('name'),
                new Slug($request->input('slug')),
                $request->file('logo_light'),
                $request->file('logo_dark'),
                $request->input('website_url'),
                (bool) $request->input('status', true),
                $request->input('description'),
                $request->input('meta_title'),
                $request->input('meta_keywords'),
                $request->input('meta_description'),
                (int) $request->input('sort_order', 0),
                (bool) $request->input('delete_logo_light', false),
                (bool) $request->input('delete_logo_dark', false)
            ));

            session()->flash('success', $response->getMessage());

            return redirect()->route('admin.catalog.brands.index');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        try {
            $response = $this->commandBus->dispatch(new DeleteBrandDto($id));

            return response()->json([
                'message' => 'Brand deleted successfully.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Mass delete resource.
     */
    public function massDestroy(Request $request)
    {
        $indices = $request->input('indices', []);
        foreach ($indices as $id) {
            try {
                $this->commandBus->dispatch(new DeleteBrandDto((int) $id));
            } catch (\Throwable $e) {
                // Keep going
            }
        }

        session()->flash('success', 'Selected brands deleted successfully.');

        return redirect()->route('admin.catalog.brands.index');
    }
}
