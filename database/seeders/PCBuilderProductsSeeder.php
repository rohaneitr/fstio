<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Webkul\Product\Models\ProductProxy;

class PCBuilderProductsSeeder extends Seeder
{
    public function run()
    {
        $products = [
            // --- CPUs ---
            [
                'name' => 'AMD Ryzen 5 7600 Processor',
                'sku' => 'cpu-ryzen5-7600',
                'price' => 199.99,
                'attributes' => [
                    'supported_chipsets' => 'B650,X670,A620',
                    'cpu_required_bios' => '1.0.0',
                    'cpu_supports_ecc' => 1,
                    'power_draw' => 65,
                    'socket' => 'AM5',
                ],
            ],
            [
                'name' => 'Intel Core i9-14900K Processor',
                'sku' => 'cpu-i9-14900k',
                'price' => 529.99,
                'attributes' => [
                    'supported_chipsets' => 'Z790,B760,H770',
                    'cpu_required_bios' => 'F10',
                    'cpu_supports_ecc' => 0,
                    'power_draw' => 125,
                    'socket' => 'LGA1700',
                ],
            ],

            // --- Motherboards ---
            [
                'name' => 'MSI PRO B650-P WIFI Motherboard',
                'sku' => 'mb-msi-b650',
                'price' => 189.99,
                'attributes' => [
                    'motherboard_chipset' => 'B650',
                    'motherboard_bios_version' => '1.2.0',
                    'motherboard_supports_ecc' => 1,
                    'supported_ram_profiles' => 'EXPO,XMP',
                    'motherboard_pcie_gen' => 4,
                    'motherboard_pcie_lanes' => 24,
                    'motherboard_sata_ports' => 4,
                    'motherboard_m2_slots' => 2,
                    'motherboard_m2_pcie_gen' => 4,
                    'motherboard_shares_lanes' => 1,
                    'motherboard_usb3_headers' => 1,
                    'motherboard_typec_headers' => 1,
                    'motherboard_audio_headers' => 'HD Audio',
                    'motherboard_5v_argb_headers' => 2,
                    'motherboard_12v_rgb_headers' => 1,
                    'motherboard_fan_headers' => 5,
                    'motherboard_form_factor' => 'ATX',
                    'socket' => 'AM5',
                ],
            ],
            [
                'name' => 'Gigabyte Z790 AORUS ELITE AX Motherboard',
                'sku' => 'mb-aorus-z790',
                'price' => 239.99,
                'attributes' => [
                    'motherboard_chipset' => 'Z790',
                    'motherboard_bios_version' => 'F2',
                    'motherboard_supports_ecc' => 0,
                    'supported_ram_profiles' => 'XMP',
                    'motherboard_pcie_gen' => 5,
                    'motherboard_pcie_lanes' => 20,
                    'motherboard_sata_ports' => 6,
                    'motherboard_m2_slots' => 4,
                    'motherboard_m2_pcie_gen' => 4,
                    'motherboard_shares_lanes' => 0,
                    'motherboard_usb3_headers' => 2,
                    'motherboard_typec_headers' => 1,
                    'motherboard_audio_headers' => 'HD Audio',
                    'motherboard_5v_argb_headers' => 3,
                    'motherboard_12v_rgb_headers' => 2,
                    'motherboard_fan_headers' => 6,
                    'motherboard_form_factor' => 'ATX',
                    'socket' => 'LGA1700',
                ],
            ],

            // --- RAM ---
            [
                'name' => 'Corsair Vengeance DDR5 32GB (2x16GB) 6000MHz',
                'sku' => 'ram-corsair-ddr5-32gb',
                'price' => 114.99,
                'attributes' => [
                    'ram_speed' => 6000,
                    'ram_capacity' => 32,
                    'ram_modules' => 2,
                    'ram_is_ecc' => 0,
                    'ram_profile' => 'both',
                    'ram_type' => 'DDR5',
                ],
            ],
            [
                'name' => 'Crucial 16GB DDR5 4800MHz ECC Memory',
                'sku' => 'ram-crucial-ddr5-ecc',
                'price' => 89.99,
                'attributes' => [
                    'ram_speed' => 4800,
                    'ram_capacity' => 16,
                    'ram_modules' => 1,
                    'ram_is_ecc' => 1,
                    'ram_profile' => 'JEDEC',
                    'ram_type' => 'DDR5',
                ],
            ],

            // --- GPUs ---
            [
                'name' => 'ASUS TUF RTX 4070 Ti SUPER 16GB GPU',
                'sku' => 'gpu-rtx4070ti',
                'price' => 799.99,
                'attributes' => [
                    'gpu_pcie_gen' => 4,
                    'pcie_lanes' => 16,
                    'gpu_slots' => 3.0,
                    'gpu_pcie_connectors_required' => 2,
                    'gpu_requires_12vhpwr' => 1,
                    'power_draw' => 285,
                    'gpu_length' => 305,
                ],
            ],
            [
                'name' => 'Gigabyte GTX 1650 OC 4GB Graphics Card',
                'sku' => 'gpu-gtx1650',
                'price' => 149.99,
                'attributes' => [
                    'gpu_pcie_gen' => 3,
                    'pcie_lanes' => 8,
                    'gpu_slots' => 2.0,
                    'gpu_pcie_connectors_required' => 0,
                    'gpu_requires_12vhpwr' => 0,
                    'power_draw' => 75,
                    'gpu_length' => 170,
                ],
            ],

            // --- PSUs ---
            [
                'name' => 'Corsair RM750e 750W Gold Power Supply',
                'sku' => 'psu-corsair-rm750e',
                'price' => 99.99,
                'attributes' => [
                    'psu_wattage' => 750,
                    'psu_length' => 140,
                    'psu_pcie_connectors_available' => 4,
                    'psu_has_12vhpwr' => 0,
                    'power_draw' => 0,
                ],
            ],
            [
                'name' => 'MSI MAG A850GL 850W ATX 3.0 PCIe 5 PSU',
                'sku' => 'psu-msi-a850gl',
                'price' => 129.99,
                'attributes' => [
                    'psu_wattage' => 850,
                    'psu_length' => 150,
                    'psu_pcie_connectors_available' => 4,
                    'psu_has_12vhpwr' => 1,
                    'power_draw' => 0,
                ],
            ],

            // --- Cases ---
            [
                'name' => 'NZXT H9 Flow Mid-Tower Case',
                'sku' => 'case-nzxt-h9',
                'price' => 159.99,
                'attributes' => [
                    'case_expansion_slots' => 7,
                    'case_max_psu_length' => 200,
                    'case_requires_usb3' => 1,
                    'case_requires_typec' => 1,
                    'case_audio_type' => 'HD Audio',
                    'case_supported_form_factors' => 'ATX,Micro-ATX,Mini-ITX',
                    'case_gpu_clearance' => 435,
                    'case_cooler_clearance' => 165,
                ],
            ],
            [
                'name' => 'Lian Li O11 Dynamic MINI Tower Case',
                'sku' => 'case-lianli-mini',
                'price' => 119.99,
                'attributes' => [
                    'case_expansion_slots' => 5,
                    'case_max_psu_length' => 130,
                    'case_requires_usb3' => 1,
                    'case_requires_typec' => 1,
                    'case_audio_type' => 'HD Audio',
                    'case_supported_form_factors' => 'Micro-ATX,Mini-ITX',
                    'case_gpu_clearance' => 395,
                    'case_cooler_clearance' => 170,
                ],
            ],

            // --- Coolers ---
            [
                'name' => 'DeepCool AK620 Digital CPU Air Cooler',
                'sku' => 'cooler-ak620',
                'price' => 69.99,
                'attributes' => [
                    'fan_count' => 2,
                    'rgb_type' => '5V ARGB',
                    'cooler_height' => 162,
                ],
            ],
            [
                'name' => 'Noctua NH-D15 chromax.black CPU Cooler',
                'sku' => 'cooler-nhd15',
                'price' => 119.99,
                'attributes' => [
                    'fan_count' => 2,
                    'rgb_type' => 'None',
                    'cooler_height' => 165,
                ],
            ],

            // --- Storage ---
            [
                'name' => 'Samsung 990 PRO 2TB NVMe M.2 SSD',
                'sku' => 'storage-990pro-2tb',
                'price' => 169.99,
                'attributes' => [
                    'storage_interface' => 'M.2 NVMe',
                    'storage_pcie_gen' => 4,
                ],
            ],
            [
                'name' => 'Crucial MX500 1TB SATA 2.5 SSD',
                'sku' => 'storage-mx500-1tb',
                'price' => 79.99,
                'attributes' => [
                    'storage_interface' => 'SATA',
                    'storage_pcie_gen' => 3,
                ],
            ],
        ];

        foreach ($products as $item) {
            // Check if product SKU already exists
            $existing = ProductProxy::where('sku', $item['sku'])->first();
            if ($existing) {
                continue;
            }

            // Create simple product
            $product = ProductProxy::create([
                'type' => 'simple',
                'attribute_family_id' => 1,
                'sku' => $item['sku'],
            ]);

            // Set default stock and basic attributes
            $this->setInventory($product->id, 100);
            $this->setAttribute($product->id, 'price', $item['price']);
            $this->setAttribute($product->id, 'name', $item['name']);

            // Insert EAV attributes
            foreach ($item['attributes'] as $code => $value) {
                $this->setAttribute($product->id, $code, $value);
            }

            // Price indexing
            $channelId = DB::table('channels')->value('id') ?? 1;
            $customerGroupId = DB::table('customer_groups')->value('id') ?? 1;

            DB::table('product_price_indices')->updateOrInsert([
                'product_id' => $product->id,
                'channel_id' => $channelId,
                'customer_group_id' => $customerGroupId,
            ], [
                'min_price' => $item['price'],
                'max_price' => $item['price'],
            ]);

            // Flat table mapping
            $channelCode = DB::table('channels')->value('code') ?? 'default';
            $localeCode = DB::table('locales')->value('code') ?? 'en';

            DB::table('product_flat')->updateOrInsert([
                'product_id' => $product->id,
                'channel' => $channelCode,
                'locale' => $localeCode,
            ], [
                'price' => $item['price'],
                'name' => $item['name'],
                'sku' => $item['sku'],
                'status' => 1,
                'visible_individually' => 1,
                'url_key' => 'product-'.$product->id,
            ]);
        }
    }

    private function setAttribute(int $productId, string $attributeCode, mixed $value): void
    {
        $attribute = DB::table('attributes')->where('code', $attributeCode)->first();
        if (! $attribute) {
            $attributeId = DB::table('attributes')->insertGetId([
                'code' => $attributeCode,
                'admin_name' => $attributeCode,
                'type' => 'text',
                'position' => 1,
                'is_required' => 0,
                'is_unique' => 0,
            ]);
        } else {
            $attributeId = $attribute->id;
        }

        $column = 'text_value';
        if (is_float($value)) {
            $column = 'float_value';
        } elseif (is_int($value) || is_bool($value)) {
            $column = 'integer_value';
            $value = (int) $value;
        }

        DB::table('product_attribute_values')->updateOrInsert([
            'product_id' => $productId,
            'attribute_id' => $attributeId,
        ], [
            $column => $value,
        ]);
    }

    private function setInventory(int $productId, int $qty): void
    {
        $sourceId = DB::table('inventory_sources')->value('id') ?? 1;
        $channelId = DB::table('channels')->value('id') ?? 1;

        DB::table('product_inventories')->updateOrInsert([
            'product_id' => $productId,
            'inventory_source_id' => $sourceId,
        ], [
            'qty' => $qty,
        ]);

        DB::table('product_inventory_indices')->updateOrInsert([
            'product_id' => $productId,
            'channel_id' => $channelId,
        ], [
            'qty' => $qty,
        ]);
    }
}
