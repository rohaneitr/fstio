<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Contracts\QueryBusInterface;
use App\Application\DTO\AddBuildToCartDto;
use App\Application\DTO\CalculatePriceDto;
use App\Application\DTO\CheckCompatibilityDto;
use App\Application\DTO\CloneBuildDto;
use App\Application\DTO\DeleteBuildDto;
use App\Application\DTO\GetRecommendationsDto;
use App\Application\DTO\SaveBuildDto;
use App\Application\Handlers\AddBuildToCartHandler;
use App\Application\Handlers\CheckCompatibilityHandler;
use App\Application\Handlers\CloneBuildHandler;
use App\Application\Handlers\DeleteBuildHandler;
use App\Application\Handlers\GetRecommendationsHandler;
use App\Application\Handlers\SaveBuildHandler;
use App\Application\Queries\GetProductPriceQuery;
use App\Domain\Repositories\BuildRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Webkul\Product\Models\ProductFlat;

class PCBuilderController extends Controller
{
    /**
     * Render the interactive storefront wizard.
     */
    public function index(?string $uuid = null)
    {
        $buildData = null;

        if ($uuid) {
            $buildRepo = app(BuildRepositoryInterface::class);
            $build = $buildRepo->findByUuid($uuid);
            if ($build) {
                // Prepare build items data
                $items = [];
                foreach ($build->items as $item) {
                    $flat = ProductFlat::where('product_id', $item->product_id)->first();
                    $priceMoney = app(QueryBusInterface::class)->ask(
                        new GetProductPriceQuery(
                            new CalculatePriceDto($item->product_id, 1)
                        )
                    );
                    $priceVal = $priceMoney->getDecimalAmount();
                    $items[$item->component_type->value] = [
                        'id' => $item->product_id,
                        'name' => $flat?->name ?? $item->product->sku,
                        'price' => $priceVal,
                        'formatted_price' => core()->currency($priceVal),
                    ];
                }

                $buildData = [
                    'id' => $build->id,
                    'uuid' => $build->uuid,
                    'name' => $build->name,
                    'version' => $build->version,
                    'total_price' => (float) $build->total_price,
                    'formatted_total_price' => core()->currency((float) $build->total_price),
                    'estimated_wattage' => (int) $build->estimated_wattage,
                    'items' => $items,
                ];
            }
        }

        return view('pc-builder.index', compact('buildData'));
    }

    /**
     * Fetch products suitable for a component slot.
     */
    public function products(Request $request): JsonResponse
    {
        $type = $request->input('type');
        $search = $request->input('search');

        $query = ProductFlat::query()
            ->where('status', 1)
            ->where('visible_individually', 1);

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        // Apply slot-specific attribute/name filtering
        switch ($type) {
            case 'cpu':
                $query->where(function ($q) {
                    $q->where('name', 'like', '%CPU%')
                        ->orWhere('name', 'like', '%Processor%')
                        ->orWhere('name', 'like', '%Intel%')
                        ->orWhere('name', 'like', '%AMD%')
                        ->orWhere('name', 'like', '%Ryzen%')
                        ->orWhere('sku', 'like', '%cpu%')
                        ->orWhereExists(function ($sub) {
                            $sub->select(DB::raw(1))
                                ->from('product_attribute_values')
                                ->join('attributes', 'product_attribute_values.attribute_id', '=', 'attributes.id')
                                ->whereColumn('product_attribute_values.product_id', 'product_flat.product_id')
                                ->whereIn('attributes.code', ['supported_chipsets', 'cpu_required_bios']);
                        });
                });
                break;
            case 'motherboard':
                $query->where(function ($q) {
                    $q->where('name', 'like', '%Motherboard%')
                        ->orWhere('name', 'like', '%ASUS%')
                        ->orWhere('name', 'like', '%Gigabyte%')
                        ->orWhere('name', 'like', '%MSI%')
                        ->orWhere('sku', 'like', '%mb-%')
                        ->orWhereExists(function ($sub) {
                            $sub->select(DB::raw(1))
                                ->from('product_attribute_values')
                                ->join('attributes', 'product_attribute_values.attribute_id', '=', 'attributes.id')
                                ->whereColumn('product_attribute_values.product_id', 'product_flat.product_id')
                                ->whereIn('attributes.code', ['motherboard_chipset', 'motherboard_form_factor']);
                        });
                });
                break;
            case 'ram':
                $query->where(function ($q) {
                    $q->where('name', 'like', '%RAM%')
                        ->orWhere('name', 'like', '%DDR%')
                        ->orWhere('name', 'like', '%Memory%')
                        ->orWhere('name', 'like', '%Corsair%')
                        ->orWhere('sku', 'like', '%ram-%')
                        ->orWhereExists(function ($sub) {
                            $sub->select(DB::raw(1))
                                ->from('product_attribute_values')
                                ->join('attributes', 'product_attribute_values.attribute_id', '=', 'attributes.id')
                                ->whereColumn('product_attribute_values.product_id', 'product_flat.product_id')
                                ->whereIn('attributes.code', ['ram_speed', 'ram_capacity']);
                        });
                });
                break;
            case 'gpu':
                $query->where(function ($q) {
                    $q->where('name', 'like', '%GPU%')
                        ->orWhere('name', 'like', '%Graphics Card%')
                        ->orWhere('name', 'like', '%RTX%')
                        ->orWhere('name', 'like', '%GTX%')
                        ->orWhere('name', 'like', '%Radeon%')
                        ->orWhere('sku', 'like', '%gpu-%')
                        ->orWhereExists(function ($sub) {
                            $sub->select(DB::raw(1))
                                ->from('product_attribute_values')
                                ->join('attributes', 'product_attribute_values.attribute_id', '=', 'attributes.id')
                                ->whereColumn('product_attribute_values.product_id', 'product_flat.product_id')
                                ->whereIn('attributes.code', ['gpu_pcie_gen', 'gpu_slots']);
                        });
                });
                break;
            case 'psu':
                $query->where(function ($q) {
                    $q->where('name', 'like', '%PSU%')
                        ->orWhere('name', 'like', '%Power Supply%')
                        ->orWhere('name', 'like', '%Watt%')
                        ->orWhere('sku', 'like', '%psu-%')
                        ->orWhereExists(function ($sub) {
                            $sub->select(DB::raw(1))
                                ->from('product_attribute_values')
                                ->join('attributes', 'product_attribute_values.attribute_id', '=', 'attributes.id')
                                ->whereColumn('product_attribute_values.product_id', 'product_flat.product_id')
                                ->whereIn('attributes.code', ['psu_length', 'psu_wattage']);
                        });
                });
                break;
            case 'case':
                $query->where(function ($q) {
                    $q->where('name', 'like', '%Case%')
                        ->orWhere('name', 'like', '%Chassis%')
                        ->orWhere('name', 'like', '%Tower%')
                        ->orWhere('sku', 'like', '%case-%')
                        ->orWhereExists(function ($sub) {
                            $sub->select(DB::raw(1))
                                ->from('product_attribute_values')
                                ->join('attributes', 'product_attribute_values.attribute_id', '=', 'attributes.id')
                                ->whereColumn('product_attribute_values.product_id', 'product_flat.product_id')
                                ->whereIn('attributes.code', ['case_expansion_slots', 'case_supported_form_factors']);
                        });
                });
                break;
            case 'cooler':
                $query->where(function ($q) {
                    $q->where('name', 'like', '%Cooler%')
                        ->orWhere('name', 'like', '%Fan%')
                        ->orWhere('sku', 'like', '%cooler-%')
                        ->orWhereExists(function ($sub) {
                            $sub->select(DB::raw(1))
                                ->from('product_attribute_values')
                                ->join('attributes', 'product_attribute_values.attribute_id', '=', 'attributes.id')
                                ->whereColumn('product_attribute_values.product_id', 'product_flat.product_id')
                                ->whereIn('attributes.code', ['fan_count']);
                        });
                });
                break;
            case 'storage':
                $query->where(function ($q) {
                    $q->where('name', 'like', '%SSD%')
                        ->orWhere('name', 'like', '%HDD%')
                        ->orWhere('name', 'like', '%Drive%')
                        ->orWhere('sku', 'like', '%storage-%')
                        ->orWhereExists(function ($sub) {
                            $sub->select(DB::raw(1))
                                ->from('product_attribute_values')
                                ->join('attributes', 'product_attribute_values.attribute_id', '=', 'attributes.id')
                                ->whereColumn('product_attribute_values.product_id', 'product_flat.product_id')
                                ->whereIn('attributes.code', ['storage_interface']);
                        });
                });
                break;
        }

        $products = $query->paginate(20);

        $mapped = $products->getCollection()->map(function ($p) {
            $qty = DB::table('product_inventories')
                ->where('product_id', $p->product_id)
                ->sum('qty');

            $priceMoney = app(QueryBusInterface::class)->ask(
                new GetProductPriceQuery(
                    new CalculatePriceDto($p->product_id, 1)
                )
            );
            $priceVal = $priceMoney->getDecimalAmount();

            return [
                'id' => $p->product_id,
                'sku' => $p->sku,
                'name' => $p->name,
                'price' => $priceVal,
                'formatted_price' => core()->currency($priceVal),
                'in_stock' => $qty > 0,
                'stock_qty' => (int) $qty,
                'url_key' => $p->url_key,
            ];
        });

        return response()->json([
            'data' => $mapped,
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'total' => $products->total(),
        ]);
    }

    /**
     * Perform live compatibility check for chosen parts.
     */
    public function compatibility(Request $request): JsonResponse
    {
        $items = $request->input('items', []);

        // Filter out empty items
        $filteredItems = array_filter($items, fn ($val) => ! empty($val));

        // Format items as array<string, int>
        $itemMap = [];
        foreach ($filteredItems as $key => $val) {
            $itemMap[$key] = (int) $val;
        }

        $dto = new CheckCompatibilityDto($itemMap);
        $handler = app(CheckCompatibilityHandler::class);
        $response = $handler->handle($dto);

        return response()->json([
            'success' => $response->isSuccess(),
            'message' => $response->getMessage(),
            'data' => $response->getPayload(),
        ]);
    }

    /**
     * Get recommendations (e.g. upsell or similar/budget).
     */
    public function recommendations(Request $request, int $productId): JsonResponse
    {
        $strategy = $request->input('strategy', 'similar');

        $dto = new GetRecommendationsDto($productId, $strategy);
        $handler = app(GetRecommendationsHandler::class);
        $response = $handler->handle($dto);

        return response()->json([
            'success' => $response->isSuccess(),
            'message' => $response->getMessage(),
            'data' => $response->getPayload(),
        ]);
    }

    /**
     * Save/update build config.
     */
    public function save(Request $request): JsonResponse
    {
        $items = $request->input('items', []);
        $filteredItems = array_filter($items, fn ($val) => ! empty($val));

        $itemMap = [];
        foreach ($filteredItems as $key => $val) {
            $itemMap[$key] = (int) $val;
        }

        $dto = new SaveBuildDto(
            $request->input('build_id') ? (int) $request->input('build_id') : null,
            auth()->guard('customer')->user()?->id,
            $itemMap,
            (int) $request->input('version', 1),
            $request->input('name')
        );

        $handler = app(SaveBuildHandler::class);
        $response = $handler->handle($dto);

        // Fetch UUID if successful
        $payload = $response->getPayload();
        if ($response->isSuccess() && isset($payload['id'])) {
            $buildRepo = app(BuildRepositoryInterface::class);
            $build = $buildRepo->find($payload['id']);
            if ($build) {
                $payload['uuid'] = $build->uuid;
                $payload['name'] = $build->name;
            }
        }

        return response()->json([
            'success' => $response->isSuccess(),
            'message' => $response->getMessage(),
            'data' => $payload,
        ]);
    }

    /**
     * Clone an existing build config.
     */
    public function clone(int $id): JsonResponse
    {
        $dto = new CloneBuildDto($id, auth()->guard('customer')->user()?->id);
        $handler = app(CloneBuildHandler::class);
        $response = $response = $handler->handle($dto);

        return response()->json([
            'success' => $response->isSuccess(),
            'message' => $response->getMessage(),
            'data' => $response->getPayload(),
        ]);
    }

    /**
     * Delete an existing build.
     */
    public function destroy(int $id): JsonResponse
    {
        $dto = new DeleteBuildDto($id);
        $handler = app(DeleteBuildHandler::class);
        $response = $handler->handle($dto);

        return response()->json([
            'success' => $response->isSuccess(),
            'message' => $response->getMessage(),
        ]);
    }

    /**
     * Convert PC Build items into cart items.
     */
    public function addToCart(int $id): JsonResponse
    {
        $dto = new AddBuildToCartDto($id);
        $handler = app(AddBuildToCartHandler::class);
        $response = $handler->handle($dto);

        return response()->json([
            'success' => $response->isSuccess(),
            'message' => $response->getMessage(),
        ]);
    }
}
