<div class="row mb-3">
    <div class="col-md-6">
        <label class='control-label' for="title_th">
            <span class='text-danger'>*</span> หัวข้อ ( ไทย ) :
        </label>
        <input type="text" class="form-control" id="title_th" name="title_th"
        value="{{ old('title_th') ?? $ticket->title_th }}" required />
        {{ $errors->first('title_th') }}
    </div>

    <div class="col-md-6">
        <label class='control-label' for="title_en">
            <span class='text-danger'>*</span> หัวข้อ ( อังกฤษ ) :
        </label>
        <input type="text" class="form-control" id="title_en" name="title_en"
            value="{{ old('title_en') ?? $ticket->title_en }}" required />
        {{ $errors->first('title_en') }}
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label class="control-label" for="description_th">
            <span class='text-danger'>*</span> รายละเอียด ( ไทย ) :
        </label>
        <textarea name="description_th" class="form-control" id="description_th" name="description_th"
        rows='4' required>{{ old('description_th') ?? $ticket->description_th }}</textarea>
        {{ $errors->first('description_th') }}
    </div>

    <div class="col-md-6">
        <label class="control-label" for="description_en">
            <span class='text-danger'>*</span> รายละเอียด ( อังกฤษ ) :
        </label>
        <textarea name="description_en" class="form-control" id="description_en" name="description_en"
        rows='4' required>{{ old('description_en') ?? $ticket->description_en }}</textarea>
        {{ $errors->first('description_en') }}
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label class="control-label" for="condition_th">
            <span class='text-danger'>*</span> เงื่อนไขบัตร ( ไทย ) :
        </label>
        <textarea name="condition_th" class="form-control" id="condition_th" name="condition_th"
        rows='4' required>{{ old('condition_th') ?? $ticket->condition_th }}</textarea>
        {{ $errors->first('condition_th') }}
    </div>

    <div class="col-md-6">
        <label class="control-label" for="condition_en">
            <span class='text-danger'>*</span> เงื่อนไขบัตร ( อังกฤษ ) :
        </label>
        <textarea name="condition_en" class="form-control" id="condition_en" name="condition_en"
        rows='4' required>{{ old('condition_en') ?? $ticket->condition_en }}</textarea>
        {{ $errors->first('condition_en') }}
    </div>
</div>

<div class="row mb-3">
    @if (!empty($ticket->stocks))
        <div class="col-md-6">
            <label class="control-label" for="quantity">จำนวนบัตรคงเหลือ :</label>
            <input type="number" class="form-control" id="quantity" value="{{ old('quantity') ?? $ticket->stocks->quantity }}" readonly />
        </div>
    @else
        <div class="col-md-6">
            <label class="control-label" for="quantity">จำนวนบัตร :</label>
            <input type="number" class="form-control" id="quantity" name="quantity"
                value="{{ old('quantity') }}" required />
            {{ $errors->first('quantity') }}
        </div>
    @endif

    <div class="col-md-6">
        <label class="control-label" for="price">ราคาบัตรเข้าชม ( บาท ) :</label>
        <input type="number" class="form-control" id="price" name="price"
            value="{{ old('price') ?? $ticket->price }}" required />
        {{ $errors->first('price') }}
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label class="control-label" for="start_at">วันที่เริ่มจำหน่าย : </label>
        <input type="text" class="form-control datepicker-startdate" id="start_at" name="start_at"
            value="{{ !empty($ticket->start_at) ? \Carbon\Carbon::parse($ticket->start_at)->isoFormat('DD/MM/YYYY') : '' }}"
            data-parsley-required-message="กรุณาระบุวันที่เริ่มจำหน่าย" required>
        {{ $errors->first('start_at') }}
    </div>

    <div class="col-md-6">
        <label class="control-label" for="end_at">วันที่สิ้นสุดจำหน่าย : </label>
        <input type="text" class="form-control datepicker-enddate" id="end_at" name="end_at"
            value="{{ !empty($ticket->end_at) ? \Carbon\Carbon::parse($ticket->end_at)->isoFormat('DD/MM/YYYY') : '' }}"
            data-parsley-required-message="กรุณาระบุวันที่สิ้นสุดจำหน่าย" required>
        {{ $errors->first('end_at') }}
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label class="control-label" for="discount_price">ราคาส่วนลดบัตรเข้าชม ( บาท ) : </label>
        <input type="number" class="form-control" id="discount_price" name="discount_price"
            value="{{ old('discount_price') ?? $ticket->discount_price }}" required />
        {{ $errors->first('discount_price') }}
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label class="control-label">
            <span class='text-danger'>*</span> รูปภาพหลักบัตรเข้าชม :
        </label>

        <div class="icon">
            <div class="uploaded_image">
                <img id="preview_image" src="{{ $ticket->image ?? '' }}" class="img-icon" data-toggle='popover' data-html='true' />
            </div>
        </div>

        <div class="custom-file">
            <input type="file" class="image" id="image" accept="image/*" name="image"
                {{request()->route()->getActionMethod() == 'create' ? 'required' : ''}}>
            <label class="custom-file-label" for="image">เลือกรูป</label>
        </div>

        <label class='text-pic'>ขนาดภาพที่แนะนำ 100 x 100 ( ขนาดไม่เกิน 200 KB และรองรับเฉพาะไฟล์นามสกุล .jpg, .jpeg และ .png เท่านั้น )</label>
        {{ $errors->first('image') }}
    </div>

    <div class="col-md-6">
        <label class="control-label" style='width: 100%;'>สถานะการใช้งาน : </label>
        <div class="radio radio-css radio-inline">
            <input class="form-check-input" type="radio" name="active" id="active" value="1" {{ $ticket->active == 'Active' ? 'checked' : '' }}>
            <label class="form-check-label ml-2" for="active">เปิดใช้งาน</label>
        </div>

        <div class="radio radio-css radio-inline">
            <input class="form-check-input" type="radio" name="active" id="deactive" value="0" {{ $ticket->active == 'Inactive' ? 'checked' : '' }}>
            <label class="form-check-label ml-2" for="deactive">ปิดใช้งาน</label>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-12">
        <label class="control-label mb-2" for="image_detail">
            <span class='text-danger'>*</span> รูปภาพบัตรเข้าชมเพิ่มเติม
        </label>
        <div class="needsclick dropzone" id="document-dropzone"></div>
    </div>
</div>

<hr class="my-5">

<div class="row mb-5">
    <div class="col-12 text-left">
        <button type="submit" class="btn btn-white mr-2">
            <i class="fa fa-save text-success"></i> บันทึกข้อมูล
        </button>

        <button type="reset" class="btn btn-white mr-2 reset">
            <i class="fas fa-eraser text-warning"></i> ล้างข้อมูล
        </button>

        <button type="button" class="btn btn-white back" value="{{ url()->previous() }}">
            <i class="fas fa-reply text-danger"></i> ย้อนกลับ
        </button>
    </div>
</div>


@push('after-scripts')
<script>
    $(function() {
        $('#form-validate').validate({
            errorPlacement: function(error, element) {
                if($(element).attr('id') == 'image'){
                    error.insertAfter($(element).parent());
                    $(element).siblings('.custom-file-label').toggleClass('error-border');
                } else {
                    error.insertAfter(element);
                }
            }
        });

        $('#image').on('change', function(){
            readURL(this, "preview_image");
        });

        CKEDITOR.replace('description_th', {
            filebrowserUploadUrl: "{{ route('backend.ckeditor.upload', ['_token' => csrf_token() ]) }}",
            filebrowserUploadMethod: 'form'
        });

        CKEDITOR.replace('description_en', {
            filebrowserUploadUrl: "{{ route('backend.ckeditor.upload', ['_token' => csrf_token() ]) }}",
            filebrowserUploadMethod: 'form'
        });

        CKEDITOR.replace('condition_th', {
            filebrowserUploadUrl: "{{ route('backend.ckeditor.upload', ['_token' => csrf_token() ]) }}",
            filebrowserUploadMethod: 'form'
        });

        CKEDITOR.replace('condition_en', {
            filebrowserUploadUrl: "{{ route('backend.ckeditor.upload', ['_token' => csrf_token() ]) }}",
            filebrowserUploadMethod: 'form'
        });
    });

    let uploadedImageDetailMap = {}
    Dropzone.options.documentDropzone = {
        url: '{!! route('backend.dropzone.upload') !!}',
        maxFilesize: 2, // MB
        addRemoveLinks: true,
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },
        success: function (file, response) {
            $('form').append('<input type="hidden" name="image_detail[]" value="' + response.name + '">')
            uploadedImageDetailMap[file.name] = response.name
        },
        removedfile: function (file) {
            file.previewElement.remove()
            let name = ''

            if (typeof file.file_name !== 'undefined') {
                name = file.file_name
            } else {
                name = uploadedDocumentMap[file.name]
            }

            $('form').find('input[name="image_detail[]"][value="' + name + '"]').remove()
        },
        init: function () {
            @if(isset($ticket) && $ticket->image_detail)
                let files =
                    {!! json_encode($ticket->image_detail) !!}
                for (let i in files) {
                    let file = files[i]
                    this.options.addedfile.call(this, file)
                    let filePath = `/storage/${file.id}/${file.file_name}`;
                    file.previewElement.querySelector('img').src = filePath;
                    file.previewElement.classList.add('dz-complete')
                    $('form').append('<input type="hidden" name="image_detail[]" value="' + file.file_name + '">')
                }
            @endif
        }
    }
</script>
@endpush
