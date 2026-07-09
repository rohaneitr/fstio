<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Models\Brand;
use App\Domain\Models\BrandSeries;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            [
                'name' => 'Asus',
                'slug' => 'asus',
                'website_url' => 'https://www.asus.com',
                'status' => true,
                'description' => 'AsusTek Computer Inc. is a Taiwanese multinational computer and phone hardware and electronics company.',
                'meta_title' => 'Asus Products - Fast Computer',
                'meta_keywords' => 'asus, laptop, motherboard, rog',
                'meta_description' => 'Buy Asus laptops, motherboards and graphics cards online.',
                'sort_order' => 1,
                'series' => ['ROG', 'TUF Gaming', 'ZenBook', 'VivoBook'],
            ],
            [
                'name' => 'MSI',
                'slug' => 'msi',
                'website_url' => 'https://www.msi.com',
                'status' => true,
                'description' => 'Micro-Star International Co., Ltd is a Taiwanese multinational information technology corporation.',
                'meta_title' => 'MSI Gaming Laptops & Components',
                'meta_keywords' => 'msi, gaming, laptop, graphics card',
                'meta_description' => 'Discover MSI gaming laptops, motherboards, and PC components.',
                'sort_order' => 2,
                'series' => ['Optix', 'MAG', 'MPG', 'MEG'],
            ],
            [
                'name' => 'Gigabyte',
                'slug' => 'gigabyte',
                'website_url' => 'https://www.gigabyte.com',
                'status' => true,
                'description' => 'Giga-Byte Technology Co., Ltd. is a Taiwanese manufacturer and distributor of computer hardware.',
                'meta_title' => 'Gigabyte Motherboards & Aorus Gaming',
                'meta_keywords' => 'gigabyte, aorus, motherboard, gpu',
                'meta_description' => 'Shop Gigabyte Aorus motherboards and graphics cards.',
                'sort_order' => 3,
                'series' => ['Aorus', 'Aero', 'Eagle', 'Windforce'],
            ],
        ];

        foreach ($brands as $brandData) {
            $seriesList = $brandData['series'];
            unset($brandData['series']);

            $brand = Brand::create($brandData);

            foreach ($seriesList as $seriesName) {
                BrandSeries::create([
                    'brand_id' => $brand->id,
                    'name' => $seriesName,
                    'slug' => strtolower(str_replace(' ', '-', $seriesName)),
                ]);
            }
        }
    }
}
