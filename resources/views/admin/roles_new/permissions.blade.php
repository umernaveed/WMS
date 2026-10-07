@extends('admin.template')
@section('content')
<style>
    .side-nav-menu-permission {
        list-style: none;
        padding-left:0px;
    }
    .nav-item-permission{
        padding: 5px 20px;
    }
    #listOne {
        padding: 0px;
        list-style: none;
    }

    #listTwo {
        padding: 0px;
        list-style: none;
    }
    .list li {
        padding: 10px 20px;
        position: relative;
        border-bottom: 1px solid #eee;
    }
    .btn1 {
        padding: 0px;
        border-radius: 34px;
        font-size: 20px;
        font-weight: bold;
        width: 30px;
        height: 30px;
        background: #f00;
        margin: 0;
    }
    .btn2 {
        padding:0px;
        border-radius: 34px;
        font-size: 20px;
        font-weight: bold;
        width: 30px;
        height: 30px;
        background: #00A300;
        border:1px solid #00A300;
        margin: 0;
    }
/*    .active {
        background:#eee;
    }*/
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
                                <div class="col-md-3" style="border: 1px solid #eee;margin-right: 20px;padding: 0px;">
                                    <div class="card-heading border bottom">
                                        <h4 class="card-title">Roles</h4>
                                    </div>
                                    <ul class="side-nav-menu-permission scrollable">
                                        @if(count($roles) > 0)
                                            @foreach($roles as $row)
                                                <li class="nav-item-permission" label="{{$row->title}}" role_id="{{$row->role_id}}">
                                                    <a style=" color:#F00" class="" href="javascript:void(0)">
                                                        <span class="title">{{$row->title}}</span>
                                                    </a><br>
                                                    <a class="" href="javascript:void(0)">
                                                        <span class="title">{{$row->lable}}</span>
                                                    </a>
                                                </li>
                                            @endforeach
                                        @endif
                                    </ul>
                                </div>
                                <div class="row permission-list col-md-8">
                                    <div class="col-md-6" style="border: 1px solid #eee;margin-right: 20px;padding: 0px;">
                                        <div class="card-heading border bottom">
                                            <h4 class="card-title permissio-title">Select Role to assign permission!</h4>
                                        </div>
                                        <ul id="listTwo" class="list" data-button-text="-">
                                        </ul>
                                    </div>
                                    <div class="col-md-5" style="border: 1px solid #eee;padding: 0px;">
                                        <div class="card-heading border bottom">
                                            <h4 class="card-title">Permissions</h4>
                                        </div>
                                        <ul id="listOne" class="list" data-button-text="+">
                                            @if(count($role_modules) > 0)
                                                @foreach($role_modules as $role)
                                                    <li>
                                                        <button type="button" class="btn btn1 btn-danger assign-btn" type1="assign" role="" users="{{$role->id}}" disabled=""><i class=" ti-plus pdd-right-0"></i></button>
                                                        {{$role->main_title}}
                                                    </li>
                                                @endforeach
                                            @endif
                                        </ul>
                                    </div>
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
        $('#name').val('');
        $('#url').val('');
    }
    $('#price').bind('keyup paste', function(){
        this.value = this.value.replace(/[^0-9]/g, '');
    });
    $('#no_of_days').bind('keyup paste', function(){
        this.value = this.value.replace(/[^0-9]/g, '');
    });
    $('.list button').click(function() {
        var module_id = $(this).attr('users');
        var type = $(this).attr('type1');
        var role_id = $(this).attr('role');
        //alert(users);

        var $list = $(this).closest('.list');
        var $targetList = $('.list').not($list);
        $(this).closest('li').appendTo($targetList);
        $(this).text($targetList.data('button-text'));
        if(type == 'assign'){
            $(this).attr("type1", "un-assign");
            //ajax-call
            $.ajax({
                type: 'GET',
                url: APP_URL+'/admin/assign/role/'+module_id+'/'+role_id,
                data: '',
                processData: false,
                contentType: false,
                //async:false,
                success: function (msg) {

                }
            });
        }else{
            $(this).attr("type1", "assign");
            $.ajax({
                type: 'GET',
                url: APP_URL+'/admin/unassign/role/'+module_id+'/'+role_id,
                data: '',
                processData: false,
                contentType: false,
                //async:false,
                success: function (msg) {

                }
            });
        }
    });

    $('.nav-item-permission').click(function() {
        var label = $(this).attr('label');
        var role_id = $(this).attr('role_id');
        $('.nav-item-permission').removeClass('active');
        $(this).addClass('active');
        var type = $(this).attr('type1');
        $('.permissio-title').html('Role Permissions :'+label);
        $('.assign-btn').removeAttr("disabled");
        $('.assign-btn').attr("role", role_id);
         $.ajax({
            type: 'GET',
            url: APP_URL+'/admin/role/permission/'+role_id,
            data: '',
            processData: false,
            contentType: false,
            //async:false,
            success: function (msg) {
                $('.permission-list').html(msg);
            }
        });
    });
</script>
@endsection
