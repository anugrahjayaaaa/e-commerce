<x-admin-layout>
    <!-- Main Content Start -->

    <main class="flex-1 overflow-y-auto p-6 bg-gray-100">

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Products</h1>
                <p class="text-sm text-gray-500">Manage your product catalog</p>
            </div>
            <div class="flex gap-3">
                <button type="button" id="bulk-delete-btn"
                    class="hidden bg-red-600 hover:bg-red-700 text-white px-5 py-2.5 rounded-lg text-sm font-medium transition flex -item-center gap-2 shadow-sm">
                    <i class="fa-solid fa-trash"></i> Deleted Selected (<span id="selected-count">0</span>)
                </button>

                <a href="{{ route('admin.products.create') }}"
                    class="bg-primary hover:bg-blue-600 text-white px-5 py-2.5 rounded-lg text-sm font-medium transition flex items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-plus"></i> Add New Product
                </a>
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6">

            <div class="flex flex-col md:flex-row gap-4 justify-between">
                <form action="{{ route('admin.products.index') }}" method="GET"
                    class="flex flex-col md:flex-row gap-4 justify-between">

                    <div class="flex flex-col md:flex-row gap-4 w-full md:w-auto">
                        <!-- Search Input: Added quotes for value attribute to prevent parsing errors -->
                        <div class="relative w-full md:w-64">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                <i class="fa-solid fa-search text-gray-400"></i>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}"
                                onkeypress="if(event.key==='enter') this.form.submit()"
                                class="w-full pl-10 pr-4 py-2 border rounded-lg text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                placeholder="Search product...">
                        </div>

                        <!-- Category Dropdown: Using request() helper for persistent state -->
                        <select name="category" onchange="this.form.submit()"
                            class="w-full md:w-48 border px-3 py-2 rounded-lg text-sm focus:outline-none focus:border-primary bg-white text-gray-600">
                            <option value="">All Categories</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(request('category') == $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>

                        <!-- Brand Dropdown: Using request() helper for persistent state -->
                        <select name="brand" onchange="this.form.submit()"
                            class="w-full md:w-48 border px-3 py-2 rounded-lg text-sm focus:outline-none focus:border-primary bg-white text-gray-600">
                            <option value="">All Brands</option>
                            @foreach ($brands as $brand)
                                <option value="{{ $brand->id }}" @selected(request('brand') == $brand->id)>
                                    {{ $brand->name }}
                                </option>
                            @endforeach
                        </select>

                        <!-- Status Dropdown: Added logic to persist selection state -->
                        <select name="status" onchange="this.form.submit()"
                            class="w-full md:w-40 border px-3 py-2 rounded-lg text-sm focus:outline-none focus:border-primary bg-white text-gray-600">
                            <option value="">All Status</option>
                            <option value="0" @selected(request('status') == '0')>Draft</option>
                            <option value="1" @selected(request('status') == '1 ')>Published</option>
                        </select>

                        <!-- Clear Filters Button -->
                        @if (request()->hasAny(['search', 'category', 'brand', 'status']))
                            <a href="{{ route('admin.products.index') }}"
                                class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors duration-200">
                                <i class="fa-solid fa-xmark text-xs"></i>
                                <span>Clear</span>
                            </a>
                        @endif
                    </div>

                    <!-- Submit button kept hidden for UI cleanliness, triggered via 'Enter' key -->
                    <button type="submit" class="hidden">Apply</button>
                </form>
                {{-- buttons --}}
                <div class="flex gap-2">
                    {{-- print --}}
                    <button type="button" onclick="printElement('printable-area')"
                        class="border border-gray-300 text-gray-600 hover:bg-gray-50 px-4 py-2 rounded-lg text-sm font-medium transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-print"></i> Print
                    </button>
                    {{-- export --}}
                    <a href="{{ route('admin.products.export') }}"
                        class="border border-gray-300 text-gray-600 hover:bg-gray-50 px-4 py-2 rounded-lg text-sm font-medium transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-file-export"></i> Export
                    </a>
                </div>

            </div>

        </div>

        <div id="printable-area" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <form id="bulk-action-form" method="POST" action="{{ route('admin.products.bulk-destroy') }}">
                @csrf
                @method('DELETE')

                @if (session('success'))
                    <div class="px-6 py-4 bg-green-100 text-green-700 text-sm rounded-tl-xl rounded-tr-xl">
                        {{ session('success') }}
                    </div>
                @endif
                <div class="overflow-x-auto">
                    <table class="w-full text-left whitespace-nowrap">
                        <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold">
                            <tr>
                                <th class="px-6 py-4">
                                    <input type="checkbox" id="select-all"
                                        class="rounded border-gray-300 text-primary focus:ring-primary">
                                </th>
                                <th class="px-6 py-4">Product Name</th>
                                <th class="px-6 py-4">Brand</th>
                                <th class="px-6 py-4">Category</th>
                                <th class="px-6 py-4">Price</th>
                                <th class="px-6 py-4">Stock</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($products as $product)
                                <tr class="hover:bg-gray-50 transition">
                                    {{-- checkbox --}}
                                    <td class="px-6 py-4">
                                        <input type="checkbox" name="ids[]" value={{ $product->id }}
                                            class="checkbox rounded border-gray-300 text-primary focus:ring-primary">
                                    </td>
                                    {{-- name --}}
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            @if (!empty($product->image))
                                                <img src="{{ asset('uploads/products/thumbnails/' . $product->image) }}"
                                                    class="w-12 h-12 rounded object-cover border"
                                                    alt="{{ $product->name }}">
                                            @else
                                                <div
                                                    class="w-12 h-12 rounded bg-gray-100 border flex items-center justify-center text-gray-400">
                                                    <i class="fa-solid fa-image text-lg"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <p class="font-semibold text-gray-800 text-sm">{{ $product->name }}</p>
                                                <p class="text-xs text-gray-500">SKU: {{ $product->SKU }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    {{-- brand --}}
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ !empty($product->brand->name) ? $product->brand->name : '-' }}</td>
                                    {{-- category --}}
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ !empty($product->category->name) ? $product->category->name : '-' }}</td>
                                    {{-- price --}}
                                    <td class="px-6 py-4 text-sm font-medium text-gray-800">
                                        @if ($product->sale_price)
                                            <span
                                                class="line-through text-gray-400 mr-2">${{ number_format($product->regular_price, 2) }}
                                            </span>
                                            <span
                                                class="text-primary">${{ number_format($product->sale_price, 2) }}</span>
                                        @else
                                            <span
                                                class="text-gray-800">${{ number_format($product->regular_price, 2) }}</span>
                                        @endif
                                    </td>
                                    {{-- stock --}}
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $product->quantity }}</td>
                                    {{-- status --}}
                                    <td class="px-6 py-4">
                                        @if ($product->status)
                                            <span
                                                class="bg-green-100 text-green-700 px-2.5 py-1 rounded-full text-xs font-semibold">
                                                Published
                                            </span>
                                        @else
                                            <span
                                                class="bg-orange-100 text-orange-700 px-2.5 py-1 rounded-full text-xs font-semibold">
                                                Draft
                                            </span>
                                        @endif
                                    </td>
                                    {{-- actions --}}
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            {{-- edit --}}
                                            <a href="{{ route('admin.products.edit', $product) }}"
                                                class="w-8 h-8 rounded-full hover:bg-gray-100 text-blue-500 transition flex items-center justify-center"
                                                title="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                            {{-- delete --}}
                                            <button type="button"
                                                class="js-delete-trigger w-8 h-8 rounded-full hover:bg-gray-100 text-red-500 transition flex items-center justify-center"
                                                data-name="{{ $product->name }}"
                                                data-url="{{ route('admin.products.destroy', $product) }}"
                                                data-type="product" title="Delete">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center text-gray-500">
                                            <i class="fa-solid fa-boxes-stacked text-4xl mb-3 text-gray-300"></i>
                                            <h3 class="text-lg font-medium text-gray-900">Products not available</h3>
                                            <p class="text-sm mt-1">You haven't added any products to your store yet.
                                            </p>
                                            <a href="{{ route('admin.products.create') }}"
                                                class="mt-4 text-primary hover:underline text-sm font-medium">
                                                Add your first product
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>
            <div
                class="px-6 py-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                {{-- pagination --}}
                {{ $products->links() }}
            </div>

    </main>

    <!-- Main Content End -->
    {{-- delete modal --}}
    @include('admin.partials.modals.delete')

    {{-- custom script delete modal --}}
    @include('admin.partials.scripts.delete-modal')

    {{-- bulk delete --}}
    @include('admin.partials.scripts.bulk-delete')

    {{-- print area --}}
    @include('admin.partials.scripts.print-utility')
</x-admin-layout>
