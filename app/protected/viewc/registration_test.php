
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <?php include Doo::conf()->SITE_PATH .  Doo::conf()->PROTECTED_FOLDER . "viewc//header.php"; ?>
    </head>
	<body>
        <table>
            <tbody><tr>
                <td valign="top">
                </td>

                <td valign="top">
                    <div class="tab clearfix" style="display:block">

                      <h2>Registration</h2>

                      <form name="form" id="form" action="<?php echo $data['baseurl']; ?>index.php/register" method="post" class="Zebra_Form">
                           <div class="hidden">
                               <input name="name_form" id="name_form" value="form" type="hidden">
                                   <label for="zebra_honeypot_form" style="display:none">Leave this field blank</label>
<input name="zebra_honeypot_form" id="zebra_honeypot_form" value="" class="control text" autocomplete="off" type="text">
<input name="zebra_csrf_token_form" id="zebra_csrf_token_form" value="53ff8aae947bd4d2c98dde2cb6d02479" type="hidden"></div>
<table><tbody>
        <tr class="row">
            <td><label for="unsername" id="label_username">UserName:<span class="required">*</span></label></td>
            <td><input name="username" id="unsername" value="joey" class="control text validate[required]" type="text"></td></tr>
        <tr class="row even">
            <td><label for="email" id="label_email">Email address:<span class="required">*</span></label></td>
            <td><input name="email" id="email" value="joeyw@reallyenglish.com" class="control text validate[required,email]" type="text">
                    <div class="note" id="note_email" style="width:200px">Please enter a valid email address. An email will be sent to this
    address with a link you need to click on in order to activate your account</div></td></tr>
        <tr class="row">
            <td><label for="password" id="label_password">Choose a password:<span class="required">*</span></label></td>
            <td><input name="password" id="password" value="asdfasdf" class="control password validate[required,length(6,10)]" maxlength="10" type="password">
                <div class="note" id="note_password">Password must be have between 6 and 10 characters.</div></td></tr>
        <tr class="row even"><td><label for="confirm_password" id="label_confirm_password">Confirm password:</label></td>
            <td><input name="confirm_password" id="confirm_password" value="asdfasdf" class="control password validate[compare(password)]" type="password"></td></tr>
        <tr class="row even">
            <td></td><td><img src="http://0.0.0.0/es/zebra/process.php?captcha=1&amp;nocache=1352933504" alt=""><label for="captcha_code" id="label_captcha_code">Are you human?</label><input name="captcha_code" id="captcha_code" value="" class="control text validate[captcha]" type="text"><div class="note" id="note_captcha" style="width: 200px">You must enter the characters with black color that stand
    out from the other characters</div></td></tr>
    <tr class="row last"><td></td>
        <td><input name="btnsubmit" id="btnsubmit" value="Submit" class="submit" type="submit"></td></tr></tbody></table>
                      </form>
                      <script type="text/javascript">function init_f4203791eba5d19407ff080f9022bb0f(){if(typeof jQuery=="undefined"||typeof jQuery.fn.Zebra_Form=="undefined"){setTimeout("init_f4203791eba5d19407ff080f9022bb0f()",100);return}$("#form").Zebra_Form({scroll_to_error:true,tips_position:'left',close_tips:true,validate_on_the_fly:false,validate_all:false,error_messages:{"firstname":{"required":"First name is required!"},"lastname":{"required":"Last name is required!"},"email":{"required":"Email is required!","email":"Email address seems to be invalid!"},"password":{"required":"Password is required!","length":"The password must have between 6 and 10 characters"},"confirm_password":{"compare":"Password not confirmed correctly!"},"captcha_code":{"captcha":"Characters from image entered incorrectly!"}}})}init_f4203791eba5d19407ff080f9022bb0f()</script>
                    </div>


                </td>

            </tr>

        </tbody></table>

	</body>
</html>
