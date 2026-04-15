@extends('backend.pdf.layouts.master')
@section('title', array_key_exists('heading',$pdfHeaderdata)?$pdfHeaderdata['heading']:'')
@section('content')	
        <table  class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{__('translation.category')}}</th>
                    <th>{{__('translation.description')}}</th>
                    <th>{{__('translation.status')}}</th>
                    <th>{{__('translation.createdat')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($menuTypeList as $menuType)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $menuType->type }}</td>
                    <td>{{ substr($menuType->description,0,50) }}</td>
                     <td>{{ $menuType->status == 1 ? 'Active':'In-active'}}</td>
                    <td>{{ App\Helpers\Settings::getFormattedDatetime($menuType->created_at)}}</td>
                </tr>
                @endforeach
            </tbody>
        </table> 		
@endsection
