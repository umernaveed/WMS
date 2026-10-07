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
                        <form role="form" method="post" action="" enctype="multipart/form-data">
                        {!! csrf_field() !!}
                        <div class="form-group">
                            <label for="exampleInputEmail1">Account no.</label>
                            <div class="col-lg-12" id="mailbox_msg" style="padding-left: 0px;margin: 14px 0;">
                                <select id="e1" class="populate" style=" width: 300px;" name="mailbox11" disabled="">
                                    <option value="">Select A Account no.</option>
                                    @if($users)
                                 	@foreach($users as $row)
                                            <option value="{{$row->mailbox}}" <?php if($row->mailbox == $mailbox){ ?> selected="" <?php } ?>>{{$row->first_name.' '.$row->last_name.' Account no: '.$row->mailbox}}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
<!--                            <select class="form-control m-bot15" name="mailbox">
                                <option value="">Select Account no.</option>
                                @if($users)
                                 	@foreach($users as $row)
                                		<option value="{{$row->mailbox}}">{{$row->first_name.' '.$row->last_name.' Account no:'.$row->mailbox}}</option>
                                    @endforeach
                                @endif
                            </select>-->
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">Name</label>
                            <input type="text" name="nick_name" value="{{Request::old('nick_name')}}" placeholder="Name" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">Item Description</label>
                            <textarea class="form-control" name="item_description" rows="3" placeholder="Item Description">{{Request::old('item_description')}}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">Value (US $)</label>
                            <input type="text" name="price" value="{{Request::old('price')}}" placeholder="Value (US $)" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">Merchant</label>
                            <input type="text" name="merchant" value="{{Request::old('merchant')}}" placeholder="Merchant" class="form-control">
                        </div>
<!--                        <div class="form-group">
                            <label for="exampleInputEmail1">Promotion Code</label>
                            <input type="text" name="permotion_code" value="{{Request::old('permotion_code')}}" placeholder="Promotion Code" class="form-control">
                        </div>-->
                        <div class="form-group">
                            <input type="hidden" name="mailbox" value="{{$mailbox}}" placeholder="" class="form-control">
                            <label for="exampleInputEmail1">Courior</label>
                            <input type="text" name="courier" value="{{Request::old('courier')}}" placeholder="Supplier" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">supplier tracking number</label>
                            <input type="text" name="supplier_tracking_no" value="{{Request::old('supplier_tracking_no')}}" placeholder="supplier tracking number" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">Weight</label>
                            <input type="text" name="weight" value="{{Request::old('weight')}}" maxlength="50" placeholder="Weight" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">Package Type</label>
                            <div class="col-lg-12" id="mailbox_msg" style="padding-left: 0px;margin: 14px 0;">
                                <select id="e9" style=" width: 300px;" name="package_type">
                                    <option value="">Select A Package Type</option>
                                    @if($types)
                                        @foreach($types as $row)
                                            <option value="{{$row->code.'-'.$row->type}}">{{$row->type}}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-12" style="padding:10px 0px;">Attach an invoice </label>
                            <hr>
                            <input type="file" name="input_img" class="default" />
                        </div>
<!--                        <div class="form-group">
                            <div class="col-md-2" style="padding-left:0px;">
                            <label for="exampleInputEmail1">EEI</label>
                            </div>
                            <div class="col-md-12" style="padding-left:0px;">
                                <div class="checkbox ">
                                    <input type="radio" id="EEI" name="EEI" value="yes" class="checkBox" style="margin-left: 2px;">
                                    <label style="margin-left: 10px;">yes</label>
                                </div>
                            </div>
                            <div class="col-md-2" style="padding-left:0px;">
                                <div class="checkbox ">
                                    <input type="radio" id="is_members" name="EEI" value="no" class="checkBox" style="margin-left: 2px;">
                                    <label style="margin-left: 10px;">no</label>
                                </div>
                            </div>
                        </div>-->
                        <div style=" width: 100%; clear: both;"></div>
                        <div class="form-group">
                            <input type="submit" class="btn btn-info" value="<?php echo $table; ?>" />
                        </div>
                    </form>
                    </div>

                </div>
            </section> 
    </div>
</div> 
@endsection        
        
      