<div>
    <!-- Error Message -->
    @if($errorMessage)
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            {{ $errorMessage }}
        </div>
    @endif

    <!-- Breadcrumb / Step Indicator -->
    <nav class="flex items-center space-x-2 mb-6 text-sm">
        <button wire:click="goToStep(1)"
            class="{{ $currentStep >= 1 ? 'text-blue-600 font-semibold' : 'text-gray-400' }}">
            1. Pilih Event
        </button>
        <span class="text-gray-400">→</span>
        <button wire:click="goToStep(2)" {{ $currentStep < 2 ? 'disabled' : '' }}
            class="{{ $currentStep >= 2 ? 'text-blue-600 font-semibold' : 'text-gray-400' }}">
            2. Pilih Kategori
        </button>
        <span class="text-gray-400">→</span>
        <span class="{{ $currentStep >= 3 ? 'text-blue-600 font-semibold' : 'text-gray-400' }}">
            3. Pilih Sub-Kategori
        </span>
    </nav>

    <!-- Loading State -->
    <div wire:loading class="w-full text-center py-8">
        <span class="text-gray-500 font-medium">Memuat data...</span>
    </div>

    <div wire:loading.remove>
        <!-- Step 1: Pilih Event -->
        @if($currentStep === 1)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($events ?? [] as $event)
                    @php
                        $isClosed = !$event->is_open;
                        $regService = app(\App\Services\RegistrationService::class);
                        $statusLabel = $regService->getRegistrationStatusLabel($event);
                    @endphp
                    <div class="bg-white border rounded-lg p-5 shadow-sm flex flex-col {{ $isClosed ? 'opacity-60 grayscale' : 'hover:shadow-md hover:border-blue-300 transition' }}">
                        <h3 class="text-lg font-bold text-gray-800 mb-1">{{ $event->name }}</h3>
                        <p class="text-sm text-gray-500 mb-3">
                            <i class="fas fa-calendar-alt mr-1"></i> {{ $event->event_date->translatedFormat('d F Y') }}
                        </p>
                        
                        <div class="mb-4 flex-grow">
                            @if($isClosed)
                                <span class="inline-block px-2 py-1 bg-red-100 text-red-700 text-xs font-semibold rounded">
                                    {{ $statusLabel }}
                                </span>
                            @else
                                <span class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded">
                                    {{ $statusLabel }}
                                </span>
                            @endif
                        </div>

                        <button 
                            wire:click="selectEvent({{ $event->id }})" 
                            {{ $isClosed ? 'disabled' : '' }}
                            class="w-full py-2 rounded font-semibold text-white {{ $isClosed ? 'bg-gray-400 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700 transition' }}">
                            {{ $isClosed ? 'Pendaftaran Ditutup' : 'Pilih Event' }}
                        </button>
                    </div>
                @empty
                    <div class="col-span-full p-8 text-center bg-gray-50 rounded-lg border border-dashed border-gray-300">
                        <p class="text-gray-500">Belum ada event pendaftaran yang dibuka saat ini.</p>
                    </div>
                @endforelse
            </div>
        @endif

        <!-- Step 2: Pilih Kategori -->
        @if($currentStep === 2)
            <div class="mb-6">
                <h2 class="text-xl font-bold text-gray-800">Kategori untuk Event: {{ $selectedEventName }}</h2>
                <p class="text-sm text-gray-500">Silakan pilih kategori yang ingin diikuti.</p>
            </div>

            @forelse($categoriesGrouped ?? [] as $type => $categories)
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2">Tipe: {{ $type }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($categories as $category)
                            <div class="bg-white border rounded-lg p-5 shadow-sm hover:shadow-md hover:border-blue-300 transition">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-bold text-gray-800 text-lg">{{ $category->class_name }}</h4>
                                    <span class="px-2 py-1 text-xs font-bold rounded {{ $type === 'Open' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }}">
                                        {{ $type }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 mb-2">
                                    <i class="fas fa-child mr-1"></i> {{ $category->readableBirthRange() }}
                                </p>
                                <p class="text-sm font-medium text-gray-500 mb-4">
                                    {{ $category->subCategories->count() }} sub-kategori tersedia
                                </p>
                                <button 
                                    wire:click="selectCategory({{ $category->id }})" 
                                    class="w-full py-2 border border-blue-600 text-blue-600 hover:bg-blue-50 hover:border-blue-700 font-semibold rounded text-sm transition">
                                    Lihat Sub-Kategori
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="p-8 text-center bg-gray-50 rounded-lg border border-dashed border-gray-300">
                    <p class="text-gray-500">Tidak ada kategori untuk event ini.</p>
                </div>
            @endforelse
        @endif

        <!-- Step 3: Pilih Sub Kategori -->
        @if($currentStep === 3)
            <div class="mb-6">
                <h2 class="text-xl font-bold text-gray-800">Sub-Kategori: {{ $selectedCategoryName }}</h2>
                <p class="text-sm text-gray-500">Pilih sub-kategori pertandingan untuk lanjut ke pengisian data peserta.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @forelse($subCategories ?? [] as $subCategory)
                    <div class="bg-white border rounded-lg p-5 shadow-sm flex flex-col hover:border-blue-400 hover:shadow-md transition cursor-pointer" wire:click="selectSubCategory({{ $subCategory->id }})">
                        <div class="flex justify-between items-start mb-3">
                            <h4 class="font-bold text-gray-800 text-lg">{{ $subCategory->name }}</h4>
                            @php
                                $genderClass = match($subCategory->gender->value) {
                                    'M' => 'bg-blue-100 text-blue-700',
                                    'F' => 'bg-pink-100 text-pink-700',
                                    default => 'bg-purple-100 text-purple-700',
                                };
                                $genderLabel = match($subCategory->gender->value) {
                                    'M' => 'Putra',
                                    'F' => 'Putri',
                                    default => 'Campuran',
                                };
                            @endphp
                            <span class="px-2 py-1 text-xs font-bold rounded {{ $genderClass }}">
                                {{ $genderLabel }}
                            </span>
                        </div>
                        
                        <div class="mb-5 flex-grow">
                            <p class="text-xl font-bold text-gray-900 mb-3">
                                Rp {{ number_format($subCategory->price, 0, ',', '.') }}
                            </p>
                            @if($subCategory->isTeam())
                                <span class="inline-flex items-center px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-medium rounded">
                                    <i class="fas fa-users mr-1"></i> Beregu (min {{ $subCategory->min_participants }}, max {{ $subCategory->max_participants }})
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 bg-gray-100 text-gray-800 text-xs font-medium rounded">
                                    <i class="fas fa-user mr-1"></i> Individu
                                </span>
                            @endif
                        </div>

                        <button class="w-full py-2 bg-blue-50 border border-transparent text-blue-700 hover:bg-blue-600 hover:text-white font-semibold rounded transition text-sm">
                            Daftar Kelas Ini
                        </button>
                    </div>
                @empty
                    <div class="col-span-full p-8 text-center bg-gray-50 rounded-lg border border-dashed border-gray-300">
                        <p class="text-gray-500">Tidak ada sub-kategori yang tersedia.</p>
                    </div>
                @endforelse
            </div>
        @endif
    </div>
</div>
