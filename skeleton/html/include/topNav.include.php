   <div class="topNavTable" id="topNavTable">
	<div class="topNavTR">
	 <div class="topNavTD" id="topLogoCont">
	  Jax Framework
     </div>
	 <div class="topNavTD" id="topNavCont">
<?php
// These must be declared global, because sometimes this include file is included
// from within the inScriptPermissionsCheck() function in Permissions class.
global $loginRequired, $loggedInUser;
if (isset($loggedInUser) && ($loggedInUser !== null)) {
?>
Logged in as: <?php echo htmlspecialchars(trim($loggedInUser->first_name.' '.$loggedInUser->last_name)); ?>&nbsp;&nbsp;&nbsp;&nbsp;<a href="logout">Log out</a>
<?php } else { ?>
You are not logged in.
<?php } ?>
	 </div> <!-- topNavCont -->
	</div> <!-- topNavTR -->
   </div> <!-- topNavTable -->
