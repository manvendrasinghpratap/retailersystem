@extends('backend.layouts.master-horizontal')

@section('title', 'Designation Permissions')

@section('content')

    <div class="container-fluid">

        <div class="card">

            <div class="card-header">
                <h4>
                    Permissions:
                    {{ $designation->name }}
                </h4>
            </div>

            <form method="POST"
                  action="{{ route(
                      'designations.permissions.update',
                      $designation->id
                  ) }}">

                @csrf
                @method('PUT')

                <div class="card-body">

                    @foreach ($modules as $module)

                        <div class="mb-4">

                            <h5 class="mb-3">
                                {{ $module->name }}
                            </h5>

                            @foreach ($module->menus as $menu)

                                <div class="border rounded p-3 mb-3">

                                    <h6>
                                        {{ $menu->name }}
                                    </h6>

                                    <div class="row">

                                        @foreach (
                                            $menu->permissions
                                            as $permission
                                        )

                                            <div class="col-md-3 mb-2">

                                                <div class="form-check">

                                                    <input
                                                        type="checkbox"
                                                        class="form-check-input"
                                                        name="permissions[]"
                                                        value="{{ $permission->id }}"
                                                        id="permission_{{ $permission->id }}"
                                                        @checked(
                                                            in_array(
                                                                $permission->id,
                                                                $assignedPermissionIds
                                                            )
                                                        )
                                                    >

                                                    <label
                                                        class="form-check-label"
                                                        for="permission_{{ $permission->id }}"
                                                    >
                                                        {{ $permission->name }}
                                                    </label>

                                                </div>

                                            </div>

                                        @endforeach

                                    </div>

                                </div>

                            @endforeach

                        </div>

                    @endforeach

                </div>

                <div class="card-footer">

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Save Permissions
                    </button>

                </div>

            </form>

        </div>

    </div>

@endsection