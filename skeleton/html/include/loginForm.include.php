<?php
if (!function_exists('loadResourceBundle')) include dirname(dirname(__FILE__)).'/jax/include/l10n.include.php';
loadResourceBundle(__FILE__);
?>
<form name="loginForm" id="loginForm" class="form-horizontal" method="POST">
 <div id="loginErrorMsg" class="errorMsg"><?php if (isset($__loginErrorMsg)) echo str_replace(array("\r\n", "\r", "\n"), array('<br/>', '<br/>', '<br/>'), htmlspecialchars($__loginErrorMsg)); ?></div>
 <h3><?php _e('loginForm.loginRequired'); ?></h3>
 <div class="form-group"><label for="loginUserName" class="col-sm-1 control-label"><?php _e('loginForm.loginUserName.label'); ?>:</label><div class="col-sm-10"><input type="text" name="loginUserName" id="loginUserName" maxlength="32" class="form-control" value="<?php if (isset($_REQUEST['loginUserName'])) echo htmlspecialchars($_REQUEST['loginUserName']); ?>"/></div></div>
 <div class="form-group"><label for="loginPassword" class="col-sm-1 control-label"><?php _e('loginForm.loginPassword.label'); ?>:</label><div class="col-sm-10"><input type="password" name="loginPassword" id="loginPassword" maxlength="32" class="form-control" value=""/></div></div>
 <div class="form-group"><label class="col-sm-1 control-label"></label><div class="col-sm-10"><label for="keepMeLoggedIn"><input type="checkbox" name="keepMeLoggedIn" id="keepMeLoggedIn" value="1"<?php if ((!isset($_REQUEST['keepMeLoggedIn'])) || (((int)trim($_REQUEST['keepMeLoggedIn'])) != 0)) echo ' checked="checked"'; ?>/> <?php _e('loginForm.keepMeLoggedIn.label'); ?></label></div></div>
 <div class="form-group"><label class="col-sm-1 control-label"></label><div class="col-sm-10"><input type="submit" name="loginSubmit" id="loginSubmit" class="btn btn-primary" value="<?php _e('loginForm.loginSubmit.label'); ?>"/></div></div>
</form>

<script type="text/javascript">
$(document).ready(function() {
<?php if ($__loginErrorMsg != '') { ?>
	$('#loginErrorMsg').show();
<?php } ?>
	$('#loginUserName').focus();
});
</script>
