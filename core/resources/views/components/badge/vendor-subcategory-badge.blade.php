@if($vendor && $vendor->isVendor() && $vendor->vendor_subcategory)
    <span class="badge bg-primary vendor-subcategory-badge" title="{{ $vendor->getVendorSubcategoryLabel() }}">
        @if($vendor->vendor_subcategory === 'veterinarian')
            <i class="las la-stethoscope"></i>
        @elseif($vendor->vendor_subcategory === 'goods')
            <i class="las la-box"></i>
        @elseif($vendor->vendor_subcategory === 'services')
            <i class="las la-tools"></i>
        @endif
        {{ $vendor->getVendorSubcategoryLabel() }}
    </span>
@endif
