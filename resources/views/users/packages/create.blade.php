@extends('users.template')
@section('content')
<!-- Content Wrapper START -->
<div class="main-content">
    <div class="container-fluid">
        
        <div class="row">
            @include('common.errors')
            <div class="col-md-12">
                <div class="card">
                    
                    <div class="card-heading border bottom">
                        <h4 class="card-title">{{$table}}</h4>
                    </div>
                    <div class="card-block">
                        <div class="card-block">
                            <div class="row">
                                <div class="col-md-8 ml-auto mr-auto">
                                    <form role="form" id="form-validation" action="" method="post" enctype="multipart/form-data">
                                        {!! csrf_field() !!}
                                        <div class="form-group">
                                            <label class="text-normal text-dark">Account no.</label><br>
                                            <select id="e1" class="populate" style=" width: 300px;" name="mailbox11" disabled="">
                                                <option value="">Select A Account no.</option>
                                                @if($users)
                                                    @foreach($users as $row)
                                                        <option value="{{$row->mailbox}}" <?php if($row->mailbox == $mailbox){ ?> selected="" <?php } ?>>{{$row->first_name.' '.$row->last_name.' Account no: '.$row->mailbox}}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="text-normal text-dark">Name</label>
                                                    <input type="hidden" name="mailbox" value="{{$mailbox}}" placeholder="" class="form-control">
                                                    <input type="text" name="nick_name" value="{{Request::old('nick_name')}}" placeholder="Name" class="form-control" required="">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="exampleInputEmail1">Merchant</label>
                                                    <input type="text" name="merchant" value="{{Request::old('merchant')}}" placeholder="Merchant" class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="exampleInputEmail1">Carrier</label>
                                                    <input type="text" name="courier" value="{{Request::old('courier')}}" placeholder="Carrier" class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="exampleInputEmail1">Carrier tracking number</label>
                                                    <input type="text" name="supplier_tracking_no" value="{{Request::old('supplier_tracking_no')}}" placeholder="Carrier Tracking Number" class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="text-normal text-dark">Weight</label>
                                                    <input type="text" name="weight" class="form-control" value="{{Request::old('weight')}}" placeholder="Weight" required="">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="exampleInputEmail1">Value (US $)</label>
                                                    <input type="text" name="price" value="{{Request::old('price')}}" placeholder="Value (US $)" class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                        <!--<div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="text-normal text-dark">Custom Fee</label>
                                                    <input type="text" name="custom_fee" class="form-control" value="{{Request::old('custom_fee')}}" placeholder="Custom Fee" required="">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="text-normal text-dark">Select A Package Type</label>
                                                    <select class="selectize-group" name="package_type" id="package_type" required="" >
                                                        <option value="">Select A Package Type</option>
                                                        @if($types)
                                                            @foreach($types as $row)
                                                                <option value="{{$row->code.'-'.$row->type}}">{{$row->type}}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </div>
                                            </div>
                                        </div>-->
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label class="text-normal text-dark">Description</label>
                                                    <textarea class="form-control" name="item_description" rows="3" placeholder="Item Description">{{Request::old('item_description')}}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label class="col-md-12" style="padding:10px 0px;">Attach an invoice </label>
                                                    <hr>
                                                    <input type="file" name="input_img" class="default" />
                                                </div>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Submit</button>
                                        <button type="button" class="btn btn-default" onclick="clearFields();">Clear</button>
                                    </form>
                                </div>
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
        $('#form-1-5').val('');
        $('#title').val('');
        
    }
    $('#role').on('change', function() {
        var id = this.value;
        if(id == '1'){
            $('#show_module').hide();
        }else{
            $('#show_module').show();
        }
    });
</script>
@endsection     