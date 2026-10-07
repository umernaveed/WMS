@extends('users.template')
@section('content')
<!-- Content Wrapper START -->
<div class="main-content">
    
    <div class="alert alert-danger alert-dismissable show_message_error" style="margin:10px 15px;display: none;">
        <button type="button" class="close close-sm" data-dismiss="alert">
            <i class="fa fa-times"></i>
        </button>
        <p style="text-align:center; line-height: 18px !important; color:#a94442;margin: 2px;" class="error_message">You do not have exchange rate against this country.  </p>
    </div>
    
    <div class="container-fluid">
        
        <div class="row">
            @include('common.errors')
            <div class="col-md-12">
                <div class="card">
                    
                    <div class="card-heading border bottom">
                        <h4 class="card-title">{{$table}}</h4>
                    </div>
                    <div class="card-block">
                            <div class="row">
                                <div class="col-md-8 ml-auto mr-auto">
                                    
                                    <form role="form" id="form-validation" action="" method="post" enctype="multipart/form-data">
                                        {!! csrf_field() !!}
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Who should we be expecting?</label>
                                                    <input type="hidden" class="form-control" name="package_total" id="package_total"  value="{{$package_total}}"  placeholder="Who should we be expecting?">
                                                    <input type="hidden" class="form-control" name="no_of_packages" id="no_of_packages"  value="{{$no_of_packages}}"  placeholder="Who should we be expecting?">
                                                    <input type="hidden" class="form-control" name="package_ids" id="package_ids"  value="{{$package_ids}}"  placeholder="Who should we be expecting?">
                                                    <input type="hidden" name="schdule_method" id="schdule_method" value="post">
                                                    <input type="text" class="form-control" name="name" id="name"  value="{{$user->first_name.' '.$user->last_name}}"  placeholder="Who should we be expecting?" required="">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Contact</label>
                                                    <input type="text" class="form-control" name="phone" id="phone"  value="{{$user->phone}}"  placeholder="Phone" required="">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Select Day</label>
                                                    <div class="mrg-top-10">
                                                        <select class="selectize-group" name="day_id" id="day_id" required="" onchange="changeDay()">
                                                            
                                                            @if(count($days) > 0)
                                                                @foreach($days as $row)
                                                                    <?php
                                                                        $selected_day = 0;
                                                                        if(count($days_selected) > 0){ 
                                                                            foreach($days_selected as $day){ 
                                                                                if($day->day_id == $row->id){
                                                                                    $selected_day = 1;
                                                                                }
                                                                            }
                                                                        }
                                                                    ?>
                                                                    <option value="{{$row->id}}-{{$selected_day}}" >{{$row->day}}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Select Area</label>
                                                    <div class="mrg-top-10">
                                                        <select class="selectize-group" name="area_id" id="area_id" required="" onchange="getAreaCostNew(value)">
                                                            <option value="">Select Area</option>
                                                            @if(count($areas) > 0)
                                                                @foreach($areas as $row)
                                                                    <option value="{{$row->id.'-'.$row->cost}}" >{{$row->name}}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Delivery Cost</label>
                                                    <input type="text" class="form-control" name="cost" id="cost" value="0.00" placeholder="Delivery Cost" readonly="">
                                                </div>
                                            </div>
                                        </div>
<!--                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Date</label>
                                                    <input type="text" class="form-control" name="req_date" id="date" value="{{Request::old('date')}}" placeholder="YY-MM-DD" required="">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Time</label>
                                                    <input type="text" class="form-control" name="req_time" id="time" value="{{Request::old('time')}}" placeholder="Time" required="">
                                                </div>
                                            </div>
                                        </div>-->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Address</label>
                                                    <textarea class="form-control" name="address" id="address" placeholder="Address" required=""> {{$user->address_1}}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Notes</label>
                                                    <textarea class="form-control" name="notes" id="notes" placeholder="Notes" required=""> {{Request::old('notes')}}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-danger">Schedule Pick Up</button>
                                        <a href="{{ URL::to('/member/package/pickup/requests') }}"><button type="button" class="btn btn-default" >Cancel</button></a>
                                    </form>
                                    <!--<strong>Note:</strong> <p>1. If you are sending a third party on your behalf, he/she needs to travel with their identification.<br>2. Please ensure that your scheduling is within your Pick Up Location's working hours.</p>-->
                                </div>
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Content Wrapper END -->
<script>
    function clearFields(){
        $('#summernote-usage').val('');
        $('#title').val('');
    }
    
    $(document).ready(function(){
        $('#time').bootstrapMaterialDatePicker({
            date: false,
            shortTime: true,
            format: 'hh:mm A'
        });
        $('#date').bootstrapMaterialDatePicker({ 
            weekStart : 0, 
            time: false,
            minDate : new Date()
        });
    });
    
    function getAreaCostNew(id){
        var day_id = $('#day_id').val();
        var str = day_id;
        var res = str.split("-");
        var checkSelect = res[1];
        if (checkSelect == 0) {
            $('#cost').val('0.00');
        }else{
            var str = id;
            var res = str.split("-");
            $('#cost').val(res[1]);
        }
    }
    
    function changeDay(){
        var $select = $("#area_id").selectize();
        var selectize = $select[0].selectize;
        selectize.setValue('');
        $('#cost').val('0.00');
    }
    
    
    
    
    
</script>
@endsection