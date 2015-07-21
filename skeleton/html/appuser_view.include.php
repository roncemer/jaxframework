<?php
// Copyright (c) 2010-2012 Ronald B. Cemer
// All rights reserved.
// This software is released under the BSD license.
// Please see the accompanying LICENSE.txt for details.

include dirname(__FILE__).'/generated/appuser_view_generated.include.php';

function afterHeaderViewHook() {
	global $ALL_ROLE_NAMES, $ALL_ROLE_DESCRIPTIONS_BY_ROLE_NAMES, $MAX_RESERVED_ID;
?>
<script type="text/javascript">
var ALL_ROLE_NAMES = <?php echo json_encode($ALL_ROLE_NAMES); ?>;
var ALL_ROLE_DESCRIPTIONS_BY_ROLE_NAMES = <?php echo json_encode($ALL_ROLE_DESCRIPTIONS_BY_ROLE_NAMES); ?>;
var MAX_RESERVED_ID = <?php echo json_encode($MAX_RESERVED_ID); ?>;
</script>
<?php
}

function emitRolesTable() {
?>
<div class="form-group" id="rolesContainerTR">
 <label class="col-sm-2 control-label">User Roles:</label>
 <div class="col-sm-10">
  <table id="rolesTable" border="0" cellspacing="2" cellpadding="0">
   <tbody id="rolesTbody"></tbody>
  </table>
 </div>
</div>
<?php
}

function beforeFooterViewHook() {
	// Template rows for child tables
?>
<textarea id="cont_template_rolesTR" style="display:none">
<tr><td><label for="role_nameSelected_${role_name|htmlencode}"><input type="checkbox" name="role_nameSelected_${role_name|htmlencode}" id="role_nameSelected_${role_name|htmlencode}" class="role_nameSelected" value="1"/> ${roleDescription|htmlencode}</label></td></tr>
</textarea>
<?php
}
