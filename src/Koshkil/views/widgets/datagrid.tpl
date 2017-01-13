<script type="text/javascript" src="{Application::getPath("/resources/js/lib/core.js")}"></script>
<script type="text/javascript" src="{Application::getPath("/resources/js/lib/jquery.dataTables.js")}"></script>
<div class="page-toolbar">
    
    <div class="page-toolbar-block">
        <div class="page-toolbar-title">{$title}</div>
        {if $subtitle}<div class="page-toolbar-subtitle">{$subtitle}</div>{/if}
    </div>                                                
    {if $breadcrumb}
    <ul class="breadcrumb">
    	{foreach $breadcrumb as $step}
        <li><a href="{if $step.link}{$step.link}{else}#{/if}">{$step.text}</a></li>
        {/foreach}
    </ul>                 
    {/if}
</div>                    
 
<div class="row">            
    <div class="col-md-12">
        
        <div class="block">
            <div class="block-head datatable-header">
                <h2>{$title}</h2>
            </div>
            <div class="block-content np">
				<table id="{$tableId}" data-url="" cellpadding="0" cellspacing="0" width="100%" class="table table-bordered table-striped sortable">
					<thead>
						{if $buttonsColumnWidth>0}
						<th width="{$buttonsColumnWidth}">&nbsp;</th>
						{/if}
						{foreach $columns as $idx=>$column}
						<th{if $column.width} width="{$column.width}"{/if}>{$column.header}</th>
						{/foreach}
					</thead>
					<tbody></tbody>
				</table>            
            </div>
        </div>

    </div>

</div>
<script type="text/javascript">
var totalRecords=0;
// groupColumn : {$groupColumn}
$(document.body).ready(function() {
	$('#{$tableId}').dataTable({
		"oLanguage":oLanguage,
		"bProcessing": true,	
        "bServerSide": true,
		"bAutoWidth":false,
		"iDisplayLength": {$displayLength|default:50},
		 {if $noSort}"bSort":false,{/if}
		"aLengthMenu": [5,10,20,50,100],
		"sPaginationType": "full_numbers",
		{if $noSearch}"bFilter":false,{/if}
		{if $ajaxUrl}
		"sAjaxSource": "{$ajaxUrl}",
		{/if}
		{if $useData}
		"data": {$useData|json_encode},
		{/if}
		{if $groupColumn && $useGrouping}
		"fnDrawCallback": function ( oSettings ) {
			if ( oSettings.aiDisplay.length == 0 ) {
                return;
            }
             
            var nTrs = $('#{$tableId} tbody tr');
            var iColspan = nTrs[0].getElementsByTagName('td').length;
            var sLastGroup = "";
            for ( var i=0 ; i<nTrs.length ; i++ )
            {
                var iDisplayIndex = oSettings._iDisplayStart + i;
                var sGroup = oSettings.aoData[ oSettings.aiDisplay[iDisplayIndex] ]._aData[{$groupColumn}];
                if ( sGroup != sLastGroup )
                {	
                    var nGroup = document.createElement( 'tr' );
                    var nCell = document.createElement( 'td' );
                    nCell.colSpan = iColspan;
                    nCell.innerHTML = '<div>'+sGroup+'</div>';
                    nGroup.appendChild( nCell );
                    nTrs[i].parentNode.insertBefore( nGroup, nTrs[i] );
                    sLastGroup = sGroup;
                }
            }
        },
        {/if}
		"fnCreatedRow": function( nRow, aData, iDataIndex ) {
			var theRowHTML=$(nRow).html();
			{if $buttonsColumnWidth>0}
			var controlCell='<td style="width:{$buttonsColumnWidth}px;" width="{$buttonsColumnWidth}" class="">';
			{if Application::$page->meetRules(Application::$page->rules,UserRules::UPDATE)}
			controlCell+='<button title="Editar registro" class="grid-update glyphicon glyphicon-edit btn btn-primary btn-xs" data-action="upd" data-record-id="'+aData[aData.length-1]+'" onclick="{$actionToCall}(this)"></button>';
			{/if}
			{if Application::$page->meetRules(Application::$page->rules,UserRules::DELETE)}
			controlCell+='<button title="Eliminar registro" class="grid-delete glyphicon glyphicon-floppy-remove btn btn-danger btn-xs" data-action="del" data-record-id="'+aData[aData.length-1]+'" onclick="{$actionToCall}(this)"></button>';
			{/if}
			{if $indexable}
			controlCell+='<button title="Subir" class="grid-update glyphicon glyphicon-arrow-up btn btn-primary btn-xs" data-action="iup" data-record-id="'+aData[aData.length-1]+'" onclick="{$actionToCall}(this)"></button>';
			controlCell+='<button title="Bajar" class="grid-update glyphicon glyphicon-arrow-down btn btn-primary btn-xs" data-action="idn" data-record-id="'+aData[aData.length-1]+'" onclick="{$actionToCall}(this)"></button>';
			{/if}
			{else}
			var controlCell='';
			{/if}
			theRowHTML=controlCell+theRowHTML;
			$(nRow).html(theRowHTML);
			{foreach $columns as $idx=>$column}
			{if $buttonsColumnWidth==0}
			{assign var="baseColumn" value=$idx}
			{else}
			{assign var="baseColumn" value=$idx+1}
			{/if}
			{if $column.formater}
			{if $column.formater.class=="combo"}
			drawCombo($('td:eq({$baseColumn})', nRow),{$column|json_encode},aData[{$idx}],aData[aData.length-1]);
			{/if}
			{if $column.formater.class=="custom"}
			{$column.formater.function}($('td:eq({$baseColumn})', nRow),{$column|json_encode},aData[{$idx}],aData[aData.length-1]);
			{/if}
			{if $column.formater.align=="right"}
			$('td:eq({$baseColumn})', nRow).attr("align","right").css({
				'text-align':'right !important',
				'padding-right':'5px !important'
			});
			{/if}
			{/if}
			{/foreach}
		},
		{if $ajaxUrl}
	    "fnServerData": function ( sSource, aoData, fnCallback, oSettings ) {
		     oSettings.jqXHR =  $.ajax( {
		        "dataType": 'json',
		        "type": "GET",
		        "url": sSource,
		        "data": aoData,
		        "success": function(data) { 
		        	totalRecords=data.iTotalRecords;
		        	fnCallback(data); 
		        	$(".page-content").mCustomScrollbar("update");
		        	{if Application::$page->meetRules(Application::$page->rules,UserRules::INSERT) && $formUrl}
					if ($("{$parent}.block .datatable-header").length!=0) { 
			        	if ($("{$parent}.block .datatable-header button").length==0) {
			        		$("{$parent}.block .datatable-header h2").addClass("pull-left");
							$("<button/>").attr("data-action","add").addClass("btn btn-primary pull-right").css({
								"cursor": "pointer",
								"margin-right":"5px",
								"margin-top":"5px"						        			
			        		}).html("Agregar").click(function() {
			        			{if $actionToCall=="doAction"}
								var url="{$formUrl}";
								if (url.indexOf("?")==-1)
									url+="?";
								else
									url+="&";
								url+="action=add";
								location.href=url;
			        			{else}
			        			{$actionToCall}(this);
			        			{/if} 
			        		}).appendTo($("{$parent}.block .datatable-header"));
			        		{foreach $extraButtons as $button}
			        		$("<button/>").attr("data-action","add").addClass("btn btn-primary pull-right").css({
								"cursor": "pointer",
								"margin-right":"5px",
								"margin-top":"5px"						        			
			        		}).html("'.$button["text"].'").click(function() { 
			        			{$button.action}(this); 
			        		}).appendTo($("{$parent}.block .datatable-header"));
			        		{/foreach}
			        	}
			        }
		        	{/if}
		        }
			});
		},
		{/if}
		"aoColumns": [
			{if $groupColumn!=0 || !$useGrouping}
			{literal}{"bSortable": true,bVisible:true },{/literal}
			{else}
			{literal}{bVisible:false },{/literal}
			{/if}
			{foreach $columns as $idx=>$column}
			{if $idx>=1}
			{if $groupColumn==$idx}
			{literal}{bVisible:false },{/literal}
			{else}
			{literal}{bVisible:true },{/literal}
			{/if}
			{/if}
			{/foreach}
		]
	});
});
function doAction(obj) {
	var dataAction=$(obj).attr("data-action");
	var dataRecordId=$(obj).attr("data-record-id");
	var url="{$formUrl}";
	if (url.indexOf("?")==-1)
		url+="?";
	else
		url+="&";
	url+="action="+dataAction+"&id="+dataRecordId;
	location.href=url;
}
</script>