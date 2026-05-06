<div>
    <!-- Error Message -->
    @if($errorMessage)
        @include('partials.error-alert', ['message' => $errorMessage, 'title' => 'Terjadi Kesalahan'])
    @endif

    @if (session('success'))
        <div class="alert alert-success d-flex align-items-center p-5 mb-6">
            <i class="fas fa-check-circle fs-2hx text-success me-4"></i>
            <div class="d-flex flex-column">
                <h4 class="mb-1 text-success">Berhasil</h4>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <div class="d-flex flex-column flex-xl-row">
        <!-- Stepper Panel (Left Sidebar) -->
        <div class="flex-column flex-lg-row-auto w-100 w-xl-350px mb-10 mb-xl-0">
            <div class="card shadow-sm stepper-panel">
                <div class="card-body p-8 p-lg-10">
                    <h3 class="fw-bolder text-dark mb-8">Tahapan Pendaftaran</h3>

                    <div class="custom-stepper">
                        <!-- Step 1 -->
                        <div class="custom-stepper-item {{ $currentStep === 1 ? 'active' : ($currentStep > 1 ? 'completed' : '') }}" wire:click="goToStep(1)" style="cursor: pointer;">
                            <div class="custom-stepper-circle">
                                @if($currentStep > 1)
                                    <i class="fas fa-check"></i>
                                @else
                                    <span>1</span>
                                @endif
                            </div>
                            <div class="custom-stepper-content">
                                <h4 class="custom-stepper-title">Event</h4>
                                <p class="custom-stepper-desc">Pilih Event</p>
                            </div>
                        </div>

                        <!-- Step 2 -->
                        <div class="custom-stepper-item {{ $currentStep === 2 ? 'active' : ($currentStep > 2 ? 'completed' : '') }}" @if($currentStep >= 2) wire:click="goToStep(2)" style="cursor: pointer;" @endif>
                            <div class="custom-stepper-circle">
                                @if($currentStep > 2)
                                    <i class="fas fa-check"></i>
                                @else
                                    <span>2</span>
                                @endif
                            </div>
                            <div class="custom-stepper-content">
                                <h4 class="custom-stepper-title">Kategori</h4>
                                <p class="custom-stepper-desc">Pilih Kategori</p>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div class="custom-stepper-item {{ $currentStep === 3 ? 'active' : '' }}">
                            <div class="custom-stepper-circle">
                                <span>3</span>
                            </div>
                            <div class="custom-stepper-content">
                                <h4 class="custom-stepper-title">Sub-Kategori</h4>
                                <p class="custom-stepper-desc">Pilih Kelas</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Panel (Right Side) -->
        <div class="flex-lg-row-fluid ms-xl-10">
            <!-- Loading State -->
            <div wire:loading class="w-100 text-center py-10">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Memuat data...</span>
                </div>
            </div>

            <div wire:loading.remove>
                <!-- Step 1: Pilih Event -->
                @if($currentStep === 1)
                    <div class="mb-7">
                        <h2 class="fw-bolder text-dark">Pilih Event Pendaftaran</h2>
                        <div class="text-muted fw-bold fs-6 mt-2">Silakan pilih event yang sedang membuka pendaftaran.</div>
                    </div>
                    <div class="row g-6">
                        @forelse($events ?? [] as $event)
                            @php
                                $isClosed = !$event->is_open;
                                $statusLabel = $statusLabels[$event->id] ?? '';
                            @endphp
                            <div class="col-md-6">
                                <div class="card card-flush shadow-sm h-100 {{ $isClosed ? 'opacity-50' : 'hover-elevate-up' }}">
                                    <div class="card-body p-6 d-flex flex-column h-100">
                                        <div class="mb-5">
                                            <div class="d-flex flex-stack mb-3">
                                                @if($isClosed)
                                                    <span class="badge badge-light-danger fw-bolder">{{ $statusLabel }}</span>
                                                @else
                                                    <span class="badge badge-light-success fw-bolder">{{ $statusLabel }}</span>
                                                @endif
                                            </div>
                                            <span class="text-dark fw-bolder fs-4">{{ $event->name }}</span>
                                            <div class="text-muted fw-bold mt-2">
                                                <i class="fas fa-calendar-alt me-2 text-muted"></i> {{ $event->event_date->translatedFormat('d F Y') }}
                                            </div>
                                        </div>
                                        <div class="mt-auto">
                                            <button
                                                wire:click="selectEvent({{ $event->id }})"
                                                {{ $isClosed ? 'disabled' : '' }}
                                                class="btn w-100 {{ $isClosed ? 'btn-light' : 'btn-primary' }}">
                                                {{ $isClosed ? 'Pendaftaran Ditutup' : 'Pilih Event' }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-6">
                                    <i class="fas fa-info-circle fs-2tx text-warning me-4"></i>
                                    <div class="d-flex flex-stack flex-grow-1">
                                        <div class="fw-semibold">
                                            <h4 class="text-gray-900 fw-bold">Perhatian</h4>
                                            <div class="fs-6 text-gray-700">Belum ada event pendaftaran yang dibuka saat ini.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforelse
                    </div>
                @endif

                <!-- Step 2: Pilih Kategori -->
                @if($currentStep === 2)
                    <div class="mb-7">
                        <h2 class="fw-bolder text-dark">Kategori Event: <span class="text-primary">{{ $selectedEventName }}</span></h2>
                        <div class="text-muted fw-bold fs-6 mt-2">Silakan pilih kategori yang ingin diikuti.</div>
                    </div>

                    @forelse($categoriesGrouped ?? [] as $type => $categories)
                        <div class="mb-10">
                            <h3 class="text-gray-700 fw-bolder fs-4 mb-7 border-bottom border-2 border-gray-200 pb-4">Tipe: {{ $type }}</h3>
                            <div class="row g-6">
                                @foreach($categories as $category)
                                    <div class="col-md-6">
                                        <div class="card shadow-sm border border-dashed hover-elevate-up h-100">
                                            <div class="card-body p-6">
                                                <div class="d-flex justify-content-between align-items-start mb-4">
                                                    <h4 class="fw-bolder text-dark fs-5">{{ $category->class_name }}</h4>
                                                    <span class="badge {{ $type === 'Open' ? 'badge-light-success' : 'badge-light-primary' }} fw-bolder">
                                                        {{ $type }}
                                                    </span>
                                                </div>
                                                <div class="d-flex align-items-center mb-4 text-muted fw-bold fs-6">
                                                    <i class="fas fa-child me-2 text-primary"></i> {{ $category->readableBirthRange() }}
                                                </div>
                                                <div class="d-flex align-items-center mb-6 text-muted fw-bold fs-6">
                                                    <i class="fas fa-layer-group me-2 text-info"></i> {{ $category->subCategories->count() }} sub-kategori tersedia
                                                </div>
                                                <button 
                                                    wire:click="selectCategory({{ $category->id }})" 
                                                    class="btn btn-outline btn-outline-dashed btn-outline-primary btn-active-light-primary w-100 fw-bolder">
                                                    Lihat Sub-Kategori
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="notice d-flex bg-light-info rounded border-info border border-dashed p-6">
                            <i class="fas fa-folder-open fs-2tx text-info me-4"></i>
                            <div class="fw-semibold">
                                <h4 class="text-gray-900 fw-bold">Kosong</h4>
                                <div class="fs-6 text-gray-700">Tidak ada kategori untuk event ini.</div>
                            </div>
                        </div>
                    @endforelse
                @endif

                <!-- Step 3: Pilih Sub Kategori -->
                @if($currentStep === 3)
                    <div class="mb-7">
                        <h2 class="fw-bolder text-dark">Sub-Kategori: <span class="text-primary">{{ $selectedCategoryName }}</span></h2>
                        <div class="text-muted fw-bold fs-6 mt-2">Pilih sub-kategori pertandingan untuk lanjut ke pengisian data peserta.</div>
                    </div>

                    @if(($draftSelections ?? collect())->count() > 0)
                        <div class="card shadow-sm mb-7">
                            <div class="card-header">
                                <h3 class="card-title fw-bolder">
                                    <i class="fas fa-list-check text-success me-2"></i>
                                    Ringkasan Draft Atlet
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="d-flex flex-column gap-4">
                                    @foreach($draftSelections as $draft)
                                        <div class="d-flex flex-stack">
                                            <div>
                                                <div class="fw-bolder">{{ $draft['subCategory']->name }}</div>
                                                <div class="text-muted fs-7">{{ $draft['subCategory']->eventCategory->class_name }}</div>
                                            </div>
                                            <span class="badge badge-light-success fw-bolder">
                                                @if (isset($draft['team_count']))
                                                    {{ $draft['team_count'] }} Tim ({{ $draft['athlete_count'] }} Atlet)
                                                @else
                                                    {{ $draft['athlete_count'] }} Atlet
                                                @endif
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="row g-6">
                        @forelse($subCategories ?? [] as $subCategory)
                            <div class="col-md-6 col-lg-4">
                                <div class="card shadow-sm border border-hover-primary h-100 cursor-pointer" wire:click="selectSubCategory({{ $subCategory->id }})">
                                    <div class="card-body p-6 d-flex flex-column">
                                        <div class="d-flex justify-content-between align-items-start mb-4">
                                            <h4 class="fw-bolder text-dark fs-5">{{ $subCategory->name }}</h4>
                                            @php
                                                $genderClass = match($subCategory->gender->value) {
                                                    'M' => 'badge-light-primary',
                                                    'F' => 'badge-light-danger',
                                                    default => 'badge-light-info',
                                                };
                                                $genderLabel = match($subCategory->gender->value) {
                                                    'M' => 'Putra',
                                                    'F' => 'Putri',
                                                    default => 'Campuran',
                                                };
                                            @endphp
                                            <span class="badge {{ $genderClass }} fw-bolder">{{ $genderLabel }}</span>
                                        </div>
                                        
                                        <div class="flex-grow-1 mb-5">
                                            <div class="fs-2 fw-bolder text-gray-900 mb-3">
                                                Rp {{ number_format($subCategory->price, 0, ',', '.') }}
                                            </div>
                                            @if($subCategory->isTeam())
                                                <div class="d-flex align-items-center text-warning fw-bold fs-7">
                                                    <i class="fas fa-users me-2 text-warning"></i> Beregu ({{ $subCategory->min_participants }} - {{ $subCategory->max_participants }} org)
                                                </div>
                                            @else
                                                <div class="d-flex align-items-center text-gray-600 fw-bold fs-7">
                                                    <i class="fas fa-user me-2 text-gray-600"></i> Individu
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Preview Atlet di draf --}}
                                        @php
                                            $currentDraft = collect($draftSelections)->firstWhere('subCategory.id', $subCategory->id);
                                        @endphp
                                        @if($currentDraft)
                                            <div class="mb-4 bg-light-success p-3 rounded border border-success border-dashed">
                                                <div class="fw-bold text-success fs-8 mb-1">DRAF TERPILIH:</div>
                                                <div class="d-flex flex-wrap gap-1">
                                                    @foreach($currentDraft['athlete_names'] as $name)
                                                        <span class="badge badge-success fs-9 px-2 py-1">{{ $name }}</span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        <button class="btn btn-light-primary w-100 fw-bolder text-hover-white">
                                            Daftar Kelas Ini
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="notice d-flex bg-light-secondary rounded border-gray-300 border border-dashed p-6">
                                    <i class="fas fa-layer-group fs-2tx text-gray-500 me-4"></i>
                                    <div class="fw-semibold">
                                        <h4 class="text-gray-900 fw-bold">Kosong</h4>
                                        <div class="fs-6 text-gray-700">Tidak ada sub-kategori yang tersedia.</div>
                                    </div>
                                </div>
                            </div>
                        @endforelse
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
