<x-app-layout>
    @section('title', 'Detail User')

    <div class="d-flex flex-column flex-xl-row">

        <div class="flex-column flex-lg-row-auto w-100 w-xl-350px mb-10">
            <div class="card mb-5 mb-xl-8">
                <div class="card-body">
                    <div class="d-flex flex-center flex-column py-5">
                        <div class="symbol symbol-100px symbol-circle mb-7">
                            <div class="symbol-label fs-1 fw-bolder bg-light-primary text-primary">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                        </div>
                        <a href="#"
                            class="fs-3 text-gray-800 text-hover-primary fw-bolder mb-3">{{ $user->name }}</a>
                        <div class="mb-9">
                            @foreach ($user->roles as $role)
                                <div class="badge badge-lg badge-light-primary d-inline">{{ $role->name }}</div>
                            @endforeach
                        </div>
                    </div>
                    <div class="d-flex flex-stack fs-4 py-3">
                        <div class="fw-bolder rotate collapsible" data-bs-toggle="collapse" href="#kt_user_view_details"
                            role="button" aria-expanded="true" aria-controls="kt_user_view_details">
                            Details
                            <span class="ms-2 rotate-180">
                                <span class="svg-icon svg-icon-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none">
                                        <path
                                            d="M11.4343 12.7344L7.25 8.55005C6.83579 8.13583 6.16421 8.13584 5.75 8.55005C5.33579 8.96426 5.33579 9.63583 5.75 10.05L11.2929 15.5929C11.6834 15.9835 12.3166 15.9835 12.7071 15.5929L18.25 10.05C18.6642 9.63584 18.6642 8.96426 18.25 8.55005C17.8358 8.13584 17.1642 8.13584 16.75 8.55005L12.5657 12.7344C12.2533 13.0468 11.7467 13.0468 11.4343 12.7344Z"
                                            fill="black" />
                                    </svg>
                                </span>
                            </span>
                        </div>
                    </div>

                    <div id="kt_user_view_details" class="collapse show">
                        <div class="pb-5 fs-6">
                            <div class="fw-bolder mt-5">Account ID</div>
                            <div class="text-gray-600">ID-{{ $user->id }}</div>

                            <div class="fw-bolder mt-5">Email</div>
                            <div class="text-gray-600">
                                <a href="#" class="text-gray-600 text-hover-primary">{{ $user->email }}</a>
                            </div>

                            <div class="fw-bolder mt-5">Last Login</div>
                            <div class="text-gray-600">Yesterday (Dummy)</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex-lg-row-fluid ms-lg-15">
            <div class="card card-flush mb-6 mb-xl-9">
                <div class="card-header mt-6">
                    <div class="card-title flex-column">
                        <h2 class="mb-1">Profile Overview</h2>
                        <div class="fs-6 fw-bold text-muted">Detail lengkap pengguna</div>
                    </div>
                    <div class="card-toolbar">
                        <button type="button" class="btn btn-light-primary btn-sm">
                            Edit User
                        </button>
                    </div>
                </div>

                <div class="card-body p-9">
                    <div class="row mb-7">
                        <label class="col-lg-4 fw-bold text-muted">Full Name</label>
                        <div class="col-lg-8">
                            <span class="fw-bolder fs-6 text-gray-800">{{ $user->name }}</span>
                        </div>
                    </div>

                    <div class="row mb-7">
                        <label class="col-lg-4 fw-bold text-muted">Contact Email</label>
                        <div class="col-lg-8">
                            <span class="fw-bold text-gray-800 fs-6">{{ $user->email }}</span>
                        </div>
                    </div>

                    <div class="row mb-7">
                        <label class="col-lg-4 fw-bold text-muted">Roles</label>
                        <div class="col-lg-8">
                            @foreach ($user->roles as $role)
                                <span class="fw-bold text-gray-800 fs-6 me-2">{{ $role->name }}</span>
                            @endforeach
                        </div>
                    </div>

                    <div class="row mb-7">
                        <label class="col-lg-4 fw-bold text-muted">Joined Date</label>
                        <div class="col-lg-8">
                            <span class="fw-bold text-gray-800 fs-6">{{ $user->created_at->format('d F Y') }}</span>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
