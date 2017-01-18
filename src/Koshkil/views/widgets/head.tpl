<head>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>{get("SYSTEM_TITLE")}</title>
	{foreach $styles as $style}
		<link rel="stylesheet" type="text/css" href="{$style}" />
	{/foreach}
	{foreach $scripts as $script}
		<script type="text/javascript" src="{$script}"></script>
	{/foreach}
	<script type="text/javascript">
		var MAIN_URL = '{asset('/')}';
	</script>
</head>