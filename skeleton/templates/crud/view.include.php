<?php
{{generatedFileMessage}}
{{viewHooksInclude}}
if ((!isset($viewOk)) || (!$viewOk)) exit();
$headerStylesheets = array(
{{if_searchPresentation_dataTables}}
	'{{jaxJQuery}}/css/datatables/datatables.css'
{{/if_searchPresentation_dataTables}}
);
{{cssFiles}}
$headerScripts = array(
	'{{jaxJS}}/baseurl.js',
	'{{jaxJS}}/cache.js',
{{if_searchPresentation_dataTables}}
	'{{jaxJS}}/dataTablesSupport.js',
{{/if_searchPresentation_dataTables}}
	'{{jaxJS}}/date.js',
	'{{jaxJS}}/htmlencode.js',
	'{{jaxJS}}/pageHelp.js',
	'{{jaxJS}}/parsemsgs.js',
	'{{jaxJS}}/popupSearch.js',
	'{{jaxJS}}/replaceTokens.js',
	'{{jaxJS}}/specialFieldFeatures.js',
	'{{jaxJS}}/toggleVis.js',
{{if_searchPresentation_dataTables}}
	'{{jaxJQuery}}/js/jquery.dataTables.min.js',
{{/if_searchPresentation_dataTables}}
);
{{javaScriptFiles}}
{{controllerIncludes}}
include {{docRootPath}}.'/include/header.include.php';
?>

<?php if (function_exists('afterHeaderViewHook')) afterHeaderViewHook(); ?>

<h2 id="{{tableName}}TableDescriptionHeading"><?php _e('crud.{{crudName}}.tableDescriptions', '{{tableDescriptions}}'); ?></h2>

<div id="errorMsg" class="errorMsg"></div>
<div id="successMsg" class="successMsg"></div>

<div id="existing{{uTableName}}sCont">
<?php if (function_exists('searchBlock1ViewHook')) searchBlock1ViewHook(); ?>
 <h3 id="{{tableName}}SearchModeHeading"><?php echo str_replace('{tableDescriptionsPlaceholder}', _t('crud.{{crudName}}.tableDescriptions', '{{tableDescriptions}}'), _t('crud.crudSearchModeTitleBase')); ?></h3>
<?php if (function_exists('searchBlock2ViewHook')) searchBlock2ViewHook(); ?>
 <a id="add{{uTableName}}Link" href="#" title="<?php echo str_replace('{tableDescriptionPlaceholder}', _t('crud.{{crudName}}.tableDescription', '{{tableDescription}}'), _t('crud.crudAddLinkTitleBase')); ?>" class="btn btn-default" onclick="add{{uTableName}}(); return false;"><i class="glyphicon glyphicon-plus-sign"></i> <?php echo str_replace('{tableDescriptionPlaceholder}', _t('crud.{{crudName}}.tableDescription', '{{tableDescription}}'), _t('crud.crudAddLinkTitleBase')); ?></a><br/>
<?php if (function_exists('searchBlock3ViewHook')) searchBlock3ViewHook(); ?>
{{if_searchPresentation_dataTables}}
<script type="text/javascript">
document.write(getDataTableHTML('{{tableName}}sTable', {{tableName}}sDataTable_aoColumnDefs));
</script>
{{/if_searchPresentation_dataTables}}
{{if_searchPresentation_AJAXSearchGrid}}
 <div id="{{tableName}}sSearchGridCont" ng-app="JaxGridApp" ng-controller="Controller">
  <div class="jax-grid-pager" has-search-box has-search-by></div>
  <table>
   <thead>
    <tr>
{{crudSearchGridHeaderColumnsHTML}}
     <th><?php _e('crud.crudSearchActionsHeader'); ?></th>
    </tr>
   </thead>
   <tbody>
     <tr ng-repeat="i in getRowIndexes()" ng-class-odd="'odd'" ng-class-even="'even'">
{{crudSearchGridBodyColumnsHTML}}
      <td ng-bind-html="toTrustedHTML(getSearchActionColumnHTML(rows[i]))"></td>
     </tr>
    </tbody>
   </table>
  <div class="jax-grid-pager" has-search-box has-search-by></div>
 </div> <!-- {{tableName}}sSearchGridCont -->
{{/if_searchPresentation_AJAXSearchGrid}}
<?php if (function_exists('searchBlock4ViewHook')) searchBlock4ViewHook(); ?>
</div> <!-- existing{{uTableName}}sCont -->

<div id="{{tableName}}FormCont">

<?php if (function_exists('formBlock1ViewHook')) formBlock1ViewHook(); ?>

 <form name="{{tableName}}Form" id="{{tableName}}Form" class="form-horizontal" role="form" method="POST" enctype="multipart/form-data" target="submitIframe" onsubmit="return submittingForm;">
  <input type="hidden" name="command" value=""/>

<?php if (function_exists('formBlock2ViewHook')) formBlock2ViewHook(); ?>

{{hiddenFormFields}}

<?php if (function_exists('formBlock3ViewHook')) formBlock3ViewHook(); ?>

 <h3 id="{{tableName}}FormModeDisplay"></h3>

<?php if (function_exists('formBlock4ViewHook')) formBlock4ViewHook(); ?>

{{formFields}}

<?php if (function_exists('formBlock5ViewHook')) formBlock5ViewHook(); ?>

  <div id="crudFormButtons">
   <?php if (function_exists('crudFormButtonsPreHook')) crudFormButtonsPreHook(); ?>
   <input type="button" name="save{{uTableName}}Button" id="save{{uTableName}}Button" onclick="save{{uTableName}}();" value=""/>
   <input type="button" name="abandon{{uTableName}}Button" id="abandon{{uTableName}}Button" onclick="abandon{{uTableName}}();" value=""/>
   <?php if (function_exists('crudFormButtonsPostHook')) crudFormButtonsPostHook(); ?>
  </div>

<?php if (function_exists('formBlock6ViewHook')) formBlock6ViewHook(); ?>
 </form>

<?php if (function_exists('formBlock7ViewHook')) formBlock7ViewHook(); ?>

</div> <!-- {{tableName}}FormCont -->

<iframe name="submitIframe" src="about:blank" style="display:none; width:0px; height:0px"></iframe>

<?php if (function_exists('beforeFooterViewHook')) beforeFooterViewHook(); ?>

<?php include {{docRootPath}}.'/include/footer.include.php'; ?>
