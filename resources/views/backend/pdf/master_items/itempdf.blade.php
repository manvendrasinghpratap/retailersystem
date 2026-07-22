@extends('backend.pdf.layouts.master')
@section('title', array_key_exists('heading', $pdfHeaderdata) ? $pdfHeaderdata['heading'] : '')
@section('content')
    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('translation.image') }}</th>
                <th>{{ __('translation.code') }}</th>
                <th>{{ __('translation.name') }}</th>
                <th>{{ __('translation.description') }}</th>
                <th>{{ __('translation.status') }}</th>
            </tr>
        </thead>
        <tbody>
            @if(!empty($items))
                @foreach($items as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td> <img src="{{ (!empty($item->image) && file_exists(public_path(Config::get('main_constants.image_path') . $item->image))) ? asset(Config::get('main_constants.image_path') . $item->image) : asset(Config::get('main_constants.no_image')) }}" width="80" height="60" alt="Master Item Image"></td>
                        <td>{{ $item->code }}</td>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->description }}</td>
                        <td>
                            <span class="badge {{ $item->status ? 'bg-success' : 'bg-danger' }}">
                                {{ $item->status ? __('translation.active') : __('translation.inactive') }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="6" class="text-center">{{__('translation.no_items_available')}}</td>
                </tr>
            @endif
        </tbody>
    </table>
@endsection