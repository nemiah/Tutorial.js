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
class VideoGUIVideoTable {
	
	protected $typeDesignPair = array();
	protected $typeDesignUniqueInstances = array();
	protected $collection = null;
	protected $attributes = null;
	protected $entryCollection = array();
	protected $entryCollectionHtml = array();
	protected $parsers = array();
	protected $topButtons = array();
	protected $htmlguix = null;
	
	public function __construct() {
		
	}
	
	public function getHtml() {
		$html = '';
//		$html .= $this->htmlguix->topButtons();
		$html .= '<div id="testStepsContainer" class="ui-state-default" style="white-space:nowrap; overflow: auto;">';
		while ($entry = $this->collection->getNextEntry()) {
			$this->entryCollection[] = $entry;
			$this->entryCollectionHtml[] = $this->getHtmlByEntry($entry, $this->getDesignInstanceByEntryType($entry->A($this->attributes[0])));
		}
		$count = count($this->entryCollectionHtml);
		$designCount = count($this->typeDesignUniqueInstances);
		$flag = true;
//		for ($i = 0; $i < $designCount; $i++) {
//			$html .= '<tr style="height: 88px; background-color: #';
//			if ($flag) {
//				$flag = false;
//				$html .= 'ccccff';
//			} else {
//				$flag = true;
//				$html .= 'ccffcc';
//			}
//			$html .= ';">';
			for ($j = 0; $j < $count; $j++) {
//				if (!$this->checkTypeDesignInstance($this->entryCollection[$j]->A($this->attributes[0]), $i)) {
//					$html .= '<td class="punit_droppable" width="167px;" style="width: 167px; height: 88px;"></td>';
//					continue;
//				}
				$html .= $this->entryCollectionHtml[$j];
			}
//			$html .= '</tr>';
//		}
		$html .= '</div>';
		return $html;
	}
	
	public function getHtmlByEntry($entry, $designInstance) {
		if ($designInstance)
			return $designInstance->getHtml($entry, $this->attributes, $this->parsers);
	}
	
	public function addTopButton($labelOrButton, $image = ""){
		if(!is_object($labelOrButton))
			$B = new Button($labelOrButton, $image);
		else
			$B = $labelOrButton;

		$this->topButtons[] = $B;

		return $B;
	}
	
	/**
	 * Als Typ wird das erste Element von showAttributes verwendet.
	 * @param type $type
	 * @param VideoGUIDesignActions $design 
	 */
	public function addTypeDesignPair($type, VideoGUIDesignActions $design) {
		$this->typeDesignPair[] = array(
			"type" => $type,
			"design" => $design
		);
		$designs = array();
		foreach ($this->typeDesignPair as $typeDesignPair)
			$designs[] = get_class($typeDesignPair["design"]);
		$designs = array_unique($designs);
		foreach ($designs as $design)
			$this->typeDesignUniqueInstances[$design] = array();
		foreach ($this->typeDesignUniqueInstances as $design => &$values)
			foreach ($this->typeDesignPair as $typeDesignPair)
				if (get_class($typeDesignPair["design"]) == $design)
					$values[] = $typeDesignPair["type"];
	}
	
	public function checkTypeDesignInstance($type, $id) {
		$i = 0;
		foreach ($this->typeDesignUniqueInstances as $design => $values) {
			if ($id != $i) {
				$i++;
				continue;
			}
			foreach ($values as $value)
				if ($value == $type)
					return true;
			return false;
		}
		return false;
	}
	
	public function getDesignInstanceByEntryType($entryType) {
		$flag = false;
		if (empty($entryType))
			$flag = true;
		foreach ($this->typeDesignPair as $designPair) {
			if ($flag && $designPair["type"] == "no")
				return $designPair["design"];
			if ($designPair["type"] == $entryType)
				return $designPair["design"];
		}
		return false;
	}
	
	public function setCollection(anyC $collection) {
		$this->collection = $collection;
		return $this;
	}
	
	public function setAttributes($attributes) {
		$this->attributes = $attributes;
		return $this;
	}
	
	public function setParsers($parsers) {
		$this->parsers = $parsers;
		return $this;
	}
	
//	public function setHtmlGuiX($gui) {
//		$this->htmlguix = $gui;
//	}
	
}

?>
