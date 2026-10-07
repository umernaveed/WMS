<div class="col-md-6" style="border: 1px solid #eee;margin-right: 20px;padding: 0px;">
    <div class="card-heading border bottom">
        <h4 class="card-title permissio-title">Role Permissions : {{$roles_title}}</h4>
    </div>
    <ul id="listTwo" class="list" data-button-text="-">
        @if(count($role_modules) > 0)
            @foreach($role_modules as $role)
                <?php $permission = commonHelper::getPermission($role->id,$role_id); ?>
                @if($permission)
                    <li>
                        <button type="button" class="btn btn2 btn-success assign-btn" type1="un-assign" role="{{$role_id}}" users="{{$role->id}}"><i class=" ti-check pdd-right-0"></i></button>
                        {{$role->main_title}}
                    </li>
                @endif
            @endforeach
        @endif
    </ul>
</div>
<div class="col-md-5" style="border: 1px solid #eee;padding: 0px;">
    <div class="card-heading border bottom">
        <h4 class="card-title">Permissions</h4>
    </div>
    <ul id="listOne" class="list" data-button-text="+">
        @if(count($role_modules) > 0)
            @foreach($role_modules as $role)
                <?php $permission = commonHelper::getPermission($role->id,$role_id); ?>
                @if($permission == null)
                    <li>
                        <button type="button" class="btn btn1 btn-danger assign-btn" type1="assign" role="{{$role_id}}" users="{{$role->id}}"><i class=" ti-plus pdd-right-0"></i></button>
                        {{$role->main_title}}
                    </li>
                @endif
            @endforeach
        @endif
    </ul>
</div>
<script>
    $('.list button').click(function() {
        //alert('dddddd');
        var module_id = $(this).attr('users');
        var type = $(this).attr('type1');
        var role_id = $(this).attr('role');
        var $list = $(this).closest('.list');
        var $targetList = $('.list').not($list);
        //console.log($targetList);
        $(this).closest('li').appendTo($targetList);
        if(type == 'assign'){
            $(this).removeClass('btn1');
            $(this).removeClass('btn-danger');
            $(this).addClass('btn2');
            $(this).addClass('btn-succes');
            $(this).attr("type1", "un-assign");
            $(this).html('<i class=" ti-check pdd-right-0" style="color: #fff;"></i>');
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
            $(this).removeClass('btn2');
            $(this).addClass('btn1');

            $(this).removeClass('btn2');
            $(this).removeClass('btn-succes');
            $(this).addClass('btn1');
            $(this).addClass('btn-danger');
            $(this).attr("type1", "assign");
            $(this).html('<i class=" ti-plus pdd-right-0" style="color: #fff;"></i>');
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
</script>
