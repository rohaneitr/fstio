<?php

declare(strict_types=1);

namespace App\Http\DataGrids;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Webkul\DataGrid\DataGrid;

class BrandDataGrid extends DataGrid
{
    /**
     * Prepare query builder.
     *
     * @return Builder
     */
    public function prepareQueryBuilder()
    {
        return DB::table('brands')
            ->select(
                'id',
                'logo_light',
                'name',
                'slug',
                'website_url',
                'status',
                'sort_order'
            );
    }

    /**
     * Add columns.
     *
     * @return void
     */
    public function prepareColumns()
    {
        $this->addColumn([
            'index' => 'id',
            'label' => 'ID',
            'type' => 'integer',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'logo_light',
            'label' => 'Logo',
            'type' => 'string',
            'searchable' => false,
            'filterable' => false,
            'sortable' => false,
            'closure' => function ($row) {
                if (! $row->logo_light) {
                    return '';
                }

                return '<img src="'.Storage::url($row->logo_light).'" class="w-10 h-10 object-contain rounded border dark:border-gray-800">';
            },
        ]);

        $this->addColumn([
            'index' => 'name',
            'label' => 'Name',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'slug',
            'label' => 'Slug',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'website_url',
            'label' => 'Website',
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'status',
            'label' => 'Status',
            'type' => 'boolean',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
            'closure' => function ($row) {
                if ($row->status) {
                    return '<span class="badge badge-md badge-success">Active</span>';
                }

                return '<span class="badge badge-md badge-danger">Inactive</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'sort_order',
            'label' => 'Sort Order',
            'type' => 'integer',
            'searchable' => false,
            'filterable' => true,
            'sortable' => true,
        ]);
    }

    /**
     * Prepare actions.
     *
     * @return void
     */
    public function prepareActions()
    {
        if (bouncer()->hasPermission('catalog.brands.edit')) {
            $this->addAction([
                'icon' => 'icon-edit',
                'title' => 'Edit',
                'method' => 'GET',
                'url' => function ($row) {
                    return route('admin.catalog.brands.edit', $row->id);
                },
            ]);
        }

        if (bouncer()->hasPermission('catalog.brands.delete')) {
            $this->addAction([
                'icon' => 'icon-delete',
                'title' => 'Delete',
                'method' => 'DELETE',
                'url' => function ($row) {
                    return route('admin.catalog.brands.delete', $row->id);
                },
            ]);
        }
    }

    /**
     * Prepare mass actions.
     *
     * @return void
     */
    public function prepareMassActions()
    {
        if (bouncer()->hasPermission('catalog.brands.delete')) {
            $this->addMassAction([
                'icon' => 'icon-delete',
                'title' => 'Delete',
                'method' => 'POST',
                'url' => route('admin.catalog.brands.mass_delete'),
            ]);
        }
    }
}
