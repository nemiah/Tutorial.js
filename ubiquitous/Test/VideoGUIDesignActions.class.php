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
abstract class VideoGUIDesignActions {
	
	protected $attributes = null;
	
	abstract public function getHtml($entry, $attributes, $parsers);
	
	protected function getTestStepId() {
		return $this->entry->A("TestStepID");
	}
	
	protected function getTestStepSortId() {
		return $this->entry->A("TestStepSort");
	}
	
}

?>
