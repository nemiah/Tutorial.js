<?php
/*
 *  This file is part of phynx.

 *  phynx is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  phynx is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *  2007 - 2013, Rainer Furtmeier - Rainer@Furtmeier.IT
 */
class Button {
	
	private $image;
	private $label;
	private $style;
	private $rme;
	private $onclick;
	private $type = "bigButton";
	private $id;
	private $class = "backgroundColor3";
	private $disabled = false;
	private $mouseOverEffect = true;
	private $name;
	private $settings;
	private $js = "";
	private $before = "";

	/**
	 * Use this class to display a button
	 * You may omitt the whole path to the $image and only give the image name e.g. "new", if the image is in the folder ./images/navi/.
	 *
	 * @param string $label The label of the button
	 * @param string $image The relative path to the image of the button.
	 */
	function __construct($label, $image = "", $type = "bigButton"){
		$this->image = $image;
		$this->label = $label;
		$this->type($type);
	}

	function getAction(){
		$rme = $this->rme;
		$this->rme = null;
		
		return $rme;
	}
	
	function label($label){
		$this->label = $label;
	}
	
	function name($name){
		$this->name = $name;
	}
	
	function disabled($bool){
		$this->disabled = $bool;
	}
	
	function id($id){
		$this->id = $id;
	}
	
	function type($type){
		switch($type){
			case "save":
				$this->image = "./images/i2/save.gif";
				
			case "bigButton":
			case "LPBig":
				$this->type = $type;
				$this->class = "backgroundColor3";
			break;
			
			case "icon":
				$this->type = $type;
				$this->class = "";
			break;
			
			case "iconic":
				$this->type = $type;
				$this->class = "";
			break;
			
			case "iconicG":
				$this->type = "iconic";
				$this->class = "iconicG";
			break;
			
			case "iconicR":
				$this->type = "iconic";
				$this->class = "iconicR";
			break;
			
			case "iconicL":
				$this->type = "iconic";
				$this->class = "iconicG iconicL";
			break;
			
			case "seamless":
			case "touch":
				$this->type = $type;
				$this->class = "";
			break;
			
			default:
				die("Button-type $type not available");
			break;
		}
	}
	
	function contextMenu($plugin, $identifier, $title, $leftOrRight = "right", $upOrDown = "down", $options = "{}"){
		$this->onclick = "phynxContextMenu.start(this, '$plugin','$identifier','$title', '$leftOrRight', '$upOrDown', $options);";
	}

	function image($path){
		$this->image = $path;
	}
	
	function style($style){
		$this->style = $style;
	}
	
	function getStyle(){
		return $this->style;
	}
	
	function className($class){
		$this->class = $class;
	}

	function newSession($physionName, $application, $plugin, $title = null){
		$this->onclick = "contentManager.newSession('$physionName', '$application', '$plugin', ".(isset($_SESSION["phynx_customer"]) ? "'".$_SESSION["phynx_customer"]."'" : "''").", '".($title != null ? $title : "")."');";
	}
	
	function select($isMultiSelection, $selectPlugin, $callingPlugin, $callingPluginID, $callingPluginFunction){
		#$this->rme = " contentManager.rightSelection(".($isMultiSelection ? "true" : "false").", '$pluginRight','$pluginLeftID','$calledPlugin','$calledPluginID','$calledPluginFunction');";
		#isMultiSelection, selectPlugin, callingPlugin, callingPluginID, callingPluginFunction
		$this->rme = "contentManager.backupFrame('contentRight','selectionOverlay'); contentManager.rightSelection(".($isMultiSelection ? "true" : "false").", '$selectPlugin','$callingPlugin','$callingPluginID','$callingPluginFunction');";

	}

	function leftSelect($isMultiSelection, $selectPlugin, $callingPlugin, $callingPluginID, $callingPluginFunction){
		$this->rme = " contentManager.backupFrame('contentLeft','selectionOverlay'); contentManager.leftSelection(".($isMultiSelection ? "true" : "false").", '$selectPlugin','$callingPlugin','$callingPluginID','$callingPluginFunction');";
	}

	function customSelect($targtFrame, $callingPluginID, $selectPlugin, $selectJSFunction, $addBPS = "", $options = ""){
		$this->rme = " contentManager.backupFrame('$targtFrame','selectionOverlay'); contentManager.customSelection('$targtFrame', '$callingPluginID', '$selectPlugin', '$selectJSFunction', '$addBPS', ".($options == "" ? "{}" : $options).");";
	}
	
	function rme($targetClass, $targetClassId, $targetMethod, $targetMethodParameters = "", $onSuccessFunction = "", $bps = ""){
		if(is_object($targetClass)) $targetClass = str_replace("GUI","",get_class($targetClass));
		$this->rme = "contentManager.rmePCR('$targetClass', '$targetClassId', '$targetMethod', Array(".(is_array($targetMethodParameters) ? implode(",",$targetMethodParameters) : "'".$targetMethodParameters."'")."), '$onSuccessFunction', '$bps');";
	}
	
	function rmeP($targetClass, $targetClassId, $targetMethod, $targetMethodParameters = "", $onSuccessFunction = "", $bps = ""){
		$this->rme = "rmeP('$targetClass', '$targetClassId', '$targetMethod', Array(".(is_array($targetMethodParameters) ? implode(",",$targetMethodParameters) : "'".$targetMethodParameters."'")."), '$onSuccessFunction', '$bps');";
	}

	function editInPopup($targetClass, $targetClassId, $title = "Eintrag bearbeiten", $bps = ""){
		$this->rme = "contentManager.editInPopup('$targetClass', '$targetClassId', '".T::_($title)."', '$bps');";
	}
	
	/**
	 * Executes a method of a class and forwards the parameters to it.
	 *
	 * Automatically checks the response before executing $onSuccessFunction
	 *
	 * @param string $targetClass The name of the class you want to call. Do not append "GUI" here, it will be automatically added
	 * @param string $targetClassId The ID will be given to the constructor of the class
	 * @param string $targetMethod The method called
	 * @param string $targetMethodParameters The parameters forwarded to the called method. Can be an array if you want to give multiple parameters
	 * @param string $onSuccessFunction Some JavaScript that will be executed if the response is fine
	 * @param string $bps Background Plugin Storage commands that will be executed before the method is called
	 */
	function rmePCR($targetClass, $targetClassId, $targetMethod, $targetMethodParameters = "", $onSuccessFunction = "", $bps = "", $doResponseCheck = true, $onFailureFunction = ""){
		if(strpos($onSuccessFunction, "function(") !== 0)
				$onSuccessFunction = "'".addslashes($onSuccessFunction)."'";
		
		#$this->rmeP($targetClass, $targetClassId, $targetMethod, $targetMethodParameters, $onSuccessFunction != "" ? addslashes("if(checkResponse(transport)) { ".$onSuccessFunction."}") : $onSuccessFunction, $bps);
		$this->rme = "contentManager.rmePCR('$targetClass', '$targetClassId', '$targetMethod', Array(".(is_array($targetMethodParameters) ? implode(",",$targetMethodParameters) : "'".$targetMethodParameters."'")."), $onSuccessFunction, '$bps', ".($doResponseCheck ? "true" : "false")." ".($onFailureFunction != "" ? ", $onFailureFunction" : "").");";
	}

	function settings($plugin, $identifier = ""){
		$this->settings = $B = new Button("Einstellungen", "./images/i2/settings.png", "icon");

		if(strpos($this->style, "float:right;") !== false)
			$B->style("float:right;margin-right:-22px;");
		else
			$B->style("margin-left:4px;margin-bottom:15px;");
		$B->contextMenu($plugin, $identifier, "Einstellungen:");
		
		$B->className("buttonSettings");
		
		return $B;
	}

	function loadFrame($target, $plugin, $withId = -1, $page = 0, $bps = "", $onSuccessFunction = ""){
		$this->rme = "contentManager.loadFrame('$target', '$plugin', '$withId', '$page', '$bps', '$onSuccessFunction');";
	}
	
	function loadPlugin($target, $plugin, $bps = "", $withId = null){
		$this->rme = "contentManager.loadPlugin('$target', '$plugin', '$bps'".($withId != null ? ", $withId" : "").");";
	}
	
	function onclick($value){
		$this->onclick = $value;
	}
	
	function addOnclick($value){
		$this->onclick .= $value;
	}
	
	function windowRme($targetClass, $targetClassId, $targetMethod, $targetMethodParameters = "", $bps = "", $target = "window"){
		$this->rme = "windowWithRme('$targetClass', '$targetClassId', '$targetMethod', Array(".(is_array($targetMethodParameters) ? implode(",",$targetMethodParameters) : "'".$targetMethodParameters."'")."), '$bps', '$target');";
		
	}

	/**
	 * call this function before rme ort popup and execute rme/popup as onSuccess
	 * use %AFTER as variable for rme/popup
	 * 
	 * @param string $before 
	 */
	function doBefore($before){
		$this->before = $before;
	}
	
	function popup($name, $title, $targetClass, $targetClassId, $targetMethod, $targetMethodParameters = "", $bps = "", $popupOptions = null){
		#$this->rme = "contentManager.rmePCR('$targetClass', '$targetClassId', '$targetMethod', Array(".(is_array($targetMethodParameters) ? implode(",",$targetMethodParameters) : "'".$targetMethodParameters."'")."), 'Popup.displayNamed(\'edit\', \'$title\', transport, \'$name\');', '$bps');";
		$this->rme = "Popup.load('".T::_($title)."', '$targetClass', '$targetClassId', '$targetMethod', [".(is_array($targetMethodParameters) ? implode(",",$targetMethodParameters) : "'".$targetMethodParameters."'")."], '$bps'".($name != "" ? ", '$name'" : ", 'edit'")."".($popupOptions != null ? ", '".addslashes($popupOptions)."'" : "").")";
	}

	function hasMouseOverEffect($bool){
		$this->mouseOverEffect = $bool;
	}
	
	function droppable($onDropFunction, $hoverClass = null){
		if(!$this->id)
			$this->id = "DroppableButton".rand (100, 100000);
		
		$this->js = OnEvent::script(OnEvent::droppable($this->id, $onDropFunction, $hoverClass));
	}
	
	function __toString(){
		if($this->type != "seamless" AND $this->type != "touch")
			$this->label = T::_($this->label);
		
		if($this->before != "")
			$this->rme = str_replace("%AFTER", $this->rme, $this->before);

		if($this->image != "" AND $this->image[0] != "." AND strpos($this->image, ":") === false AND $this->image[0] != "/" AND $this->type != "iconic" AND $this->type != "seamless" AND $this->type != "touch")
			$this->image = "./images/navi/$this->image.png";# : $this->image );

		$onclick = $this->onclick != null ? $this->onclick : "";
		#if($this->pluginRight != null) $onclick .= ;
		if($this->rme != null) $onclick .= " ".$this->rme;
		if($this->type == "bigButton" OR $this->type == "LPBig") return (strpos($this->style, "float:right;") !== false ? $this->settings : "")."<button".($this->name != null ? " name=\"$this->name\"" : "")." ".($this->disabled ? "disabled=\"disabled\"" : "")." ".($this->id ? "id=\"$this->id\" " : "")."onclick=\"$onclick\" type=\"button\" class=\"$this->class ".($this->type == "bigButton" ? "bigButton" : "LPBigButton")."\" style=\"{$this->style}".($this->image != "" ? "background-image:url(".$this->image.");" : "")."\" ".($this->type == "bigButton" ? "" : "title=\"$this->label\"").">".($this->type == "bigButton" ? nl2br($this->label) : "")."</button>".(strpos($this->style, "float:right;") === false ? $this->settings : "")."$this->js";
		
		if($this->type == "icon") return "<img ".($this->id ? "id=\"$this->id\" " : "")." ".($onclick != "" ? "onclick=\"$onclick\"" : "")." class=\"".($this->mouseOverEffect ? "mouseoverFade" : "")." $this->class\" style=\"{$this->style}\" src=\"".$this->image."\" title=\"$this->label\" alt=\"$this->label\" />$this->js";
		
		if($this->type == "iconic") return "<span ".($this->id ? "id=\"$this->id\" " : "")." ".($onclick != "" ? "onclick=\"$onclick\"" : "")." class=\"iconic $this->class $this->image\" style=\"{$this->style}\" title=\"$this->label\" alt=\"$this->label\" ></span>$this->js";
		
		if($this->type == "save") return "<input ".($this->id ? "id=\"$this->id\" " : "")." onclick=\"$onclick\" type=\"button\" value=\"$this->label\" style=\"{$this->style}background-image:url(".$this->image.");\" />$this->js";
		
		if($this->type == "seamless"){
			$B = new Button($this->label, $this->image, "iconicG");
			$B->style("float:right;margin-left:10px;margin-top:-1px;");
			
			return "<div style=\"$this->style\" onclick=\"$onclick\" class=\"seamlessButton\">$B".T::_($this->label)."</div>";
		}
		
		if($this->type == "touch"){
			$B = new Button($this->label, $this->image, "iconicL");
			#$B->style("float:left;margin-left:10px;margin-top:-1px;");
			
			return "
			<div class=\"touchButton\" ".($this->id ? "id=\"$this->id\" " : "")." onclick=\"$onclick\" style=\"$this->style\">
				".$B."
				<div class=\"label\">".T::_($this->label)."</div>
				<div style=\"clear:both;\"></div>
			</div>";
		}
	}
}
?>