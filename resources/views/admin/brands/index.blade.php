<x-admin-layout>
    <!-- Main Content Start -->

    <main class="flex-1 overflow-y-auto p-6 bg-gray-100">

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Brands</h1>
                <p class="text-sm text-gray-500">Manage product brands and partners</p>
            </div>
            <a href="{{ route('admin.brands.create') }}"
                class="bg-primary hover:bg-blue-600 text-white px-5 py-2.5 rounded-lg text-sm font-medium transition flex items-center gap-2 shadow-sm">
                <i class="fa-solid fa-plus"></i> Add New Brand
            </a>
        </div>

        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6">
            <form action="{{ route('admin.brands.index') }}" method="GET"
                class="flex flex-col md:flex-row gap-4 justify-between">
                <div class="flex flex-col md:flex-row gap-4 w-full md:w-auto">
                    <div class="relative w-full md:w-64">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <i class="fa-solid fa-search text-gray-400"></i>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}"
                            class="w-full pl-10 pr-4 py-2 border rounded-lg text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                            placeholder="Search brand...">
                    </div>

                    <select name="status"
                        class="w-full md:w-40 border px-3 py-2 rounded-lg text-sm focus:outline-none focus:border-primary bg-white text-gray-600">
                        <option value="">All Status</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                    </select>

                    @if (request('search') || request()->filled('status'))
                        <a href="{{ route('admin.brands.index') }}"
                            class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors duration-200">
                            <i class="fa-solid fa-xmark text-xs"></i>
                            <span>Clear</span>
                        </a>
                    @endif
                </div>
                <button type="submit" class="hidden">Apply</button>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            @if (session('success'))
                <div class="px-6 py-4 bg-green-100 text-green-700 text-sm rounded-tl-xl rounded-tr-xl">
                    {{ session('success') }}
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="w-full text-left whitespace-nowrap">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold">
                        <tr>
                            <th class="px-6 py-4">ID</th>
                            <th class="px-6 py-4">Logo</th>
                            <th class="px-6 py-4">Brand Name</th>
                            <th class="px-6 py-4">Slug</th>
                            <th class="px-6 py-4">Products</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($brands as $brand)
                            <tr class="hover:bg-gray-50 transition">
                                {{-- id --}}
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $brand->id }}</td>
                                {{-- logo --}}
                                <td class="px-6 py-4">
                                    <div
                                        class="w-12 h-12 bg-gray-50 rounded-lg flex items-center justify-center border">
                                        <img src="{{ asset('uploads/brands/thumbnails') }}/{{ $brand->image }}"
                                            class="max-w-[30px] max-h-[30px] object-contain" alt="{{ $brand->name }}"
                                            onerror="this.src='https://placehold.co/40x40?text=B'">
                                    </div>
                                </td>
                                {{-- name --}}
                                <td class="px-6 py-4">
                                    <span class="font-semibold text-gray-800">{{ $brand->name }}</span>
                                </td>
                                {{-- slug --}}
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $brand->slug }}</td>
                                {{-- products --}}
                                <td class="px-6 py-4">
                                    <span class="bg-blue-50 text-blue-700 px-2 py-1 rounded text-xs font-semibold">
                                        0</span>
                                </td>
                                {{-- status --}}
                                <td class="px-6 py-4">
                                    @if ($brand->status)
                                        <span
                                            class="bg-green-100 text-green-700 px-2.5 py-1 rounded-full text-xs font-semibold">
                                            Active
                                        </span>
                                    @else
                                        <span
                                            class="bg-red-100 text-red-700 px-2.5 py-1 rounded-full text-xs font-semibold">
                                            Inactive
                                        </span>
                                    @endif

                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.brands.edit', $brand) }}"
                                            class="w-8 h-8 rounded-full hover:bg-gray-100 text-blue-500 transition flex items-center justify-center"
                                            title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        {{-- delete --}}
                                        <button type="button"
                                            class="w-8 h-8 rounded-full hover:bg-gray-100 text-red-500 transition flex items-center justify-center"
                                            onclick="openDeleteModal('{{ $brand->name }}', '{{ route('admin.brands.destroy', $brand) }}', 'brand')"
                                            title="Delete">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>

                                    </div>
                                </td>
                            </tr>
                        @empty
                            {{-- if $brands empty --}}
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-500">
                                        <i class="fa-solid fa-boxes-stacked text-4xl mb-3 text-gray-300"></i>
                                        <h3 class="text-lg font-medium text-gray-900">Brands not available</h3>
                                        <p class="text-sm mt-1">You haven't added any brands to your store yet.</p>
                                        <a href="{{ route('admin.brands.create') }}"
                                            class="mt-4 text-primary hover:underline text-sm font-medium">
                                            Add your first brand
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>
                {{-- pagination --}}
                {{ $brands->links() }}
            </div>
        </div>
    </main>
    {{-- delete modal --}}
    @include('admin.partials.delete-modal')

    {{-- custom script delete modal --}}
    @include('admin.partials.script-delete-modal')
</x-admin-layout>
