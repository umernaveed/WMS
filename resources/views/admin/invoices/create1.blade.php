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
                        <form id="invoice" role="form" method="post" action="{{URL::to('admin/create/invoice')}}">
                        {!! csrf_field() !!}
                        <div class="form-group">
                            <a href='{{URL::to('admin/invoice/add')}}'><input type="button" class="btn btn-info" value="Back to Account no Search" /></a>
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
                            <label for="exampleInputEmail1">Number Of Fields</label>
                            <input type="number" id="total_rows" name="total_rows" maxlength="9" value="{{Request::old('total')}}" placeholder="9" class="form-control">
                        </div>
                        <input type="button" class="btn btn-info" value="Add Fields" onClick='submitDetailsForm()' />
                    </form>
                    </div>

                </div>
            </section> 
    </div>
</div> 
<script>
    function submitDetailsForm() {
        var tracking_no   = $("#tracking_no").val();
        var total_rows   = $("#total_rows").val();
        var count = 0;
        if (tracking_no !=""){
            $("#tracking_msg").css({"border-color": "#b7b7b7", "border-width": "1px", "border-style": "solid"});
        }else{
            $("#tracking_msg").css({"border-color": "#FD0004", "border-width": "1px", "border-style": "solid"});
            count++;
        }
        if (total_rows !=""){
            $("#total_rows").css({"border-color": "#b7b7b7", "border-width": "1px", "border-style": "solid"});
        }else{
            $("#total_rows").css({"border-color": "#FD0004", "border-width": "1px", "border-style": "solid"});
            count++;
        }
        if (count == 0){
            $("#invoice").submit();
        }
    }
</script>
@endsection        
        
      