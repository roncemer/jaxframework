<?php
// Copyright (c) 2010-2012 Ronald B. Cemer
// All rights reserved.
// This software is released under the BSD license.
// Please see the accompanying LICENSE.txt for details.

include dirname(__FILE__).'/generated/apppageuri_view_generated.include.php';

function afterHeaderViewHook() {
	global $ALL_PERM_NAMES, $ALL_PERM_DESCRIPTIONS_BY_PERM_NAMES, $RESERVED_PAGE_URIS;
?>
<script type="text/javascript">
var ALL_PERM_NAMES = <?php echo json_encode($ALL_PERM_NAMES); ?>;
var ALL_PERM_DESCRIPTIONS_BY_PERM_NAMES = <?php echo json_encode($ALL_PERM_DESCRIPTIONS_BY_PERM_NAMES); ?>;
var RESERVED_PAGE_URIS = <?php echo json_encode($RESERVED_PAGE_URIS); ?>;
</script>
<?php
}

function emitPermissionsTable() {
?>
<div class="control-group" id="permsContainerTR">
 <label class="control-label">Permissions required to access this Page:</label>
 <div class="controls">
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
<label for="perm_nameSelected_${perm_name|htmlencode}" class="checkbox"><input type="checkbox" name="perm_nameSelected_${perm_name|htmlencode}" id="perm_nameSelected_${perm_name|htmlencode}" class="perm_nameSelected" value="1"/> ${permDescription|htmlencode}</label>
</textarea>
<?php
}
