<!-- begin row -->

      <div class="row">

        <!-- begin col-12 -->

        <div class="col-lg-12">

          <!-- begin panel -->

          <div class="panel ">

            <!-- begin panel-heading -->

           

            <!-- end panel-heading -->

            <!-- begin panel-body -->

            <div class="panel-body">

              <table id="data-table-list" class="table table-striped table-bordered w-100 nowrap ">

                <thead>

                  <tr>

                    <!-- <th width="1%">ลำดับ</th> -->

                    <th class="text-center">จัดการ</th>

                    <th class="text-center">วันที่อัพเดต</th>

                    <th class="text-center">รูป</th>

                    <th class="text-center">ชื่อ</th>

                    <th class="text-center">url</th>    

                    <th class="text-center">ผู้แก้ไขล่าสุด</th>

                  </tr>

                </thead>

                <tbody>

                

                  @if(!empty($banners)) 

                    @foreach($banners as $banner)

                      <tr class='del'>

                        <td class="text-center">

                          <div class=" dropright" >

                            <button class="btn btn-white dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

                             <i class="fas fa-bars "></i>

                               <span class="sr-only">Toggle Dropdown</span>

                            </button>



                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">

                            {{-- @can('delete banner')

                              <form action="{{ route('backend.banner.destroy', ['banner' => $banner->id]) }}" method='post' >

                               {{ method_field('DELETE') }}

                                 <button class="del-trans  dropdown-item" data-id="" data-module="Del" data-controller="" 

                                  ><i class="fa fa-trash text-danger"></i>&nbsp;&nbsp; ลบ </button>

                                   @csrf

                              </form>

                            @endcan --}}

                            @can('edit banner')

                              <div class="dropdown-divider"></div>

                              <a href="{{ route('backend.banner.edit', ['banner' => $banner->id]) }}" class=" edit  dropdown-item" data-id="" ><i class="fa fa-pencil-alt text-warning"></i>&nbsp;&nbsp; แก้ไข </a>

                            @endcan



                          </div>

                        </div>

                        </td>



                        <td class='text-center'>{{ !empty($banner->banners_detail[0]->updated_at) ? $banner->banners_detail[0]->updated_at->format('d/m/Y H:i:s') : '' }}</td>

                        <td class='text-center'>

                          @if(collect($banner->banners_detail)->first()->type == 'image')

                          <img src="{{ collect($banner->banners_detail)->first()->image_thumb ?? '' }}" class='img-table'>

                          @elseif(collect($banner->banners_detail)->first()->type == 'video')

                            <video class='w-100' controls>

                              <source src="{{collect($banner->banners_detail)->first()->banner_video}}" type="video/mp4">

                            Your browser does not support the video tag.

                            </video>

                           @endif

                        </td>

                        <td class='text-left'>{{ $banner->name }}</td>

                        <td class='text-left'>{{collect($banner->banners_detail)->first()->url }}</td> 

                        <td class='text-center'>{{ collect($banner->banners_detail)->first()->update_name->name }}</td>

                      </tr>

                    @endforeach

                  @endif

                </tbody>

              </table>

            </div>

            <!-- end panel-body -->

          </div>

          <!-- end panel -->

        </div>

        <!-- end col-12 -->

      </div>



@push('after-scripts')

<script>

    data_table_list.columns.adjust().draw();

</script>



@endpush      