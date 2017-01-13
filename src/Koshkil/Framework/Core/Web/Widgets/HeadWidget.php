<?php
namespace Koshkil\Framework\Core\Web\Widgets;

use Koshkil\Framework\Core\Web\Widget;
use Koshkil\Framework\Core\Web\Support\Request;
use Koshkil\Framework\Support\Collection;

class HeadWidget extends Widget {

	protected $name="head";

	public function init(Request $request) {
		$parameters=Application::getWidgetParameters("head");
		$this->parameters=$parameters;
		$scripts=Application::get("scripts");
		$styles=Application::get("styles");

		if (Application::get("COMBINE_JS_CSS")===true) {
			$gzip = strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip');
			$deflate = strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate');

			$encoding = $gzip ? 'gzip' : ($deflate ? 'deflate' : 'none');

			// Check for buggy versions of Internet Explorer
			if (!strstr($_SERVER['HTTP_USER_AGENT'], 'Opera') &&
			preg_match('/^Mozilla\/4\.0 \(compatible; MSIE ([0-9]\.[0-9])/i', $_SERVER['HTTP_USER_AGENT'], $matches)) {
				$version = floatval($matches[1]);

				if ($version < 6)
					$encoding = 'none';

				if ($version == 6 && !strstr($_SERVER['HTTP_USER_AGENT'], 'EV1'))
					$encoding = 'none';
			}

			$coreLoader=$_SERVER["DOCUMENT_ROOT"]."/../core/autoload.php";
			$controller=Application::get("RUNNING_CONTROLLER");
			if (substr($controller,0,1)=="/") $controller=substr($controller,1);
			if (substr($controller,-1)=="/") $controller=substr($controller,0,-1);
			$controller=md5($controller);

			$combinedJScriptFile=Application::get("STORAGE_DIR")."/resources/views/{$controller}.js.php";
			$jsFile="<?php\n";
			$jsFile.="use Koshkil\Framework\Support\Minify;";
			$jsFile.="require_once(\"{$coreLoader}\");\n";
			$jsFile.="header('Content-Type: text/javascript');\n";

			$jsFile.="\$jsContent='';\n";
			$_scripts=$scripts;
			$_styles=$styles;
			$scripts=array();
			$styles=array();
			foreach($_scripts as $script) {
				if (substr($script,0,2)!="//" && substr($script,0,4)!="http")
					$jsFile.="\$jsContent.=Minify::processJs(\$_SERVER['DOCUMENT_ROOT'].'{$script}');\n";
				else
					$scripts[]=$script;
			}

			if (isset($encoding) && $encoding != 'none')
			{
				// Send compressed contents
				$jsFile.='$jsContent = gzencode($jsContent, 9, '.($gzip ? 'FORCE_GZIP' : 'FORCE_DEFLATE').');';
				$jsFile.="header (\"Content-Encoding: {$encoding}\");\n";
				$jsFile.="header ('Content-Length: ' . strlen(\$jsContent));\n";
			}
			else
			{
				// Send regular contents
				$jsFile.="header ('Content-Length: ' . strlen(\$jsContent));\n";
			}



			$jsFile.="echo \$jsContent;\n";
			file_put_contents($combinedJScriptFile, $jsFile);
			$scripts[]=Application::getLink("/resources/templates_c/{$controller}.js.php");

			// Combining Styles
			$combinedCSStyleFile=$_SERVER["DOCUMENT_ROOT"].Application::getLink("/resources/templates_c/{$controller}.css.php");
			$cssFile="<?php\n";
			$jsFile.="use Koshkil\Framework\Support\Minify;";
			$cssFile.="require_once(\"{$coreLoader}\");\n";
			$cssFile.="header('Content-Type: text/css');\n";
			$cssFile.="\$cssContent='';\n";
			foreach($_styles as $style) {
				if (substr($style,0,2)!="//" && substr($style,0,4)!="http")
					$cssFile.="\$cssContent.=Minify::processCss(\$_SERVER['DOCUMENT_ROOT'].'{$style}');\n";
				else
					$styles[]=$style;
			}


			if (isset($encoding) && $encoding != 'none')
			{
				// Send compressed contents
				$cssFile.='$cssContent = gzencode($cssContent, 9, '.($gzip ? 'FORCE_GZIP' : 'FORCE_DEFLATE').');';
				$cssFile.="header (\"Content-Encoding: {$encoding}\");\n";
				$cssFile.="header ('Content-Length: ' . strlen(\$cssContent));\n";
			}
			else
			{
				// Send regular contents
				$cssFile.="header ('Content-Length: ' . strlen(\$cssContent));\n";
			}



			$cssFile.="echo \$cssContent;\n";
			file_put_contents($combinedCSStyleFile, $cssFile);
			$styles[]=Application::getLink("/resources/templates_c/{$controller}.css.php");
		}


		$this->view->assign("SERVER_NAME",$_SERVER["SERVER_NAME"]);

		$this->view->assign("scripts",$scripts);
		$this->view->assign("styles",$styles);
	}
}