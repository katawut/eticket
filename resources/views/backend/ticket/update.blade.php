@extends('backend.layouts.header', ['script' => ['editor' => true, 'dropzone' => true]], ['css' => ['dropzone' => true]])

@push('custom-style')
    <style>
        h5 { letter-spacing: 2px; }
        .control-label { color: darkblue; font-size: 14px; font-weight: bold; letter-spacing: 1px; }
        .btn { font-size: 14px; font-weight: bold; letter-spacing: 2px; }
    </style>
@endpush

@section('title')
<h3>
    <i class="fa fa-lg fa-ticket-alt"></i> แก้ไขบัตรเข้าชม
</h3>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="panel " data-sortable-id="form-validation-1">
            <div class="panel-heading panel-black">
                <h5 class="text-white">แก้ไขข้อมูลบัตรเข้าชม</h5>
            </div>

            <div class="panel-body">
                <form class="form-horizontal" method='post' id="form-validate" name="demo-form" enctype="multipart/form-data"
                    action="{{ route('backend.ticket.update', ['ticket' => $ticket->id] ) }}">
                    @method('patch')
                    @csrf
                    @include('backend.ticket.form')
                </form>
            </div>
            <!-- end panel-body -->
        </div>
        <!-- end panel -->
    </div>
    <!-- end col-6 -->
</div>
<!-- end row -->

@endsection
