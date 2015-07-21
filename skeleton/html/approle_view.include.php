<?php
// Copyright (c) 2010-2014 Ronald B. Cemer
// All rights reserved.
// This software is released under the BSD license.
// Please see the accompanying LICENSE.txt for details.

include dirname(__FILE__).'/generated/approle_view_generated.include.php';

function afterHeaderViewHook() {
	global $ALL_PERM_NAMES, $ALL_PERM_DESCRIPTIONS_BY_PERM_NAMES, $RESERVED_ROLE_NAMES;
?>
<script type="text/javascript">
var ALL_PERM_NAMES = <?php echo json_encode($ALL_PERM_NAMES); ?>;
var ALL_PERM_DESCRIPTIONS_BY_PERM_NAMES = <?php echo json_encode($ALL_PERM_DESCRIPTIONS_BY_PERM_NAMES); ?>;
var RESERVED_ROLE_NAMES = <?php echo json_encode($RESERVED_ROLE_NAMES); ?>;
</script>
<?php
}

function emitPermissionsTable() {
?>
<div class="form-group" id="permsContainerTR">
 <label class="col-sm-2 control-label">Permissions for this Role:</label>
 <div class="col-sm-10">
  <table id="permsTable" border="0" cellspacing="2" cellpadding="0">
   <tbody id="permsTbody"></tbody>
  </table>
 </div>
</div>
<?php
}

function beforeFooterViewHook() {
	// Template rows for child tables
?>
<textarea id="cont_template_permsTR" style="display:none">
<tr><td><label for="perm_nameSelected_${perm_name|htmlencode}"><input type="checkbox" name="perm_nameSelected_${perm_name|htmlencode}" id="perm_nameSelected_${perm_name|htmlencode}" class="perm_nameSelected" value="1"/> ${permDescription|htmlencode}</label></td></tr>
</textarea>
<?php
}
