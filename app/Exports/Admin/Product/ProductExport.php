<?php

namespace App\Exports\Admin\Product;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Product::with(['brand', 'category'])->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'SKU',
            'Brand',
            'Category',
            'Regular Price',
            'Sale Price',
            'Stock Status',
            'Status'
        ];
    }

    public function map($product): array
    {
        return [
            $product->id,
            $product->name,
            $product->SKU,
            $product->brand->name ?? 'N/A',
            $product->category->name ?? 'N/A',
            $product->regular_price,
            $product->sale_price,
            $product->stock_status,
            $product->status,
        ];
    }
}
