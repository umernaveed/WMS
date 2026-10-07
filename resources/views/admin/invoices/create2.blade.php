@extends('admin.template')
@section('content')
<style>
.radio, .checkbox{
	padding-left:0px;
}
.icheck div, .icheck .disabled {
    float: left;
}
.icheck .single-row {
    display: inline-block;
    width: 100%;
}
</style>
<div class="row">
	<div class="col-lg-12">
            
     @include('common.errors')
         <section class="panel">
                <header class="panel-heading">
                    <?php echo $table; ?>
                </header>
                <div class="panel-body">
                    <div class="position-center">
                        <form id="invoice" method="post" action="{{URL::to('admin/invoice/submit')}}">
                        {!! csrf_field() !!}
                        <div class="form-group">
                            <a href='{{URL::to('admin/invoice/add')}}'><input type="button" class="btn btn-info" value="Back to Account no Search" /></a>
                            <input type="hidden" name="mailbox_no" value="{{$mailbox}}" placeholder="GCT" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">Package Tracking No</label>
                            <div class="col-lg-12" id="tracking_msg" style="padding-left: 0px;margin: 14px 0;">
                                <select id="tracking_no"  class="form-control"  name="tracking_no">
                                    @if($packeges)
                                 	@foreach($packeges as $row)
                                            <option value="{{$row->tracking_no}}">{{$row->tracking_no}}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">Tex</label>
                            <input type="text" name="gct" id="gct" value="{{Request::old('gct')}}" placeholder="GCT" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">Memo</label>
                            <input type="text" name="type" id="type" value="{{Request::old('type')}}" placeholder="Notes/Memo" class="form-control">
                        </div>
                        @if($total_rows > 0)
                            <div class="form-group">
                                <div class="row" style="border: 1px solid #eee;padding: 10px 5px;width: 100%;margin-left: 1px;">
                                    <div class="outer_lable" style="background: #eee;float: left;width: 96%;padding: 10px;margin: 0px 15px;">
                                        <div class="col-lg-4">
                                            <label for="exampleInputEmail1">Description</label>
                                        </div>
                                        <div class="col-lg-4">
                                            <label for="exampleInputEmail1">USD Amount </label>
                                        </div>
                                        <div class="col-lg-4">
                                            <label for="exampleInputEmail1">JMD Amount</label>
                                        </div>
                                    </div>
                                    <?php for($i = 1;  $i <=$total_rows; $i++ ){ ?>
                                    <div class="outer_desc" style="margin-top: 8px;float: left;width: 100%;">
                                        <div class="col-lg-4">
                                            <input type="text" name="name[]" value="{{Request::old('name')}}" placeholder="Name" class="form-control name" required="">
                                        </div>
                                        <div class="col-lg-4">
                                            <input type="text" name="usd[]" value="{{Request::old('usd')}}" placeholder="USD" class="form-control usd" required="">
                                        </div>
                                        <div class="col-lg-4">
                                            <input type="text" name="jmd[]" value="{{Request::old('jmd')}}" placeholder="JMD" class="form-control jmd" required="">
                                        </div>
                                    </div>
                                    <?php } ?>
                                </div>
                            </div>
                        @endif
<!--                        <div class="form-group">
                            <label for="exampleInputEmail1">Status</label>
                            <div class="col-lg-12" style="padding-left: 0px;margin: 14px 0;">
                                <select  class="form-control"  name="status">   
                                    <option value="0">Unpaid</option>
                                    <option value="1">Paid</option>
                                </select>
                            </div>
                        </div>-->
                        <input type="button" class="btn btn-info" value="Save changes" onClick='submitDetailsForm()'  />
                    </form>
                    </div>

                </div>
            </section> 
    </div>
</div>
<script>
    function submitDetailsForm() {
        var tracking_no   = $("#tracking_no").val();
        var gct   = $("#gct").val();
        var type   = $("#type").val();
        var name   = $(".name").val();
        var usd   = $(".usd").val();
        var jmd   = $(".jmd").val();
        var count = 0;
        if (tracking_no !=""){
            $("#tracking_msg").css({"border-color": "#b7b7b7", "border-width": "1px", "border-style": "solid"});
        }else{
            $("#tracking_msg").css({"border-color": "#FD0004", "border-width": "1px", "border-style": "solid"});
            count++;
        }
        if (type !=""){
            $("#type").css({"border-color": "#b7b7b7", "border-width": "1px", "border-style": "solid"});
        }else{
            $("#type").css({"border-color": "#FD0004", "border-width": "1px", "border-style": "solid"});
            count++;
        }
        if (gct !=""){
            $("#gct").css({"border-color": "#b7b7b7", "border-width": "1px", "border-style": "solid"});
        }else{
            $("#gct").css({"border-color": "#FD0004", "border-width": "1px", "border-style": "solid"});
            count++;
        }
        if (name !=""){
            $(".name").css({"border-color": "#b7b7b7", "border-width": "1px", "border-style": "solid"});
        }else{
            $(".name").css({"border-color": "#FD0004", "border-width": "1px", "border-style": "solid"});
            count++;
        }
        if (usd !=""){
            $(".usd").css({"border-color": "#b7b7b7", "border-width": "1px", "border-style": "solid"});
        }else{
            $(".usd").css({"border-color": "#FD0004", "border-width": "1px", "border-style": "solid"});
            count++;
        }
        if (jmd !=""){
            $(".jmd").css({"border-color": "#b7b7b7", "border-width": "1px", "border-style": "solid"});
        }else{
            $(".jmd").css({"border-color": "#FD0004", "border-width": "1px", "border-style": "solid"});
            count++;
        }
        if (count == 0){
            $("#invoice").submit();
        }
    }
</script>
@endsection        
        
      