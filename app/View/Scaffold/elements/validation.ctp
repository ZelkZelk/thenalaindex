<script>
    $(function(){        
       $("form#scaffold").find("#<?=$field?>_container").find('.portlet').addClass('has-error');
    });
</script>

 
<div class="row">
    <div class="col-md-12">
        <div class="note note-danger note-bordered">
                <p><?=$errors?></p>
        </div>
    </div>
</div>
