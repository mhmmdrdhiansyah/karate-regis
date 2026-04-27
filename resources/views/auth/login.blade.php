<x-guest-layout>
    <form class="form w-100" novalidate="novalidate" id="kt_sign_in_form" method="POST" action="{{ route('login') }}">
        @csrf

        <div class="text-center mb-10">
            <h1 class="text-dark mb-3">Sign In to Metronic</h1>
            <div class="text-gray-400 fw-bold fs-4">Sign In to your account</div>
        </div>

        <div class="fv-row mb-10">
            <label class="form-label fs-6 fw-bolder text-dark">Username</label>
            <input class="form-control form-control-lg form-control-solid" type="text" name="username"
                value="{{ old('username') }}" autocomplete="off" required autofocus />
            @error('username')
                <div class="text-danger mt-2 small">{{ $message }}</div>
            @enderror
        </div>

        <div class="fv-row mb-10">
            <div class="d-flex flex-stack mb-2">
                <label class="form-label fw-bolder text-dark fs-6 mb-0">Password</label>
            </div>
            <input class="form-control form-control-lg form-control-solid" type="password" name="password"
                autocomplete="off" required />
            @error('password')
                <div class="text-danger mt-2 small">{{ $message }}</div>
            @enderror
        </div>

        <div class="text-center">
            <button type="submit" id="kt_sign_in_submit" class="btn btn-lg btn-primary w-100 mb-5">
                <span class="indicator-label">Continue</span>
                <span class="indicator-progress">Please wait...
                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
            </button>

            {{-- 
            <div class="text-center text-muted text-uppercase fw-bolder mb-5">or</div>
            <a href="#" class="btn btn-flex flex-center btn-light btn-lg w-100 mb-5">
                <img alt="Logo" src="{{ asset('assets/media/svg/brand-logos/google-icon.svg') }}" class="h-20px me-3" />Continue with Google
            </a> 
            --}}
        </div>
    </form>
</x-guest-layout>
