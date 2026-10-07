<style>
    .modal-dialog {
        max-width: 1000px;
    }
    /* Formatting search box */
    
    .search-box input[type="text"] {
        padding: 6px 12px;
        border: 1px solid #e2e2e4;
        font-size: 14px;
        border-radius: 4px;
    }
    .result {
        position: absolute;
        z-index: 999;
        left: 15px;
        max-height: 290px;
        overflow: hidden;
        overflow-y: scroll;
        background: #fff;
        display:none;
    }
    .search-box input[type="text"], .result{
        /* width: 350px; */
        box-sizing: border-box;
    }
    /* Formatting result items */
    .result p{
        margin: 0;
        padding: 7px 10px;
        border: 1px solid #CCCCCC;
        border-top: none;
        cursor: pointer;
        width: 350px;
    }
    .result p:hover{
        background: #f2f2f2;
    }
</style>
@if(count($users) > 0)
    @foreach($users as $row)
        <p class="member-list" mailbox='{{$row->mailbox.'+'.$row->first_name.' '.$row->last_name.'+'.$row->email}}' user_id='{{$row->user_id}}'>{{$row->first_name.' '.$row->last_name.' Account no: '.$row->mailbox}}</p>
    @endforeach
@else
    <p class="member-list">No matches found</p>
@endif
<script type="text/javascript">
    $('.search-box input[type="text"]').on("keypress input", function(){
        /* Get input value on change */
        var inputVal = $(this).val();
        var courier_id = $('#courier_user_id').val();
        var strCount = inputVal.length;
        if(strCount > 2){
            //$('.member-list').html('Searching Users ...');
            var resultDropdown = $(this).siblings(".result");
            if(inputVal.length){
                $.get(APP_URL+'/get-members/'+courier_id+'/'+inputVal, {}).done(function(data){
                    // Display the returned data in browser
                    resultDropdown.html(data);
                });
            } else{
                resultDropdown.empty();
            }
        }else{
            $('.result').html('<p class="member-list">Enter at least <b>3 characters</b></p>');
        }
    });
    
    $(document).on("click", ".result p", function(){
        var mailbox = $(this).attr('mailbox');
        $('#mailbox').val(mailbox);
        $(this).parents(".search-box").find('input[type="text"]').val($(this).text());
        $(this).parent(".result").empty();
        //getUserOrders(uoe_no);
        //$('#search_school').submit();
    });
</script>