<?php
// Copyright (c) 2010-2012 Ronald B. Cemer
// All rights reserved.
// This software is released under the BSD license.
// Please see the accompanying LICENSE.txt for details.

include dirname(__FILE__).'/generated/appperm_view_generated.include.php';

function afterHeaderViewHook() {
	global $RESERVED_PERM_NAMES;
?>
<script type="text/javascript">
var RESERVED_PERM_NAMES = <?php echo json_encode($RESERVED_PERM_NAMES); ?>;
</script>
<?php
}
