<x-app-layout>
    @section('title', 'Dashboard')

    @role('super-admin')
        @include('dashboard.components.super-admin')
    @endrole

    @role('panitia')
        @include('dashboard.components.panitia')
    @endrole

    @role('kontingen')
        @include('dashboard.components.kontingen')
    @endrole
</x-app-layout>
