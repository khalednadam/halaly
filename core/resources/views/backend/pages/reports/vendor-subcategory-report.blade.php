@extends('backend.layout.master')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Vendor Subcategory Report') }}</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('Subcategory') }}</th>
                                    <th>{{ __('Number of Listings') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($report as $key => $data)
                                    <tr>
                                        <td>
                                            <span class="badge bg-info">{{ $data['label'] }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">{{ $data['count'] }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.listing.filter.vendor-subcategory', ['vendor_subcategory' => $key]) }}" class="btn btn-sm btn-info">
                                                {{ __('View Listings') }}
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">{{ __('No data available') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
