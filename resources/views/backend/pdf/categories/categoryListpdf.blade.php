@extends('backend.pdf.layouts.master')
@section('title', array_key_exists('heading', $pdfHeaderdata) ? $pdfHeaderdata['heading'] : '')
@section('content')
    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>{{__('translation.category_name')}}</th>
                <th>{{__('translation.brand_name')}}</th>
                <th>{{__('translation.slug')}}</th>
                <th>{{__('translation.image')}}</th>
                <th>{{__('translation.status')}}</th>
                <th>{{__('translation.createdat')}}</th>
            </tr>
        </thead>

        tbody>
        @if(!empty($categories))
            @foreach($categories as $categoriesType)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $categoriesType->name }}</td>
                    <td>{{ substr($categoriesType->description, 0, 50) }}</td>
                    <td>{{ $categoriesType->slug }}</td>
                    <td>
                        <img src="{{ (!empty($categoriesType->image) && file_exists(public_path('uploads/categories/small/' . $categoriesType->image))) ? asset('uploads/categories/small/' . $categoriesType->image) : asset('assets/images/no-image.png') }}" width="80" height="60" alt="Category Image">
                    </td>
                    <td>
                        @if($categoriesType->status == 1)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                    </td>
                    <td>{{ App\Helpers\Settings::getFormattedDatetime($categoriesType->created_at)}}</td>
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="7" class="text-center">No categories available</td>
            </tr>
        @endif
        </tbody>
    </table>
@endsection