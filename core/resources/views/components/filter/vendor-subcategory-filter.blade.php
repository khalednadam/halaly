@props(['value' => null])

<div class="filter-vendor-subcategory">
    <label for="vendor_subcategory_filter" class="form-label">{{ __('Filter by Vendor Type') }}</label>
    <select id="vendor_subcategory_filter" name="vendor_subcategory" class="form-select filter-select">
        <option value="">{{ __('All Vendors') }}</option>
        @foreach(get_vendor_subcategories() as $key => $label)
            <option value="{{ $key }}" @if($value === $key) selected @endif>
                {{ $label }}
            </option>
        @endforeach
    </select>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterSelect = document.getElementById('vendor_subcategory_filter');
    if (filterSelect) {
        filterSelect.addEventListener('change', function() {
            // Trigger search with new filter
            if (window.triggerSearch) {
                window.triggerSearch();
            }
        });
    }
});
</script>

<style>
.filter-vendor-subcategory {
    margin-bottom: 1.5rem;
}

.filter-select {
    border: 1px solid #e2e8f0;
    padding: 0.5rem 0.75rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
}

.filter-select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}
</style>
