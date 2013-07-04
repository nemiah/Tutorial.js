<?php
/**
 * 	
 * 	
 * 	@author		Marcel Block <mb@suchlotsen.de>
 * 	@version	
 * 	@copyright	2012 Marcel Block <mb@suchlotsen.de>
 * 	
 * 	2012-06-07
 */
class VideoGUIPresentationActions extends VideoGUIDesignActions {
	
	public function __construct() {
		
	}
	
	public function getHtml($entry, $attributes, $parsers) {
		$this->entry = $entry;
		$this->attributes = $attributes;
		$firstFlag = true;
		$html = '<div id="VideoGUIStep_' . $this->getTestStepId() . '" class="punit_qtip punit_draggable ui-state-default" style="display: inline-block; margin: 5px; border: solid 1px #cccc00; border-radius: 4px; background-color: #cccc99; width: 116px; height: 80px;">';
		
		$editButton = new Button("Eintrag editieren", "./images/i2/edit.png", "icon");
		$trashButton = new Button("Eintrag löschen", "./images/i2/delete.gif", "icon");
		foreach ($attributes as $attribute)
			if (preg_match("/ID$/", $attribute)) {
				$editButton->editInPopup("TestStep", $entry->A($attribute));
				$trashButton->onclick("deleteClass('TestStep','" . $entry->A($attribute) . "', function() { contentManager.reloadFrame('contentScreen'); /*ADD*/ },'Eintrag wirklich löschen?');");
			}
		
		$nonParserContent = "";
		foreach ($attributes as $attribute) {
			$content = $entry->A($attribute);
			if (isset($parsers[$attribute])) {
				$content = VideoGUI::invokeParser($parsers[$attribute], $content, $entry);
			} else {
				$nonParserContent .= $content . "<br>";
				continue;
			}
			if ($firstFlag) {
				$firstFlag = false;
				$html .= '<h2 style="padding-left: 5px;"><div style="display:inline-block;">' . $content . '</div>&nbsp;&nbsp;<div style="display: inline-block; position: relative; left: 39px;">' . $editButton . '&nbsp;&nbsp;' . $trashButton . '</div></h2><p>';
				continue;
			}
			$html .= $content;
		}
		$html .= "<br>" . $nonParserContent . '</p></div>';
		return $html;
	}
	
}

?>
