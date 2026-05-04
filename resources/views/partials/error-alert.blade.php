@props(['message' => '', 'title' => 'Perhatian'])

<div class="alert alert-danger d-flex align-items-center p-5 mb-6">
    <i class="fas fa-exclamation-triangle fs-2hx text-danger me-4"></i>
    <div class="d-flex flex-column">
        <h4 class="mb-1 text-danger">{{ $title }}</h4>
        <span>{{ $message }}</span>
    </div>
</div>
