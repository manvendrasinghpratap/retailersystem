@extends('backend.layouts.master-horizontal')
@section('title')
    {{ array_key_exists('title', $breadcrumb) ? $breadcrumb['title'] : '' }}
@endsection
@section('content')
    @include('backend.components.breadcrumb')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ isset($accountSetting) ? __('translation.edit_account_setting') : __('translation.add_account_setting') }}</h4>
                </div>
                <div class="card-body">
                    <form id="accountSettingForm" autocomplete="off" method="POST" action="{{ isset($accountSetting) ? route('admin.account-settings.update', $accountSetting->id) : route('admin.account-settings.store') }}" class="needs-validation" novalidate>
                        @csrf
                        @isset($accountSetting) @method('PUT') @endisset
                        <div class="row">
                            {{-- Module --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('translation.module') }}<span class="text-danger">*</span></label>
                                @if(isset($accountSetting))
                                    <input type="text" class="form-control" value="{{ ucfirst($accountSetting->module) }}" readonly>
                                    <input type="hidden" name="module" value="{{ $accountSetting->module }}">
                                @else
                                    <x-select-dropdown nolabel="false" name="module" label="{{ __('translation.module') }}" :options="$availableModules" :selected="old('module')" required class="module" mainrows="12" />
                                @endif
                                @error('module')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0"> {{ __('translation.settings') }}</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th width="35%"> {{ __('translation.setting_key') }}</th>
                                        <th> {{ __('translation.setting_value') }}</th>
                                        <!-- <th width="80"> {{ __('translation.action') }}</th> -->
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(isset($accountSetting) && !empty($accountSetting->settings))
                                        @php
                                            $allowedSettings = ['tax', 'session_timeout', 'warning_before', 'pagination'];
                                        @endphp
                                        @foreach($accountSetting->settings as $key => $value)
                                            @if(in_array($key, $allowedSettings))
                                                <tr>
                                                    <td><input type="text" name="keys[]" class="form-control" value="{{ $key }}" required readonly></td>
                                                    <td><input type="text" name="values[]" class="form-control" value="{{ $value }}"></td>
                                                    <!-- <td class="text-center"><button type="button" class="btn btn-danger btn-sm removeRow"><i class="bx bx-trash"></i></button></td> -->
                                                </tr>
                                            @endif
                                        @endforeach
                                    @else
                                        <tr>
                                            <td><input type="text" name="keys[]" class="form-control" value="tax" required readonly></td>
                                            <td><input type="text" name="values[]" class="form-control"></td>
                                            <td class="text-center"><button type="button" class="btn btn-danger btn-sm removeRow"><i class="bx bx-trash"></i></button></td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        @error('keys')
                            <div class="text-danger mb-3">
                                {{ $message }}
                            </div>
                        @enderror
                        <div class="row">
                            <x-form-buttons submitText="{{ isset($accountSetting) ? 'Update' : 'Save' }}" resetText="{{ __('translation.cancel') }}" url="{{ route('admin.account-settings.index') }}" />
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(document).ready(function () {

            $(document).on('click', '.removeRow', function () {

                let row = $(this).closest('tr');

                if ($('#settingsTable tbody tr').length <= 1) {

                    Swal.fire({
                        icon: 'warning',
                        title: 'Warning',
                        text: 'At least one setting is required.',
                        confirmButtonText: 'OK'
                    });

                    return;
                }

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Do you want to remove this setting?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#556ee6',
                    cancelButtonColor: '#f46a6a',
                    confirmButtonText: 'Yes, Remove',
                    cancelButtonText: 'Cancel'
                }).then((result) => {

                    if (result.isConfirmed) {
                        row.remove();
                        $('#accountSettingForm').submit();
                    }

                });

            });

        });
    </script>
@endsection