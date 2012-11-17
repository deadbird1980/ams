
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
        <?php include Doo::conf()->SITE_PATH .  Doo::conf()->PROTECTED_FOLDER . "viewc//header.php"; ?>
    </head>
	<body>
      <?php include Doo::conf()->SITE_PATH .  Doo::conf()->PROTECTED_FOLDER . "viewc//nav.php"; ?>
	  <table>

        	<tbody><tr>

        		<td valign="top">

                </td>

                <td valign="top">

                    <div class="tab clearfix" style="display:block">

                            <h2>Login</h2>

                            <form name="form" id="form" action="<?php echo $data['baseurl']; ?>index.php/login" method="post" class="Zebra_Form">
                            <div class="hidden">
                                <input name="name_form" id="name_form" value="form" type="hidden">
                                <label for="zebra_honeypot_form" style="display:none">Leave this field blank</label>
                                <input name="zebra_honeypot_form" id="zebra_honeypot_form" value="" class="control text" autocomplete="off" type="text">
                                <input name="zebra_csrf_token_form" id="zebra_csrf_token_form" value="107ac2f6731db05af99212e353561cb7" type="hidden">
                            </div>
                            <div>
                                <span><?php echo $data['message']; ?></span>
                            </div>
                            <table>
                                <tbody>
                                    <tr class="row">
                                        <td>
                                            <label for="username" id="label_username">UserName<span class="required">*</span></label>
                                        </td>
                                        <td>
                                            <input name="username" id="unsername" value="" class="control text validate[required,email]" autocomplete="off" type="text">
                                        </td>
                                    </tr>
                                    <tr class="row even">
                                        <td>
                                            <label for="password" id="label_password">Password<span class="required">*</span></label>
                                        </td>
                                        <td>
                                            <input name="password" id="password" value="" class="control password validate[required,length(6,10)]" autocomplete="off" maxlength="10" type="password">
                                        </td>
                                    </tr>
                                    <tr class="row">
                                        <td></td>
                                        <td>
                                            <div class="cell"><input name="remember_me" id="remember_me_yes" value="yes" class="control checkbox" type="checkbox"></div>
                                            <div class="cell">
                                                <label for="remember_me_yes" id="label_remember_me_yes" style="font-weight:normal" class="option">Remember me</label>
                                            </div><div class="clear"></div></td></tr><tr class="row even last"><td></td><td><input name="btnsubmit" id="btnsubmit" value="Submit" class="submit" type="submit">
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </form>
                            <script type="text/javascript">
                                function init() {
                                    if(typeof jQuery=="undefined"||typeof jQuery.fn.Zebra_Form=="undefined") {
                                        setTimeout("init()",100);
                                        return}$("#form").Zebra_Form({scroll_to_error:true,tips_position:'left',close_tips:true,validate_on_the_fly:false,validate_all:false,error_messages:{"email":{"required":"Email is required!","email":"Email address seems to be invalid!"},"password":{"required":"Password is required!","length":"The password must have between 6 and 10 characters!"}}})
                                        }init()
                                    </script>
                    </div>


                </td>

        	</tr>

        </tbody>
    </table>
	</body>
</html>
