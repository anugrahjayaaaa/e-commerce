<x-admin-layout>
    <!-- Main Content Start -->

    <main class="flex-1 overflow-y-auto p-6 bg-gray-100">

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Add New Product</h1>
            <a href="{{ route('admin.products.index') }}"
                class="border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 px-5 py-2.5 rounded-lg text-sm font-medium transition flex items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i> Back to Products
            </a>
        </div>

        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                        <h3 class="font-bold text-gray-800 mb-4 border-b pb-2">Basic Information</h3>
                        <div class="space-y-4">
                            {{-- name --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
                                <input type="text" id="name" name="name" value="{{ old('name') }}"
                                    placeholder="e.g. Modern Sofa"
                                    class="w-full border px-4 py-2 rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm"
                                    required>
                                @error('name')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            {{-- slug --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                                <input type="text" name="slug" id="slug" name="slug"
                                    value="{{ old('slug') }}" placeholder="e.g. modern-sofa"
                                    class="w-full border px-4 py-2 rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm bg-gray-50">
                                @error('slug')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            {{-- short description --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Short Description</label>
                                <textarea id="short_description" name="short_description" rows="3" placeholder="Brief summary..."
                                    class="w-full border px-4 py-2 rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm">{{ old('short_description') }}</textarea>
                                @error('short_description')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            {{-- Informatiorn --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Informatiorn</label>
                                <textarea id="information" name="information" rows="3" placeholder="Information..."
                                    class="w-full border px-4 py-2 rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm">{{ old('information ') }}</textarea>
                                @error('information')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            {{-- descrpition --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <textarea id="description" name="description" rows="18" placeholder="Detailed description..."
                                    class="w-full border px-4 py-2 rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm">{{ old('description') }}</textarea>
                                @error('description')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    {{-- price and inventory --}}
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                        <h3 class="font-bold text-gray-800 mb-4 border-b pb-2">Pricing & Inventory</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- regular price --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Regular Price ($)</label>
                                <input type="number" id="regular_price" name="regular_price"
                                    value="{{ old('regular_price') }}" placeholder="0.00"
                                    class="w-full border px-4 py-2 rounded-lg focus:outline-none focus:border-primary text-sm">
                                @error('regular_price')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            {{-- sale price --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Sale Price ($)</label>
                                <input type="number" id="sale_price" value="{{ old('sale_price') }}" name="sale_price"
                                    placeholder="0.00"
                                    class="w-full border px-4 py-2 rounded-lg focus:outline-none focus:border-primary text-sm">
                                @error('sale_price')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            {{-- SKU --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                                <input type="text" id="SKU" name="SKU" value="{{ old('SKU') }}"
                                    placeholder="Product SKU"
                                    class="w-full border px-4 py-2 rounded-lg focus:outline-none focus:border-primary text-sm">
                                @error('SKU')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            {{-- stock status --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Stock Status</label>
                                <select id="stock_status" name="stock_status" value=" {{ old('stock_status') }}"
                                    class="w-full border px-4 py-2 rounded-lg focus:outline-none focus:border-primary text-sm bg-white">
                                    <option value="instock" {{ old('stock_status') == 'instock' ? 'selected' : '' }}>In
                                        Stock</option>
                                    <option value="outofstock"
                                        {{ old('stock_status') == 'outofstock' ? 'selected' : '' }}>Out of Stock
                                    </option>
                                </select>
                                @error('stock_status')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            {{-- quantity --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                                <input type="number" id="quantity" name="quantity" value="{{ old('quantity') }}"
                                    placeholder="Total items"
                                    class="w-full border px-4 py-2 rounded-lg focus:outline-none focus:border-primary text-sm">
                                @error('quantity')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                {{-- publish --}}
                <div class="space-y-6">
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                        <h3 class="font-bold text-gray-800 mb-4 border-b pb-2">Publish</h3>
                        <div class="space-y-3">
                            {{-- status --}}
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Status:</span>
                                <select id="status" name="status"
                                    class="border rounded text-sm pl-4 pr-10 py-1 bg-white focus:outline-none">
                                    <option value="0" {{ old('status') ? '' : 'selected' }}>Draft</option>
                                    <option value="1" {{ old('status') ? 'selected ' : '' }}>Published</option>
                                </select>
                                @error('status')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            {{-- featured --}}
                            <div class="flex items-center gap-2 pt-2">
                                <input type="checkbox" id="featured" name="featured" value="1"
                                    {{ old('featured') == '1' || old('featured') === true ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-primary focus:ring-primary">
                                <label for="featured" class="text-sm text-gray-700 cursor-pointer">
                                    This is a featured product
                                </label>
                                @error('featured')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            {{-- save btn --}}
                            <button type="submit"
                                class="w-full bg-primary hover:bg-blue-600 text-white py-2 rounded-lg text-sm font-medium transition mt-4 shadow">
                                Save Product
                            </button>
                        </div>
                    </div>
                    {{-- organization --}}
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                        <h3 class="font-bold text-gray-800 mb-4 border-b pb-2">Organization</h3>
                        <div class="space-y-4">
                            {{-- categories --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                <select id="category_id" name="category_id"
                                    class="w-full border px-4 py-2 rounded-lg focus:outline-none focus:border-primary text-sm bg-white">
                                    <option value="">Select Category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            {{-- brands --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
                                <select id="brand_id" name="brand_id"
                                    class="w-full border px-4 py-2 rounded-lg focus:outline-none focus:border-primary text-sm bg-white">
                                    <option value="">Select Brand</option>
                                    @foreach ($brands as $brand)
                                        <option value="{{ $brand->id }}"
                                            {{ old('brand_id') == $brand->id ? 'selected' : '' }}>
                                            {{ $brand->name }}</option>
                                    @endforeach
                                </select>
                                @error('brand_id')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- main image --}}
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100" data-upload-group="product">
                        <h3 class="font-bold text-gray-800 mb-4 border-b pb-2">Product Image (Main)</h3>

                        <label for="upload-image" id="single-upload-label" data-upload="content"
                            class="block border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:bg-gray-50 transition cursor-pointer">
                            <i class="fa-solid fa-cloud-arrow-up text-3xl text-gray-400 mb-2"></i>
                            <p class="text-sm text-gray-500">Click to upload main image</p>
                            <input type="file" id="upload-image" data-upload="input" name="image"
                                class="hidden" accept="image/png, image/jpeg, image/jpg, image/webp">
                        </label>
                        @error('image')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror

                        <div id="single-preview-container" data-upload="container"
                            class="hidden mt-4 relative h-48 bg-gray-50 rounded-lg border border-gray-200 flex items-center justify-center overflow-hidden group shadow-sm">
                            <img id="single-image-preview" src="" data-upload="preview"
                                class="max-w-full max-h-full object-contain">
                            {{-- remove btn --}}
                            <button type="button" id="remove-single-image" data-upload="remove"
                                class="absolute top-2 right-2 bg-red-500 text-white rounded-md w-7 h-7 flex items-center justify-center text-sm shadow-md hover:bg-red-600 transition focus:outline-none">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </div>

                    {{-- gallery image --}}
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                        <h3 class="font-bold text-gray-800 mb-4 border-b pb-2">Product Gallery Images</h3>

                        <label for="upload-images"
                            class="block border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:bg-gray-50 transition cursor-pointer">
                            <i class="fa-solid fa-cloud-arrow-up text-3xl text-gray-400 mb-2"></i>
                            <p class="text-sm text-gray-500">Click to upload multiple gallery images</p>
                            <input type="file" id="upload-images" name="images[]" class="hidden" multiple
                                accept="image/png, image/jpeg, image/jpg, image/webp">
                        </label>
                        @if ($errors->has('images') || $errors->has('images.*'))
                            <p class="text-red-500 text-sm mt-1">
                                {{ $errors->first('images') ?: $errors->first('images.*') }}
                            </p>
                        @endif

                        <div id="gallery-preview-container" class="grid grid-cols-3 gap-3 mt-4">
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </main>

    <!-- Main Content End -->

    {{-- custom scripts --}}
    @include('admin.partials.scripts.slug-generator')
    @include('admin.partials.scripts.image-upload')
    @include('admin.partials.scripts.images-upload')

</x-admin-layout>
