@extends('backend.layouts.header')

@push('custom-style')
    <style>
        thead tr th { font-size: 14px; font-weight: bold; color: steelblue !important; letter-spacing: 2px; }
        tbody tr td { font-size: 14px; color: black !important; letter-spacing: 1px; }

        .control-label { color: darkblue; font-size: 14px; font-weight: bold; letter-spacing: 1px; }
        .btn { font-size: 14px; font-weight: bold; letter-spacing: 2px; }
    </style>
@endpush

@section('title')
<h3>
    <i class="fa fa-lg fa-ticket-alt"></i> จัดการบัตรเข้าชม
</h3>
@endsection

@section('content')
{{-- Form --}}
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse gray">
            <div class="panel-body mgbt">
                <form action="{{ route('backend.ticket.index') }}" method='post' data-parsley-validate="true">
                    @csrf
                    @method('get')
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label class="control-label">คีย์เวิร์ด </label>
                            <input type="text" class="form-control " name="keyword" value="{{ request('keyword') ?? '' }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-12 mt-2">
                            <button type="submit" class="btn btn-white mr-3">
                                <i class='fas fa-search text-info'></i> ค้นหา
                            </button>

                            @can('add ticket')
                            <a href="{{ route('backend.ticket.create') }}" class="btn btn-white">
                                <i class="fa fa-plus-square fa-lg text-success"></i> เพิ่มบัตรเข้าชม
                            </a>
                            @endcan
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Table --}}
<div class="row">
    <div class="col-md-12">
        <div class="panel ">
            <div class="panel-body">
                <table id="ticket-table-list" class="table table-striped table-bordered w-100 nowrap ">
                    <thead>
                        <tr class="text-center">
                            <th>จัดการ</th>
                            <th>ช่วงเวลาการขายบัตร</th>
                            <th>ชื่อบัตร</th>
                            <th>ราคาต่อหน่วย</th>
                            <th>ราคาส่วนลด</th>
                            <th>จำนวนคงเหลือ</th>
                            <th>ผู้แก้ไขล่าสุด</th>
                            <th>วันเวลาเพิ่มบัตร</th>
                            <th>วันเวลาอัพเดท</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($lists as $item)
                        <tr class="text-center">
                            <td>
                                <div class=" dropright">
                                    <button class="btn btn-white dropdown-toggle" type="button"
                                        id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                                        aria-expanded="false">
                                        <i class="fas fa-bars "></i>
                                        <span class="sr-only">Toggle Dropdown</span>
                                    </button>

                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        @can('edit ticket')
                                        {{-- <div class="dropdown-divider"></div> --}}
                                        <a href="{{ route('backend.ticket.edit', ['ticket' => $item['id']]) }}"
                                            class="edit dropdown-item" class="btn">
                                            <i class="fa fa-pencil-alt text-warning"></i> แก้ไข
                                        </a>
                                        @endcan
                                    </div>
                                </div>
                            </td>
                            <td>{{ $item['duration_sell'] }}</td>
                            <td>{{ $item['name'] }}</td>
                            <td class="font-weight-bold text-success">{{ $item['price'] }} บาท</td>
                            <td class="font-weight-bold text-danger">{{ $item['discount'] }} บาท</td>
                            <td>{{ $item['quantity'] }} ใบ</td>
                            <td>{{ $item['updated_by'] }}</td>
                            <td>{{ $item['created_at'] }}</td>
                            <td>{{ $item['updated_at'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
        </div>
        <!-- end panel-body -->
    </div>
    <!-- end panel -->
</div>
<!-- end row -->
</div>
@endsection
