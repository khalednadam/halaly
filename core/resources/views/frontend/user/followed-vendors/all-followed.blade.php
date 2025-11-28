@extends('frontend.layout.master')
@section('site_title')
    {{ __('Followed Vendors') }}
@endsection
@section('style')
   <style>
       .vendor-card {
           border: 1px solid #e5e7eb;
           border-radius: 8px;
           padding: 20px;
           margin-bottom: 20px;
           transition: all 0.3s ease;
       }
       .vendor-card:hover {
           box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
       }
       .vendor-image {
           width: 80px;
           height: 80px;
           border-radius: 50%;
           object-fit: cover;
       }
       .vendor-info {
           flex: 1;
           margin-left: 15px;
       }
       .vendor-name {
           font-size: 18px;
           font-weight: 600;
           margin-bottom: 5px;
       }
       .vendor-location {
           color: #64748B;
           font-size: 14px;
           margin-bottom: 10px;
       }
       .vendor-stats {
           display: flex;
           gap: 20px;
           margin-top: 10px;
       }
       .vendor-stat {
           font-size: 14px;
           color: #64748B;
       }
       .vendor-stat strong {
           color: #1e293b;
       }
       .unfollow-btn {
           margin-top: 10px;
       }
   </style>
@endsection
@section('content')
    <div class="profile-setting favourite-ads section-padding2">
        <div class="container-1920 plr1">
            <div class="row">
                <div class="col-12">
                    <div class="profile-setting-wraper">
                        @include('frontend.user.layout.partials.user-profile-background-image')
                        <div class="down-body-wraper">
                            @include('frontend.user.layout.partials.sidebar')
                            <div class="main-body">
                                <x-frontend.user.responsive-icon/>
                                <div class="relevant-ads all-listings box-shadow1">
                                    <h4 class="dis-title">{{ __('Followed Vendors') }}</h4>
                                    <div class="add-wraper">
                                        @if($followedVendors->count() > 0)
                                            @foreach($followedVendors as $follow)
                                                @php
                                                    $vendor = $follow->following;
                                                    $vendorListingsCount = $vendor->listings()->where('status', 1)->where('is_published', 1)->count();
                                                    $vendorReviewsCount = $vendor->reviews()->count();
                                                    $averageRating = $vendor->reviews()->avg('rating');
                                                @endphp
                                                <div class="vendor-card">
                                                    <div class="d-flex align-items-start">
                                                        <div class="vendor-image-wrapper">
                                                            <a href="{{ route('about.user.profile', $vendor->username) }}">
                                                                @if(!empty($vendor->image))
                                                                    {!! render_image_markup_by_attachment_id($vendor->image, '', 'thumb') !!}
                                                                @else
                                                                    <img src="{{ asset('assets/frontend/img/static/user-no-image.webp') }}" alt="No Image" class="vendor-image">
                                                                @endif
                                                            </a>
                                                        </div>
                                                        <div class="vendor-info">
                                                            <div class="d-flex justify-content-between align-items-start">
                                                                <div>
                                                                    <h4 class="vendor-name">
                                                                        <a href="{{ route('about.user.profile', $vendor->username) }}" class="text-decoration-none">
                                                                            {{ $vendor->fullname }}
                                                                        </a>
                                                                    </h4>
                                                                    @if($vendor->user_country || $vendor->user_state)
                                                                        <div class="vendor-location">
                                                                            <svg width="16" height="17" viewBox="0 0 16 17" fill="none" xmlns="http://www.w3.org/2000/svg" style="display: inline-block; vertical-align: middle;">
                                                                                <path d="M5.99984 7.83332C5.99984 8.36376 6.21055 8.87246 6.58562 9.24754C6.9607 9.62261 7.4694 9.83332 7.99984 9.83332C8.53027 9.83332 9.03898 9.62261 9.41405 9.24754C9.78912 8.87246 9.99984 8.36376 9.99984 7.83332C9.99984 7.30289 9.78912 6.79418 9.41405 6.41911C9.03898 6.04404 8.53027 5.83332 7.99984 5.83332C7.4694 5.83332 6.9607 6.04404 6.58562 6.41911C6.21055 6.79418 5.99984 7.30289 5.99984 7.83332Z" stroke="#64748B" stroke-linecap="round" stroke-linejoin="round"/>
                                                                                <path d="M11.7712 11.6047L8.94251 14.4333C8.6925 14.6831 8.35356 14.8234 8.00017 14.8234C7.64678 14.8234 7.30785 14.6831 7.05784 14.4333L4.22851 11.6047C3.48265 10.8588 2.97473 9.90845 2.76896 8.8739C2.5632 7.83934 2.66883 6.767 3.07251 5.79247C3.47618 4.81795 4.15977 3.98501 5.03683 3.39899C5.91388 2.81297 6.94502 2.50018 7.99984 2.50018C9.05466 2.50018 10.0858 2.81297 10.9629 3.39899C11.8399 3.98501 12.5235 4.81795 12.9272 5.79247C13.3308 6.767 13.4365 7.83934 13.2307 8.8739C13.0249 9.90845 12.517 10.8588 11.7712 11.6047Z" stroke="#64748B" stroke-linecap="round" stroke-linejoin="round"/>
                                                                            </svg>
                                                                            @if($vendor->user_city)
                                                                                {{ $vendor->user_city->city }},
                                                                            @endif
                                                                            @if($vendor->user_state)
                                                                                {{ $vendor->user_state->state }},
                                                                            @endif
                                                                            @if($vendor->user_country)
                                                                                {{ $vendor->user_country->country }}
                                                                            @endif
                                                                        </div>
                                                                    @endif
                                                                    <div class="vendor-stats">
                                                                        <div class="vendor-stat">
                                                                            <strong>{{ $vendorListingsCount }}</strong> {{ __('Listings') }}
                                                                        </div>
                                                                        @if($vendorReviewsCount > 0)
                                                                            <div class="vendor-stat">
                                                                                <strong>{{ number_format($averageRating, 1) }}</strong>
                                                                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg" style="display: inline-block; vertical-align: middle; margin-left: 2px;">
                                                                                    <path d="M7 0L8.5716 5.37836L14 5.37836L9.7143 8.72327L11.2859 14.1216L7 10.7767L2.71411 14.1216L4.28571 8.72327L0 5.37836L5.4284 5.37836L7 0Z" fill="#F59E0B"/>
                                                                                </svg>
                                                                                ({{ $vendorReviewsCount }} {{ __('reviews') }})
                                                                            </div>
                                                                        @endif
                                                                        <div class="vendor-stat">
                                                                            <svg width="14" height="13" viewBox="0 0 14 13" fill="none" xmlns="http://www.w3.org/2000/svg" style="display: inline-block; vertical-align: middle; margin-right: 4px;">
                                                                                <path d="M9 7.83333L7 6.5V3.16667M1 6.5C1 7.28793 1.15519 8.06815 1.45672 8.7961C1.75825 9.52405 2.20021 10.1855 2.75736 10.7426C3.31451 11.2998 3.97595 11.7417 4.7039 12.0433C5.43185 12.3448 6.21207 12.5 7 12.5C7.78793 12.5 8.56815 12.3448 9.2961 12.0433C10.0241 11.7417 10.6855 11.2998 11.2426 10.7426C11.7998 10.1855 12.2417 9.52405 12.5433 8.7961C12.8448 8.06815 13 7.28793 13 6.5C13 5.71207 12.8448 4.93185 12.5433 4.2039C12.2417 3.47595 11.7998 2.81451 11.2426 2.25736C10.6855 1.70021 10.0241 1.25825 9.2961 0.956723C8.56815 0.655195 7.78793 0.5 7 0.5C6.21207 0.5 5.43185 0.655195 4.7039 0.956723C3.97595 1.25825 3.31451 1.70021 2.75736 2.25736C2.20021 2.81451 1.75825 3.47595 1.45672 4.2039C1.15519 4.93185 1 5.71207 1 6.5Z" stroke="#64748B" stroke-linecap="round" stroke-linejoin="round"/>
                                                                            </svg>
                                                                            {{ __('Following since') }} {{ $follow->created_at->diffForHumans() }}
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div>
                                                                    <button type="button" class="btn btn-sm btn-outline-danger unfollow-btn follow-user-btn"
                                                                            data-user-id="{{ $vendor->id }}">
                                                                        <span class="follow-text d-none">{{ __('Follow') }}</span>
                                                                        <span class="following-text">{{ __('Unfollow') }}</span>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                            <div class="blog-pagination">
                                                <div class="custom-pagination mt-4 mt-lg-5">
                                                    {!! $followedVendors->links() !!}
                                                </div>
                                            </div>
                                        @else
                                            <x-pagination.empty-data-placeholder :title="__('No Followed Vendors Yet')"/>
                                        @endif
                                    </div>
                                </div>
                            </div>
                       </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        (function ($) {
            "use strict";
            $(document).ready(function () {
                // Unfollow button functionality
                $(document).on('click', '.unfollow-btn.follow-user-btn', function (event) {
                    event.preventDefault();
                    let button = $(this);
                    let userId = button.data('user-id');
                    let card = button.closest('.vendor-card');

                    $.ajax({
                        url: "{{ route('user.follow.unfollow') }}",
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            user_id: userId
                        },
                        beforeSend: function() {
                            button.prop('disabled', true);
                        },
                        success: function(res) {
                            button.prop('disabled', false);
                            if (res.status === 'unfollow_success') {
                                // Remove the vendor card with animation
                                card.fadeOut(300, function() {
                                    $(this).remove();
                                    // Check if there are no more vendors
                                    if ($('.vendor-card').length === 0) {
                                        location.reload();
                                    }
                                });
                                toastr_success_js(res.message);
                            } else {
                                toastr_error_js(res.message);
                            }
                        },
                        error: function(xhr) {
                            button.prop('disabled', false);
                            let message = xhr.responseJSON?.message || '{{ __('An error occurred. Please try again.') }}';
                            toastr_error_js(message);
                        }
                    });
                });
            });
        })(jQuery);
    </script>
@endsection

