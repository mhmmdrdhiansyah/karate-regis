<x-app-layout>
    @section('title', 'Dashboard')

    @if(($role ?? '') === 'super-admin')
        @include('dashboard.components.super-admin')
    @elseif(($role ?? '') === 'panitia')
        @include('dashboard.components.panitia')
    @elseif(($role ?? '') === 'panitia-empty')
        @include('dashboard.components.panitia-empty')
    @else
        @include('dashboard.components.kontingen')
    @endif
</x-app-layout>
