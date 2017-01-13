{if $form_scripts}
{foreach $form_scripts as $script}
<script type="text/javascript" src="{$script}"></script>
{/foreach}
{/if}
<div class="page-toolbar">
    <div class="page-toolbar-block">
		<div class="page-toolbar-title">{$title}</div>
		<div class="page-toolbar-subtitle">{$subtitle}</div>
	</div>                                                
	<div class="page-toolbar-block pull-right">
		<div class="widget-info widget-from">
			<button class="btn btn-success"><i class="fa fa-floppy-o"></i> {$SAVE_TEXT}</button>                            
			<button class="btn btn-danger" onclick="location.href='{$BACK_TO}';"><a href="{$BACK_TO}"><i class="fa fa-power-off"></i> Cancelar</a></button>
		</div>
	</div>
</div>                    
<div class="row">
	<div class="col-md-12">
		<div class="block">
			<div class="block-content controls">
			{$fields}
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
$(document.body).ready(function() {
	{if !is_null($globalInit)}
	{$globalInit}();
	{/if}
	$(".block-content.controls").createForm({
		formContainer: ".block-content.controls",
		elements:"input,textarea,select",
		submitURL:"{$submitUrl}",
		usePrompt:false,
		submitByAjax:false,
		submitElement:".btn-success",
		hasAttachments:{if $hasAttachments}true{else}false{/if},
		globalValidator:{$globalValidator},
		submitSuccessCallBack:{$submitSuccessCallBack},
		validators:{$validators}
	});				
});				
</script>
		