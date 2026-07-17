<x-app-layout>
    {{-- style custom range slider --}}
    @include('partials.styles.custom-range-slider')
    <!-- Main Content Start -->

    <div class="relative bg-sky-700 text-white h-64 flex items-center justify-center bg-cover bg-center"
        style="background-image: url('assets/images/page-banner.jpg');">
        <div class="absolute inset-0 bg-black bg-opacity-40"></div>
        <div class="relative z-10 text-center">
            <h2 class="text-4xl font-bold mb-2">Shop</h2>
            <ul class="flex justify-center space-x-2 text-sm">
                <li><a href="{{ route('home.index') }}" class="hover:text-primary">Home</a></li>
                <li>/</li>
                <li class="text-primary">Shop</li>
            </ul>
        </div>
    </div>

    <div class="container mx-auto px-4 py-16">
        <form id="form-filter" method="GET" action="{{ route('shop.index') }}">
            <div class="flex flex-col lg:flex-row gap-8">
                <aside class="w-full lg:w-1/4 order-2 lg:order-1 space-y-8">
                    {{-- clear filter --}}
                    @if (request()->anyFilled(['search', 'brand', 'category', 'min_price', 'max_price', 'sort_by', 'per_page']))
                        <div class="mt-3 flex items-center justify-between animate-fade-in">
                            <span class="text-xs font-medium text-gray-400">Filters are active</span>
                            <a href="{{ route('shop.index') }}"
                                class="inline-flex items-center space-x-1 text-xs font-semibold text-red-500 hover:text-red-600 transition-colors">
                                <i class="fa-solid fa-xmark"></i>
                                <span>Clear All Filters</span>
                            </a>
                        </div>
                    @endif
                    {{-- search --}}
                    <div class="bg-gray-50 p-6 rounded-lg border">
                        <form class="relative">
                            <input name="search" value="{{ request('search') }}" type="text"
                                placeholder="Search product..."
                                class="w-full border p-3 rounded focus:outline-none focus:border-primary pr-10">
                            <button type="button" class="absolute right-3 top-3 text-gray-400 hover:text-primary">
                                <iclass="fa fa-search"></i>
                            </button>
                        </form>
                    </div>
                    {{-- brand --}}
                    <div class="bg-gray-50 p-6 rounded-lg border">
                        <h4 class="font-bold text-lg mb-4">Brands</h4>
                        <ul class="space-y-3">
                            @foreach ($brands as $brand)
                                <li class="flex items-center">
                                    <label class="flex items-center cursor-pointer hover:text-primary">
                                        <input name="brand[]" type="checkbox" value="{{ $brand->id }}"
                                            {{ in_array($brand->id, request('brand', [])) ? 'checked' : '' }}
                                            class="peer custom-checkbox hidden filter-checkbox">
                                        <div
                                            class="w-4 h-4 border border-gray-300 rounded mr-3 flex items-center justify-center bg-white transition peer-checked:bg-primary peer-checked:border-primary text-transparent peer-checked:text-white ">
                                            <i class="fa fa-check text-[10px]"></i>
                                        </div>
                                        {{ $brand->name }} ({{ $brand->products_count }})
                                    </label>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    {{-- category --}}
                    <div class="bg-gray-50 p-6 rounded-lg border">
                        <h4 class="font-bold text-lg mb-4">Categories</h4>
                        <ul class="space-y-3">
                            @foreach ($categories as $category)
                                <li class="flex items-center">
                                    <label class="flex items-center cursor-pointer hover:text-primary">
                                        <input name="category[]" type="checkbox" value="{{ $category->id }}"
                                            {{ in_array($category->id, request('category', [])) ? 'checked' : '' }}
                                            class="peer custom-checkbox hidden filter-checkbox">
                                        <div
                                            class="w-4 h-4 border border-gray-300 rounded mr-3 flex items-center justify-center bg-white transition peer-checked:bg-primary peer-checked:border-primary text-transparent peer-checked:text-white ">
                                            <i class="fa fa-check text-[10px]"></i>
                                        </div>
                                        {{ $category->name }} ({{ $category->products_count }})
                                    </label>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    {{-- price --}}
                    <div class="bg-gray-50 p-6 rounded-lg border">
                        <h4 class="font-bold text-lg mb-4">Filter By Price</h4>
                        <div class="relative pt-6 pb-2">
                            {{-- background track --}}
                            <div class="absolute w-full h-1 bg-gray-300 rounded top-6 z0"></div>
                            {{-- active colored track --}}
                            <div id="range-tracker" class="absolute h-1 bg-primary rounded top-6 z-10"
                                style="left: 0%; right:0%;"></div>
                            {{-- min price --}}
                            <input type="range" name="min_price" min="0" max="1000"
                                value="{{ request('min_price', 0) }}"
                                class="absolute w-full h-1 bg-transparent appearance-none top-6 left-0 pointer-events-none z-20 custom-range-slider price-slider"
                                id="range-min">
                            {{-- max price --}}
                            <input type="range" name="max_price" min="0" max="1000"
                                value="{{ request('max_price', 1000) }}"
                                class="absolute w-full h-1 bg-transparent appearance-none top-6 left-0 pointer-events-none z-20 custom-range-slider price-slider "
                                id="range-max">
                            {{-- price display --}}
                            <div class="flex justify-between mt-4 text-sm font-medium text-gray-600">
                                <span>$<span id="price-min-display">{{ request('min_price', 0) }}</span></span>
                                <span>$<span id="price-max-display">{{ request('max_price', 1000) }}</span></span>
                            </div>
                        </div>
                    </div>
                    {{-- color --}}
                    <div class="bg-gray-50 p-6 rounded-lg border">
                        <h4 class="font-bold text-lg mb-4">Filter By Color</h4>
                        <ul class="space-y-3">
                            <li class="flex items-center">
                                <input type="checkbox" id="c1" class="hidden peer">
                                <label for="c1"
                                    class="flex items-center cursor-pointer hover:text-primary peer-checked:text-primary group">
                                    <span
                                        class="w-4 h-4 rounded-full bg-blue-500 mr-3 border border-gray-200 group-hover:shadow-md"></span>
                                    Blue
                                </label>
                            </li>
                            <li class="flex items-center">
                                <input type="checkbox" id="c2" class="hidden peer">
                                <label for="c2"
                                    class="flex items-center cursor-pointer hover:text-primary peer-checked:text-primary group">
                                    <span
                                        class="w-4 h-4 rounded-full bg-green-500 mr-3 border border-gray-200 group-hover:shadow-md"></span>
                                    Green
                                </label>
                            </li>
                            <li class="flex items-center">
                                <input type="checkbox" id="c3" class="hidden peer">
                                <label for="c3"
                                    class="flex items-center cursor-pointer hover:text-primary peer-checked:text-primary group">
                                    <span
                                        class="w-4 h-4 rounded-full bg-gray-500 mr-3 border border-gray-200 group-hover:shadow-md"></span>
                                    Gray
                                </label>
                            </li>
                            <li class="flex items-center">
                                <input type="checkbox" id="c4" class="hidden peer">
                                <label for="c4"
                                    class="flex items-center cursor-pointer hover:text-primary peer-checked:text-primary group">
                                    <span
                                        class="w-4 h-4 rounded-full bg-black mr-3 border border-gray-200 group-hover:shadow-md"></span>
                                    Black
                                </label>
                            </li>
                        </ul>
                    </div>
                    {{-- tag --}}
                    <div class="bg-gray-50 p-6 rounded-lg border">
                        <h4 class="font-bold text-lg mb-4">Tags</h4>
                        <div class="flex flex-wrap gap-2">
                            <a href="#"
                                class="px-3 py-1 bg-white border rounded text-sm hover:bg-primary hover:text-white transition">Clothing</a>
                            <a href="#"
                                class="px-3 py-1 bg-white border rounded text-sm hover:bg-primary hover:text-white transition">Furniture</a>
                            <a href="#"
                                class="px-3 py-1 bg-white border rounded text-sm hover:bg-primary hover:text-white transition">Lights</a>
                            <a href="#"
                                class="px-3 py-1 bg-white border rounded text-sm hover:bg-primary hover:text-white transition">Modern</a>
                        </div>
                    </div>

                </aside>

                <div class="w-full lg:w-3/4 order-1 lg:order-2">

                    <div
                        class="flex flex-col sm:flex-row justify-between items-center bg-white border p-4 rounded mb-8 shadow-sm">
                        <p class="text-sm mb-4 sm:mb-0">
                            Showing
                            <span class="font-bold text-primary">
                                {{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }}
                            </span>
                            of
                            <span class="font-bold">{{ $products->total() }}</span>
                            Results
                        </p>

                        <div class="flex items-center space-x-6">
                            <div class="flex space-x-2">
                                <button id="btn-grid"
                                    class="w-8 h-8 flex items-center justify-center rounded bg-primary text-white transition">
                                    <i class="fa fa-th"></i>
                                </button>
                                <button id="btn-list"
                                    class="w-8 h-8 flex items-center justify-center rounded bg-gray-200 hover:bg-primary hover:text-white transition">
                                    <i class="fa fa-list"></i>
                                </button>
                            </div>

                            {{-- sort by --}}
                            <div class="flex items-center">
                                <span class="mr-2 text-sm font-medium">Sort By:</span>
                                <select name="sort_by"
                                    class="border rounded p-1 text-sm focus:outline-none focus:border-primary auto-submit">
                                    <option value="newest" {{ request('sort_by') == 'newest' ? 'selected' : '' }}>
                                        Newest
                                    </option>
                                    <option value="price_asc"
                                        {{ request('sort_by') == 'price_asc' ? 'selected' : '' }}>
                                        Price: Low to High</option>
                                    <option value="price_desc"
                                        {{ request('sort_by') == 'price_desc' ? 'selected' : '' }}>
                                        Price: High to Low</option>
                                    <option value="featured" {{ request('sort_by') == 'featured' ? 'selected' : '' }}>
                                        Featured</option>

                                </select>
                            </div>
                            {{-- view --}}
                            <div class="flex items-center">
                                <span class="mr-2 text-sm font-medium">View:</span>
                                <select name="per_page"
                                    class="border rounded p-2 text-sm focus:outline-none focus:border-primary auto-submit">
                                    <option value="12" {{ request('per_page') == '12' ? 'selected' : '' }}>12
                                        Products
                                    </option>
                                    <option value="24" {{ request('per_page') == '24' ? 'selected' : '' }}>24
                                        Products
                                    </option>
                                    <option value="48" {{ request('per_page') == '48' ? 'selected' : '' }}>48
                                        Products
                                    </option>

                                </select>
                            </div>
                        </div>
                    </div>

                    <div id="product-grid-view" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                        {{-- show products --}}
                        @foreach ($products as $product)
                            <div class="group">
                                <div class="relative overflow-hidden bg-gray-100 rounded-lg mb-4">
                                    {{-- img --}}
                                    <a href="{{ route('shop.products.show', $product->slug) }}">
                                        <img src="{{ asset('uploads/products/thumbnails/' . $product->image) }}"
                                            alt="{{ $product->name }}"
                                            class="w-full h-[300px] object-cover transition duration-500 group-hover:scale-105" />
                                    </a>
                                    <div
                                        class="absolute bottom-4 left-0 right-0 flex justify-center space-x-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                        {{-- whishlist --}}
                                        <button
                                            class="w-10 h-10 bg-white rounded-full shadow hover:bg-primary hover:text-white flex items-center justify-center transition">
                                            <i class="fa-regular fa-heart"></i>
                                        </button>
                                        {{-- bag --}}
                                        <button
                                            class="w-10 h-10 bg-white rounded-full shadow hover:bg-primary hover:text-white flex items-center justify-center transition">
                                            <i class="fa-solid fa-bag-shopping"></i>
                                        </button>
                                        {{-- search --}}
                                        <button
                                            class="w-10 h-10 bg-white rounded-full shadow hover:bg-primary hover:text-white flex items-center justify-center transition">
                                            <i class="fa-solid fa-magnifying-glass"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="text-center">
                                    {{-- name --}}
                                    <h4 class="text-lg font-medium hover:text-primary">
                                        <a
                                            href="{{ route('shop.products.show', $product->slug) }}">{{ $product->name }}</a>
                                    </h4>
                                    {{-- price container --}}
                                    <div class="mt-1">
                                        <div class="flex items-center justify-center space-x-4">
                                            @if ($product->sale_price && $product->sale_price < $product->regular_price)
                                                <span class="text-xl text-gray-400 line-through">
                                                    ${{ number_format($product->regular_price, 2) }}
                                                </span>
                                                <span class="text-2xl text-primary font-bold">
                                                    ${{ number_format($product->sale_price, 2) }}
                                                </span>
                                            @else
                                                <span class="text-2xl text-primary font-bold">
                                                    ${{ number_format($product->regular_price, 2) }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div id="product-list-view" class="flex flex-col space-y-8 hidden">
                        <div
                            class="flex flex-col md:flex-row gap-6 bg-white border rounded-lg p-4 hover:shadow-lg transition">
                            <div class="w-full md:w-1/3 relative bg-gray-100 rounded overflow-hidden">
                                <a href="details.php"><img src="assets/images/product/product-01.jpg" alt="Product"
                                        class="w-full h-full object-cover"></a>
                            </div>
                            <div class="w-full md:w-2/3 flex flex-col justify-center">
                                <h4 class="text-xl font-bold hover:text-primary mb-2"><a href="details.php">Elona
                                        bedside
                                        grey table</a></h4>
                                <p class="text-primary font-bold text-lg mb-4">$40.00</p>
                                <p class="text-gray-600 mb-6 text-sm leading-relaxed">
                                    Block out the haters with the fresh adidas® Originals Kaval Windbreaker Jacket. Part
                                    of
                                    the Kaval Collection. Regular fit is eased, but not sloppy, and perfect for any
                                    activity.
                                </p>
                                <div class="flex space-x-2">
                                    <button
                                        class="px-4 py-2 border rounded hover:bg-primary hover:text-white transition"><i
                                            class="fa-regular fa-heart"></i></button>
                                    <button
                                        class="px-4 py-2 border rounded hover:bg-primary hover:text-white transition"><i
                                            class="fa-solid fa-bag-shopping"></i> Add to Cart</button>
                                    <button
                                        class="px-4 py-2 border rounded hover:bg-primary hover:text-white transition"><i
                                            class="fa-solid fa-magnifying-glass"></i></button>
                                </div>
                            </div>
                        </div>

                        <div
                            class="flex flex-col md:flex-row gap-6 bg-white border rounded-lg p-4 hover:shadow-lg transition">
                            <div class="w-full md:w-1/3 relative bg-gray-100 rounded overflow-hidden">
                                <a href="details.php"><img src="assets/images/product/product-02.jpg" alt="Product"
                                        class="w-full h-full object-cover"></a>
                            </div>
                            <div class="w-full md:w-2/3 flex flex-col justify-center">
                                <h4 class="text-xl font-bold hover:text-primary mb-2"><a href="details.php">Simple
                                        Minimal
                                        Chair</a></h4>
                                <p class="text-primary font-bold text-lg mb-4">$240.00</p>
                                <p class="text-gray-600 mb-6 text-sm leading-relaxed">
                                    Block out the haters with the fresh adidas® Originals Kaval Windbreaker Jacket. Part
                                    of
                                    the Kaval Collection. Regular fit is eased, but not sloppy, and perfect for any
                                    activity.
                                </p>
                                <div class="flex space-x-2">
                                    <button
                                        class="px-4 py-2 border rounded hover:bg-primary hover:text-white transition"><i
                                            class="fa-regular fa-heart"></i></button>
                                    <button
                                        class="px-4 py-2 border rounded hover:bg-primary hover:text-white transition"><i
                                            class="fa-solid fa-bag-shopping"></i> Add to Cart</button>
                                    <button
                                        class="px-4 py-2 border rounded hover:bg-primary hover:text-white transition"><i
                                            class="fa-solid fa-magnifying-glass"></i></button>
                                </div>
                            </div>
                        </div>

                        <div
                            class="flex flex-col md:flex-row gap-6 bg-white border rounded-lg p-4 hover:shadow-lg transition">
                            <div class="w-full md:w-1/3 relative bg-gray-100 rounded overflow-hidden">
                                <a href="details.php"><img src="assets/images/product/product-03.jpg" alt="Product"
                                        class="w-full h-full object-cover"></a>
                            </div>
                            <div class="w-full md:w-2/3 flex flex-col justify-center">
                                <h4 class="text-xl font-bold hover:text-primary mb-2"><a href="details.php">Pendant
                                        Chandelier Light</a></h4>
                                <p class="text-primary font-bold text-lg mb-4">$40.00</p>
                                <p class="text-gray-600 mb-6 text-sm leading-relaxed">
                                    Block out the haters with the fresh adidas® Originals Kaval Windbreaker Jacket. Part
                                    of
                                    the Kaval Collection. Regular fit is eased, but not sloppy, and perfect for any
                                    activity.
                                </p>
                                <div class="flex space-x-2">
                                    <button
                                        class="px-4 py-2 border rounded hover:bg-primary hover:text-white transition"><i
                                            class="fa-regular fa-heart"></i></button>
                                    <button
                                        class="px-4 py-2 border rounded hover:bg-primary hover:text-white transition"><i
                                            class="fa-solid fa-bag-shopping"></i> Add to Cart</button>
                                    <button
                                        class="px-4 py-2 border rounded hover:bg-primary hover:text-white transition"><i
                                            class="fa-solid fa-magnifying-glass"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- paginatiom --}}
                    <div class="mt-12 flex justify-center">
                        {{ $products->links() }}
                    </div>

                </div>
            </div>
        </form>
    </div>

    <!-- Main Content End -->

    {{-- form filter --}}
    @include('partials.scripts.shop-filters ')
</x-app-layout>
