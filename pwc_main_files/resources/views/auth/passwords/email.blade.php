@extends('layouts.app')

@section('content')

<section class="login_pg_sec">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6">
                <div class=" login_pg_img_sec">
                    <!--begin::Aside-->
                    <div class="">
                        <img src="{{ asset('website') }}/assets/images/service_img1.png ">
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="sign_in_fields">
                    <!--begin::Card-->
                    <div class="bg-body  custom_background">
                        <!--begin::Wrapper-->
                        <div class="login_form_upper_wrapper">
                            <form class="form" novalidate="novalidate" method="POST" action="{{ route('password.email') }}">
                            @csrf
                            <!--begin::Heading-->
                                <div class="sign_in_heading">
                                    <h3>Reset Password</h3>
                                </div>
                                <!--begin::Heading-->


                                <!--begin::Input group=-->
                                <div class="input_filed_wrapper_sign form-floating">
                                    <!--begin::Email-->
                                    <input id="email" type="email" placeholder="" class="form-control bg-transparent @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                                    <label for="email">Email*</label>
                                    @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                                <!--end::Email-->
                                </div>
                                <!--end::Input group=-->
                                <!--begin::Submit button-->
                                <div class="btn_wrapper btn_wrapper_reset_pass_pg">
                                    <button type="submit" id="kt_sign_in_submit" class="btn_global">
                                        <!--begin::Indicator label-->
                                        <span class="indicator-label">Send</span>
                                        <div class="btn_img_icon">--}}
                                            <img src="{{ asset('website') }}/assets/images/arrow-right.svg ">--}}
                                        </div>
                                        <!--end::Indicator label-->
                                        <!--begin::Indicator progress-->
                                        <span class="indicator-progress">Please wait...
                                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                        <!--end::Indicator progress-->
                                    </button>
                                </div>
                                <!--end::Submit button-->
                                <a href="{{ route('login') }}" class="link-primary" style="display:block;text-align:center;margin-top:18px;font-size:14px;">Back to Sign In</a>
                            </form>
                            <!--end::Form-->
                        </div>
                        <!--end::Wrapper-->

                    </div>
                    <!--end::Card-->
                </div>
            </div>
        </div>
    </div>
</section>


    <!--end::Body-->

@endsection
