@extends('shared::layouts.app')
@section('content')

<div class="container py-4">

    <h3 class="mb-4">
        Stage Status Management
    </h3>

    

    {{-- FORM --}}
    <div class="card mb-4">

        <div class="card-body">

            <form action="{{ $editStatus
                    ? route('stage-status.update', $editStatus->id)
                    : route('stage-status.store') }}"
                method="POST">

                @csrf

                @if($editStatus)
                    @method('PUT')
                @endif

                <div class="row">

                    {{-- NAME --}}
                    <div class="col-md-3">

                        <label class="mb-1">
                            Status Name
                        </label>

                        <input type="text"
                            name="name"
                            class="form-control"
                            value="{{ old('name', $editStatus->name ?? '') }}"
                            required>

                    </div>

                    {{-- TYPE --}}
                    <div class="col-md-3">

                        <label class="mb-1">
                            Status Type
                        </label>

                        <select name="type"
                            class="form-control"
                            required>

                            <option value="">
                                Select
                            </option>

                            <option value="parent"
                                {{ old('type', $editStatus->type ?? '') == 'parent' ? 'selected' : '' }}>

                                Parent Stage Status

                            </option>

                            <option value="sub"
                                {{ old('type', $editStatus->type ?? '') == 'sub' ? 'selected' : '' }}>

                                Sub Stage Status

                            </option>

                        </select>

                    </div>

                    {{-- ORDER --}}
                    <div class="col-md-2">

                        <label class="mb-1">
                            Order
                        </label>

                        <input type="number"
                            name="order_no"
                            class="form-control"
                            value="{{ old('order_no', $editStatus->order_no ?? 1) }}">

                    </div>

                    {{-- BUTTON --}}
                    <div class="col-md-2 d-flex align-items-end">

                        <button class="btn btn-primary w-100">

                            {{ $editStatus ? 'Update Status' : 'Save Status' }}

                        </button>

                    </div>

                    {{-- CANCEL --}}
                    @if($editStatus)

                        <div class="col-md-2 d-flex align-items-end">

                            <a href="{{ route('stage-status.index') }}"
                                class="btn btn-secondary w-100">

                                Cancel

                            </a>

                        </div>

                    @endif

                </div>

            </form>

        </div>

    </div>

    <div class="row">

        {{-- PARENT STATUS --}}
        <div class="col-md-6">

            <div class="card">

                <div class="card-header">
                    Parent Stage Statuses
                </div>

                <div class="card-body">

                    <table class="table table-bordered align-middle">

                        <thead>

                            <tr>
                                <th>Name</th>
                                <th>Order</th>
                                <th width="180">
                                    Action
                                </th>
                            </tr>

                        </thead>

                        <tbody>

                            @forelse($parentStatuses as $status)

                                <tr>

                                    <td>
                                        {{ $status->name }}
                                    </td>

                                    <td>
                                        {{ $status->order_no }}
                                    </td>

                                    <td>

                                        <div class="d-flex gap-2">

                                            {{-- EDIT --}}
                                            <a href="{{ route('stage-status.edit', $status->id) }}"
                                                class="btn btn-warning btn-sm">

                                                Edit

                                            </a>

                                            {{-- DELETE --}}
                                            <form action="{{ route('stage-status.destroy', $status->id) }}"
                                                method="POST">

                                                @csrf
                                                @method('DELETE')

                                                <button class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Delete this status?')">

                                                    Delete

                                                </button>

                                            </form>

                                        </div>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="3"
                                        class="text-center text-muted">

                                        No Parent Status Found

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

        {{-- SUB STATUS --}}
        <div class="col-md-6">

            <div class="card">

                <div class="card-header">
                    Sub Stage Statuses
                </div>

                <div class="card-body">

                    <table class="table table-bordered align-middle">

                        <thead>

                            <tr>
                                <th>Name</th>
                                <th>Order</th>
                                <th width="180">
                                    Action
                                </th>
                            </tr>

                        </thead>

                        <tbody>

                            @forelse($subStatuses as $status)

                                <tr>

                                    <td>
                                        {{ $status->name }}
                                    </td>

                                    <td>
                                        {{ $status->order_no }}
                                    </td>

                                    <td>

                                        <div class="d-flex gap-2">

                                            {{-- EDIT --}}
                                            <a href="{{ route('stage-status.edit', $status->id) }}"
                                                class="btn btn-warning btn-sm">

                                                Edit

                                            </a>

                                            {{-- DELETE --}}
                                            <form action="{{ route('stage-status.destroy', $status->id) }}"
                                                method="POST">

                                                @csrf
                                                @method('DELETE')

                                                <button class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Delete this status?')">

                                                    Delete

                                                </button>

                                            </form>

                                        </div>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="3"
                                        class="text-center text-muted">

                                        No Sub Status Found

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection