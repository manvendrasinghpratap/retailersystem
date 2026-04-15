@extends('backend.pdf.layouts.master')
@section('title', array_key_exists('heading',$pdfHeaderdata)?$pdfHeaderdata['heading']:'')
@section('content')	
      <table id="datatable-buttons-" class="table table-striped align-middle">
         <thead>
           <tr>
               <th>#</th>
               <th>{{__('translation.category')}}</th>
               <th>{{__('translation.product')}}</th>
               <th>{{__('translation.ngn')}} {{__('translation.regular').' '. __('translation.price')}}</th>
               <th>{{__('translation.status')}}</th>
               <th>{{__('translation.createdat')}}</th>
           </tr>
         </thead>
        <tbody>
             @foreach($menuList as $menu)
             <tr>
                 <td>{{ $loop->iteration }}</td>
                 <td>{{ @$menu->menuType->type }}</td>
                 <td>{{ $menu->title }}</td>
                 <td>{{__('translation.ngn')}}  {{ \App\Helpers\Settings::getcustomnumberformat($menu->price) }}</td>
                 <td>{{ $menu->status == 1 ? 'Active':'In-active'}}</td>
                 <td>{{ App\Helpers\Settings::getFormattedDatetime($menu->created_at)}}</td>
             </tr>
             @endforeach
        </tbody>
      </table>			
@endsection
