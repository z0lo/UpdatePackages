<br />
<div class="row marginBottom5">
	<div class="col-md-5">{vtranslate('LBL_NAME', $QUALIFIED_MODULE)}:</div>
	<div class="col-md-7">
		<input name="label" class="form-control" type="text" value="{if $RECORD}{$RECORD->get('label')}{/if}" data-validation-engine="validate[required]" />
	</div>
</div>
<div class="row marginBottom5">
	<div class="col-md-5">{vtranslate('LBL_JAVASCRIPT', $QUALIFIED_MODULE)}:</div>
	<div class="col-md-7">
		<textarea name="dataurl" class="form-control">{if $RECORD}{$RECORD->get('dataurl')}{else}javascript:{/if}</textarea>
	</div>
</div>
{include file='fields/Hotkey.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
