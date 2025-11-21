<div class="seller-phone text-center">
    <p>{{ __('Phone') }}</p>
    <span type="text" id="default_phone_number_show" class="number"  dir="ltr">{{ __('+965 XXX XXX XX') }}</span>
    @if($listing->phone_hidden === 0)
         <div class="number" id="phoneNumber" dir="ltr">{{ $listing->phone }}</div>
        <a href="#" class="show-number" id="userPhoneNumberBtn">{{ get_static_option('listing_show_phone_number_title') ?? __('Show Number') }}</a>
    @endif
</div>
