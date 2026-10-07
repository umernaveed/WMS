@extends('admin.template')
@section('content')
<style>
    .ms-container {
        width: 670px;
    }
</style>
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
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label class="control-label">Select Customer</label>
                                                    <div>
                                                        <select multiple="multiple" class="multi-select" id="my_multi_select1" name="day_id[]">
                                                            <optgroup label="Select All">
                                                                @if(count($days) > 0)
                                                                    @foreach($days as $row)
                                                                        <option value="{{$row->id}}" @if(count($days_selected) > 0) @foreach($days_selected as $day) @if($day->day_id == $row->id) selected  @endif  @endforeach @endif>{{$row->day}}</option>
                                                                    @endforeach
                                                                @endif
                                                            </optgroup>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">Update</button>
                                        <!--<button type="button" class="btn btn-default" onclick="clearFields();">Clear</button>-->
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
</script>
@endsection