@extends('backend.layouts.master-horizontal')
@section('title', 'Designation Permissions')

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    Permissions: <strong>{{ $designation->name }}</strong>
                </h4>
                <a href="{{ route('admin.designations.index') }}" class="btn btn-secondary btn-sm">
                    Back to Designations
                </a>
            </div>

            <form method="POST" action="{{ route('designations.permissions.update', $designation->id) }}">
                @csrf
                @method('PUT')

                <div class="card-body">
                    @forelse ($modules as $module)
                        <div class="mb-4 border-bottom pb-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0 text-primary">
                                    <i class="feather icon-grid me-1"></i> {{ $module->name }}
                                </h5>
                                <button type="button" class="btn btn-sm btn-outline-secondary select-all-module" data-module-id="module_{{ $module->id }}">
                                    Select All Module
                                </button>
                            </div>

                            <div id="module_{{ $module->id }}">
                                @forelse ($module->menus as $menu)
                                    <div class="border rounded p-3 mb-3 bg-light">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0 font-weight-bold text-dark">
                                                {{ $menu->name }}
                                            </h6>
                                            <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none select-all-menu" data-menu-id="menu_{{ $menu->id }}">
                                                Toggle Menu Permissions
                                            </button>
                                        </div>

                                        <div class="row" id="menu_{{ $menu->id }}">
                                            @forelse ($menu->permissions as $permission)
                                                <div class="col-md-3 mb-2">
                                                    <div class="form-check">
                                                        <input type="checkbox" class="form-check-input permission-checkbox" name="permissions[]" value="{{ $permission->id }}" id="permission_{{ $permission->id }}" @checked(in_array($permission->id, $assignedPermissionIds))>
                                                        <label class="form-check-label" for="permission_{{ $permission->id }}">
                                                            {{ $permission->name }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="col-12">
                                                    <small class="text-muted">No permissions defined for this menu.</small>
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-muted ms-3">No menus found in this module.</p>
                                @endforelse
                            </div>
                        </div>
                    @empty
                        <div class="alert alert-warning mb-0">
                            No modules found. Please run your database seeds or SQL inserts.
                        </div>
                    @endforelse
                </div>

                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-primary">
                        Save Permissions
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Toggle all checkboxes within a module
            document.querySelectorAll('.select-all-module').forEach(button => {
                button.addEventListener('click', function () {
                    const targetId = this.getAttribute('data-module-id');
                    const checkboxes = document.querySelectorAll('#' + targetId + ' input[type="checkbox"]');
                    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                    checkboxes.forEach(cb => cb.checked = !allChecked);
                });
            });

            // Toggle all checkboxes within a menu
            document.querySelectorAll('.select-all-menu').forEach(button => {
                button.addEventListener('click', function () {
                    const targetId = this.getAttribute('data-menu-id');
                    const checkboxes = document.querySelectorAll('#' + targetId + ' input[type="checkbox"]');
                    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                    checkboxes.forEach(cb => cb.checked = !allChecked);
                });
            });
        });
    </script>
@endpush