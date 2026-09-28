@extends('layouts.app')

@section('content')
<style>
    .login_pg_sec .reset_pass_sub { color:#4A4A4A; font-size:14px; margin:6px 0 0; }
    .login_pg_sec .sign_in_fields .input_filed_wrapper_sign input[readonly] { background:#F3F4F6 !important; color:#6B7280; cursor:not-allowed; }
    .login_pg_sec .sign_in_fields .input_filed_wrapper_sign { margin-bottom:18px; }
    .login_pg_sec .sign_in_fields .input_filed_wrapper_sign.input_wrapper input { padding-right:50px; }
    .login_pg_sec .sign_in_fields .input_filed_wrapper_sign i.reset_toggle { top:24px; color:#5B5B5B; z-index:5; }
    .login_pg_sec .sign_in_fields .input_filed_wrapper_sign .invalid-feedback { display:block; }
    .login_pg_sec .reset_back_link { display:block; text-align:center; margin-top:18px; font-size:14px; }
</style>

<section class="login_pg_sec">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6">
                <div class="login_pg_img_sec">
                    <div>
                        <img src="{{ asset('website') }}/assets/images/service_img1.png" alt="Paul's Window Cleaning">
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="sign_in_fields">
                    <div class="bg-body custom_background">
                        <div class="login_form_upper_wrapper">
                            <form class="form" method="POST" action="{{ route('password.update') }}">
                                @csrf
                                <input type="hidden" name="token" value="{{ $token }}">

                                <div class="sign_in_heading">
                                    <h3>Reset Password</h3>
                                    <p class="reset_pass_sub">Create a new password for your account.</p>
                                </div>

                                <!--begin::Email-->
                                <div class="input_filed_wrapper_sign form-floating">
                                    <input id="email" type="email" placeholder="" readonly class="form-control @error('email') is-invalid @enderror" name="email" value="{{ $email ?? old('email') }}" required autocomplete="email">
                                    <label for="email">Email Address</label>
                                    @error('email')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>

                                <!--begin::Password-->
                                <div class="input_filed_wrapper_sign form-floating input_wrapper">
                                    <input id="password" type="password" placeholder="" class="form-control bg-transparent reset_pass_input @error('password') is-invalid @enderror" name="password" required autocomplete="new-password" autofocus>
                                    <label for="password">New Password*</label>
                                    <i class="fa-solid fa-eye-slash reset_toggle" role="button" aria-label="Show password"></i>
                                    @error('password')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>

                                <!--begin::Confirm Password-->
                                <div class="input_filed_wrapper_sign form-floating input_wrapper">
                                    <input id="password-confirm" type="password" placeholder="" class="form-control bg-transparent reset_pass_input" name="password_confirmation" required autocomplete="new-password">
                                    <label for="password-confirm">Confirm Password*</label>
                                    <i class="fa-solid fa-eye-slash reset_toggle" role="button" aria-label="Show password"></i>
                                </div>

                                <div class="input_field_belwo_wapper">
                                    <p>Password should be at least 8 characters long, alphanumeric and contain at least one capital letter.</p>
                                </div>

                                <div class="btn_wrapper btn_wrapper_reset_pass_pg">
                                    <button type="submit" id="kt_sign_in_submit" class="btn_global">
                                        <span class="indicator-label">Reset Password</span>
                                        <div class="btn_img_icon">
                                            <img src="{{ asset('website') }}/assets/images/arrow-right.svg" alt="">
                                        </div>
                                    </button>
                                </div>

                                <a href="{{ route('login') }}" class="link-primary reset_back_link">Back to Sign In</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('js')
<script>
    $(document).on('click', '.reset_toggle', function () {
        var input = $(this).siblings('.reset_pass_input');
        var show = input.attr('type') === 'password';
        input.attr('type', show ? 'text' : 'password');
        $(this).toggleClass('fa-eye', show).toggleClass('fa-eye-slash', !show)
               .attr('aria-label', show ? 'Hide password' : 'Show password');
    });
</script>
@endpush
