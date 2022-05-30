<style type="text/css">
    body{
        overflow: hidden !important;
    }
    body,
    body *{
        box-sizing:border-box;
        -webkit-box-sizing:border-box;
        -o-box-sizing:border-box;
        -moz-box-sizing:border-box;
    }
    .arm_update_page_wrapper{
        float:left;
        display:block;
        z-index:999999;
        background:#f1f1f1;
        height:100%;
        width:100%;
        position:fixed;
        top:0;
        left:0;
        padding:30px;
    }
    .arm_update_title{
        float:left;
        width:100%;
        font-family:NotoSans, sans-serif, "Trebuchet MS";
        font-size:30px;
        text-align:center;
        font-weight: bold;
    }
    .arm_update_note{
        float:left;
        padding:5px 0;
        margin-top:20px;
        width:100%;
        text-align: center;
        font-family: NotoSans, sans-serif, "Trebuchet MS";
        font-size:24px;
        color:#ff0000;
    }
    .arm_update_container{
        float:left;
        width:100%;
        padding:40px;
        margin-top:20px;
    }
    .arm_progress_wrapper{
        float:none;
        width:80%;
        display:block;
        margin:5px auto;
        height:30px;
        border:1px solid #ccc;
        border-radius:3px;
        -webkit-border-radius:3px;
        -o-border-radius:3px;
        -moz-border-radius:3px;
        overflow: hidden;
    }
    .arm_inner_progress{
        float:left;
        width:7%;
        display: inline-block;
        height: 30px;
        box-shadow: 0 1px 0 rgba(255, 255, 255, 0.5) inset;
        -webkit-box-shadow: 0 1px 0 rgba(255, 255, 255, 0.5) inset;
        -moz-box-shadow: 0 1px 0 rgba(255, 255, 255, 0.5) inset;
        -o-box-shadow: 0 1px 0 rgba(255, 255, 255, 0.5) inset;
        transition: width 0.9s ease-in-out;
        -webkit-transition: width 0.9s ease-in-out;
        -moz-transition: width 0.9s ease-in-out;
        -ms-transition: width 0.9s ease-in-out;
        -o-transition: width 0.9s ease-in-out;
        background-color: #149bdf;
        background-size: 30px 30px;
        background-image: linear-gradient(135deg, rgba(255, 255, 255, 0.15) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, 0.15) 50%, rgba(255, 255, 255, 0.15) 75%, transparent 75%, transparent);
        background-image: -webkit-linear-gradient(135deg, rgba(255, 255, 255, 0.15) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, 0.15) 50%, rgba(255, 255, 255, 0.15) 75%, transparent 75%, transparent);
        animation: animate-stripes 3s linear infinite;
        -webkit-animation: animate-stripes 3s linear infinite;
        -moz-animation: animate-stripes 3s linear infinite;
        -o-animation: animate-stripes 3s linear infinite;
        font-size: 18px;
        color: #ffffff;
        line-height: 30px;
        text-align: center;
    }
    @keyframes animate-stripes {
        0% {
            background-position: 0 0;
        }
        100% {
            background-position: 60px 0;
        }
    }

    @-webkit-keyframes animate-stripes {
        0% {
            background-position: 0 0;
        }
        100% {
            background-position: 60px 0;
        }
    }
    .arm_progress_percentage{
        float:none;
        width:80%;
        display:block;
        margin:10px auto 20px;
        font-size:16px;
    }
    .arm_progress_percentage{
        
    }
    .arm_update_message_text{
        float:none;
        display: none;
        width:80%;
        display:block;
        margin:10px auto;
        text-align:left;
        font-family: NotoSans, sans-serif, "Trebuchet MS";
        font-size:16px;
    }
    .arm_update_redirect_button{
        float:left;
        width:100%;
        height:auto;
        padding:20px;
        text-align:center;
        display:none;
    }
    .arm_redirect_button{
        float:none;
        display: inline-block;
        color:#ffffff;
        background: #4CAF50;
        border:1px solid #4CAF50;
        border-radius:3px;
        -webkit-border-radius:3px;
        -o-border-radius:3px;
        -moz-border-radius:3px;
        cursor: pointer;
        width:auto;
        height:auto;
        padding:10px 20px 12px;
        font-size:20px;
        box-shadow: none;
        outline: none;
    }
</style>
<div class="arm_update_page_wrapper">
    <div class="arm_update_title">Updating ARMember</div>
    <div class="arm_update_note">
        <?php _e('Do not refresh/leave this page until plugin updated successfully.', 'ARMember'); ?>
    </div>
    <div class="arm_update_container">
        <div class="arm_progress_wrapper">
            <div id='arm_inner_progress' class="arm_inner_progress"></div>
        </div>
        <div class="arm_progress_percentage">7%</div>
        <div id="arm_progress_message_wrapper" class="arm_progress_message_wrapper">
        </div>
        <div class="arm_update_redirect_button">
            <button type="button" class="arm_redirect_button" onClick="<?php echo "window.location.href='".admin_url('admin.php?page=arm_manage_members')."';"; ?>" value=""><?php echo _e('Go to ARMember','ARMember'); ?></button>
        </div>
    </div>
</div>
<script type="text/javascript">
    jQuery(document).ready(function(){
        var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
        var ajaxObj = {
            url:ajaxurl,
            data:'action=arm_perform_update&page=arm_update_page',
            type:'POST',
            success:function(res){
                jQuery(".arm_progress_percentage").html('100%');
                jQuery(".arm_inner_progress").css('width','100%');
                jQuery('.arm_update_redirect_button').show();
            }
        };
        setTimeout(function(){
            jQuery.ajax(ajaxObj);
        },1500);
    });
</script>