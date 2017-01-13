<?php
namespace Koshkil\Framework\Core\Web\Widgets;

use Koshkil\Framework\Core\Web\Widget;
use Koshkil\Framework\Core\Web\Support\Request;
use Koshkil\Framework\Support\StringUtils;

class FormWidget extends Widget {

	private $form_scripts;

	protected function init(Request $request) {
		if (!$this->parameters["SAVE_TEXT"]) $this->parameters["SAVE_TEXT"]="Grabar";

		$this->form_scripts=Application::get("form_scripts");
		if (is_null($this->form_scripts)) $this->form_scripts=array();
		if (!in_array("/resources/js/admin/core.js",$this->form_scripts)) {
			$this->form_scripts[]=Application::getLink('/resources/js/lib/core.js');
		}
		if (!in_array("/resources/js/admin/jquery.forms.js",$this->form_scripts)) {
			$this->form_scripts[]=Application::getLink('/resources/js/lib/jquery.forms.js');
		}

		$html='';

		$st=new StringUtils();

		if (!$this->parameters["globalInit"])
			$this->parameters["globalInit"]=null;
		if (!$this->parameters["globalValidator"])
			$this->parameters["globalValidator"]="null";
		if (!$this->parameters["hasAttachments"])
			$this->parameters["hasAttachments"]=false;
		if (!$this->parameters["submitSuccessCallBack"])
			$this->parameters["submitSuccessCallBack"]="null";
		if (!$this->parameters["validators"])
			$this->parameters["validators"]="null";


		$html_fields=array();
		$hasImage=false;
		$hasDate=false;
		if ($this->parameters["tabs"]) {
			//dump_var($this->parameters["tabs"]);
			$tabsHtml='<ul class="nav nav-tabs">';
			$tabIdx=0;
			$tabsFields=array();
			foreach($this->parameters["tabs"] as $id=>$tab) {
				$classes=array();
				if ($tabIdx==0) $classes[]="active";
				else if ($tab["disabled"]===true) $classes[]="disabled";

				$tabsHtml.='<li id="tab_selector_'.$id.'"'.(!empty($classes)?' class="'.(implode(" ",$classes)).'"':'').'><a href="#'.$id.'" data-toggle="tab">'.$tab["title"].'</a></li>';
				$tabsFields[$id]='<div class="tab-pane'.($tabIdx==0?' active':'').'" id="'.$id.'">';
				foreach($tab["fields"] as $fieldId) {
					if (Application::$page->fields[$fieldId]["type"]=="date")
						$hasDate=true;
					$tabsFields[$id].=$this->processField($fieldId,Application::$page->fields[$fieldId]);
				}
				$tabsFields[$id].='</div>';
				$tabIdx++;
			}
			$tabsHtml.='</ul>';
			$html_fields[]=$tabsHtml;
			$html_fields[]='<div class="block-content tab-content">';
			foreach($tabsFields as $html) $html_fields[]=$html;
			$html_fields[]='</div>';
		} else {
			foreach(Application::$page->fields as $name=>$data) {
				if ($data["type"]=="date")
					$hasDate=true;
				$html_fields[]=$this->processField($name,$data);
			}
		}

		foreach($this->parameters as $key=>$value) {
			if (is_string($value) || is_numeric($value) || is_bool($value)) {
				$this->view->assign($key,$value);
			}
		}

		$this->view->assign("BACK_TO",Application::$page->urlBackTo);
		$this->view->assign("fields",implode("\n",$html_fields));
		$this->view->assign("form_scripts",$this->form_scripts);
		Application::set("form_scripts",$this->form_scripts);
	}

	function processField($name,$data) {
		$retVal="";
		extract($data);
		if ($data["type"]=="custom") {
			if (method_exists(Application::$page, $method)) {
				$retVal=call_user_func(array(Application::$page, $method),$name,$data);
			}
		} else {
			$funcName="form_field_{$data["type"]}";
			if ($data["type"]=="gallery") {
				if (!in_array("/resources/js/lib/jquery.galleryManager.js",$this->form_scripts)) {
					$this->form_scripts[]=Application::GetLink("/resources/js/lib/jquery.galleryManager.js");
					$hasImage=true;
				}
			}
			if ($data["type"]=="slider") {
				if (!in_array("/resources/js/lib/jquery.slider.js",$this->form_scripts)) {
					$this->form_scripts[]=Application::GetLink("/resources/js/lib/jquery.slider.js");
					$hasImage=true;
				}
			}
			if (method_exists($this,$funcName)) {
				$retVal=$this->{$funcName}($name,$data);
			} else
				$retVal="<!-- $funcName no existe! -->";
		}
		return $retVal;
	}
	function preprocess_template($type,$name,$field) {
		$templatesBaseDir=Application::getTemplatesDir();

		$widgetFile=$templatesBaseDir."/widgets/form/{$type}.tpl";
		if (file_exists($widgetFile)) {
			$html=FileWrapper::file_get_contents($widgetFile);
		} else {
			$html='';
		}
		$st=new stringUtils();

		foreach($field as $key=>$value) {
			if ($key=="value"){
				$value=utf8_encode($value);
			}

			if (is_string($value) || is_numeric($value)) {
				if (!in_array($key,array("width","readonly","maxlength","disabled","rows","cols")))
					$html=$st->replace_all("[[".$key."]]",$value,$html);
			}
		}
		$html=$st->replace_all("[[name]]",$name,$html);
		return $html;
	}

	function form_field_text($name,$field) {
		extract($field);
		$st=new stringUtils();

		$html=$this->preprocess_template("text",$name,$field);

		if (Application::$page->mode=="DELETE" || $readonly)
			$html=$st->replace_all("[[readonly]]",'readonly="readonly" ',$html);
		else
			$html=$st->replace_all("[[readonly]]",'',$html);

		if ($required || $format=="email" || $minlength) {
			if ($format=="email")
				$validator="email";
			else if ($minlength)
				$validator="min_length";
			else
				$validator="mandatory";

			$html=$st->replace_all("[[validator]]",'validator="'.$validator.'"',$html);
		} else
			$html=$st->replace_all("[[validator]]",'',$html);

		if ($minlength)
			$html=$st->replace_all("[[minlength]]",'data-minimal-length="'.$minlength.'"',$html);
		else
			$html=$st->replace_all("[[minlength]]",'',$html);

		if ($errormsg)
			$html=$st->replace_all("[[errormsg]]",'data-error-message="'.$errormsg.'"',$html);
		else
			$html=$st->replace_all("[[errormsg]]",'',$html);

		if ($width)
			$html=$st->replace_all("[[width]]",'style="width:'.$width.'" ',$html);
		else
			$html=$st->replace_all("[[width]]",'',$html);

		if ($maxlength)
			$html=$st->replace_all("[[maxlength]]",'maxlength="'.$maxlength.'" ',$html);
		else
			$html=$st->replace_all("[[maxlength]]",'',$html);

		if ($comments)
			$html=$st->replace_all("[[comments]]",$comment ,$html);
		else
			$html=$st->replace_all("[[comments]]",'',$html);

		return $html;
	}

	function form_field_date($name,$field) {
		extract($field);
		$st=new stringUtils();

		$html=$this->preprocess_template("date",$name,$field);

		if (Application::$page->mode=="DELETE" || $readonly)
			$html=$st->replace_all("[[readonly]]",'readonly="readonly" ',$html);
		else
			$html=$st->replace_all("[[readonly]]",'',$html);

		if ($width)
			$html=$st->replace_all("[[width]]",'style="width:'.$width.'" ',$html);
		else
			$html=$st->replace_all("[[width]]",'',$html);

		return $html;
	}
	function form_field_file($name,$field) {
		extract($field);
		$st=new stringUtils();

		$html=$this->preprocess_template("file",$name,$field);

		if (Application::$page->mode=="DELETE" || $readonly)
			$html=$st->replace_all("[[readonly]]",'readonly="readonly" ',$html);
		else
			$html=$st->replace_all("[[readonly]]",'',$html);

		if ($width)
			$html=$st->replace_all("[[width]]",'style="width:'.$width.'" ',$html);
		else
			$html=$st->replace_all("[[width]]",'',$html);

		if ($maxlength)
			$html=$st->replace_all("[[maxlength]]",'maxlength="'.$maxlength.'" ',$html);
		else
			$html=$st->replace_all("[[maxlength]]",'',$html);

		return $html;
	}

	function form_field_checkbox($name,$field) {
		extract($field);
		$st=new stringUtils();

		$html=$this->preprocess_template("checkbox",$name,$field);

		if ($checkedFunc && method_exists(Application::$page,$checkedFunc)) {
			$checked=call_user_func(array(Application::$page,$checkedFunc),$field["value"]);
		}

		if (Application::$page->mode=="DELETE" || $readonly)
			$html=$st->replace_all("[[readonly]]",'readonly="readonly" ',$html);
		else
			$html=$st->replace_all("[[readonly]]",'',$html);

		if ($checked)
			$html=$st->replace_all("[[checked]]",'checked="checked" ',$html);
		else
			$html=$st->replace_all("[[checked]]",'',$html);

		if ($width)
			$html=$st->replace_all("[[width]]",'style="width:'.$width.'" ',$html);
		else
			$html=$st->replace_all("[[width]]",'',$html);

		return $html;
	}

	function form_field_number($name,$field) {
		extract($field);
		$st=new stringUtils();

		if (!isset($field["min_value"])) $field["min_value"]="-999999";
		if (!isset($field["max_value"])) $field["max_value"]="999999";
		if (!isset($field["step"])) $field["step"]="1";

		$html=$this->preprocess_template("number",$name,$field);

		if (Application::$page->mode=="DELETE" || $readonly)
			$html=$st->replace_all("[[readonly]]",'readonly="readonly" ',$html);
		else
			$html=$st->replace_all("[[readonly]]",'',$html);

		if ($width)
			$html=$st->replace_all("[[width]]",'style="width:'.$width.'" ',$html);
		else
			$html=$st->replace_all("[[width]]",'',$html);

		return $html;
	}

	function form_field_custom($name,$field) {
		extract($field);
		$st=new stringUtils();

		$html=$this->preprocess_template("custom",$name,$field);
		$html=$st->replace_all("[[content]]",$content,$html);
		return $html;

	}
	function form_field_hidden($name,$field) {
		extract($field);
		$st=new stringUtils();

		$html=$this->preprocess_template("hidden",$name,$field);

		if (Application::$page->mode=="DELETE" || $readonly)
			$html=$st->replace_all("[[readonly]]",'readonly="readonly" ',$html);
		else
			$html=$st->replace_all("[[readonly]]",'',$html);

		if ($width)
			$html=$st->replace_all("[[width]]",'style="width:'.$width.'" ',$html);
		else
			$html=$st->replace_all("[[width]]",'',$html);

		return $html;
	}

	function form_field_gallery($name,$field) {
		extract($field);
		$st=new stringUtils();

		$html=$this->preprocess_template("gallery",$name,$field);

		if (Application::$page->mode=="DELETE" || $readonly)
			$html=$st->replace_all("[[readonly]]",'readonly="readonly" ',$html);
		else
			$html=$st->replace_all("[[readonly]]",'',$html);

		if ($width)
			$html=$st->replace_all("[[width]]",'style="width:'.$width.'" ',$html);
		else
			$html=$st->replace_all("[[width]]",'',$html);

		return $html;
	}

	function form_field_slider($name,$field) {
		extract($field);
		$st=new stringUtils();

		$html=$this->preprocess_template("slider",$name,$field);

		if (Application::$page->mode=="DELETE" || $readonly)
			$html=$st->replace_all("[[readonly]]",'readonly="readonly" ',$html);
		else
			$html=$st->replace_all("[[readonly]]",'',$html);

		if ($width)
			$html=$st->replace_all("[[width]]",'style="width:'.$width.'" ',$html);
		else
			$html=$st->replace_all("[[width]]",'',$html);

		return $html;
	}

	function form_field_textarea($name,$field) {
		extract($field);
		$st=new stringUtils();

		$html=$this->preprocess_template("textarea",$name,$field);

		if (Application::$page->mode=="DELETE" || $readonly)
			$html=$st->replace_all("[[readonly]]",'readonly="readonly" ',$html);
		else
			$html=$st->replace_all("[[readonly]]",'',$html);

		if ($width || $height) {
			$styles=array();
			if ($height) $styles[]="height: {$height};";
			if ($width) $styles[]="width: {$width};";
			$html=$st->replace_all("[[styles]]",'style="'.implode("",$styles).'" ',$html);
		} else
			$html=$st->replace_all("[[styles]]",'',$html);

		if ($required || $format=="email" || $minlength) {
			if ($format=="email")
				$validator="email";
			else if ($minlength)
				$validator="min_length";
			else
				$validator="mandatory";

			$html=$st->replace_all("[[validator]]",'validator="'.$validator.'"',$html);
		} else
			$html=$st->replace_all("[[validator]]",'',$html);

		if ($minlength)
			$html=$st->replace_all("[[minlength]]",'data-minimal-length="'.$minlength.'"',$html);
		else
			$html=$st->replace_all("[[minlength]]",'',$html);

		if ($errormsg)
			$html=$st->replace_all("[[errormsg]]",'data-error-message="'.$errormsg.'"',$html);
		else
			$html=$st->replace_all("[[errormsg]]",'',$html);

			if ($rows)
			$html=$st->replace_all("[[rows]]",'rows="'.$rows.'" ',$html);
		else
			$html=$st->replace_all("[[rows]]",'',$html);
		if ($cols)
			$html=$st->replace_all("[[cols]]",'cols="'.$cols.'" ',$html);
		else
			$html=$st->replace_all("[[cols]]",'',$html);
		return $html;
	}

	function form_field_select($name,$field) {
		extract($field);
		$st=new stringUtils();

		$html=$this->preprocess_template("select",$name,$field);
		if (Application::$page->mode=="DELETE" || $readonly)
			$html=$st->replace_all("[[disabled]]",'disabled="disabled" ',$html);
		else
			$html=$st->replace_all("[[disabled]]",'',$html);
		if ($select_size)
			$html=$st->replace_all("[[size]]",'size="'.$select_size.'" multiple="multiple" ',$html);
		else
			$html=$st->replace_all("[[size]]",'',$html);

		if (method_exists(Application::$page, $optionsFunction)) {
			$html=$st->replace_all("[[OPTIONS]]",Application::$page->$optionsFunction($value),$html);
		} else {
			$html=$st->replace_all("[[OPTIONS]]",'',$html);
		}
		return $html;
	}
}