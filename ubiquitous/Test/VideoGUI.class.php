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
class VideoGUI extends propertyC {
	
	protected $videoTable = null;
	protected $parsers = array();
	protected $buttons = array();
	
	public function getHTML($id, $page) {
		$this->videoTable = new VideoGUIVideoTable();
		$this->videoTable->setParsers($this->parsers);
		$this->addDesignTypes();
		
		if(!$this->singleOwner){
			$this->addAssocV3($this->collectionOf."OwnerClass", "=", $this->ownerClassName);
			$this->addAssocV3($this->collectionOf."OwnerClassID", "=", $this->ownerClassID);
		} else
			$this->addAssocV3($this->collectionOf.$this->ownerClassName."ID", "=", $this->ownerClassID);
		
		if($this->buttonLabel instanceof Button)
			$B = $this->buttonLabel;
		else {
			$B = new Button($this->buttonLabel, $this->buttonIcon == null ? "new" : $this->buttonIcon);
			if($this->buttonOnclick == null)
				$B->select(false, "m".$this->targetClassName, $this->ownerClassName, $this->ownerClassID, "add".$this->targetClassName);
			else
				$B->onclick($this->buttonOnclick);
		}
		
//		$this->videoTable->setHtmlGuiX($this->getGUI());
		$this->videoTable->setCollection($this);
		$this->videoTable->setAttributes($this->showAttributes);
		
//		return parent::getHTML($id, $page);
		return '<div style="margin: 3px;">' . $this->getButtonHtml() . "</div>" . $this->videoTable->getHtml();
	}
	
	public function setButton(Button &$button) {
		$this->buttons[] = $button;
	}
	
	protected function getButtonHtml() {
		$html = "";
//		$htmlTable = new HTMLTable(1);
//		$htmlTable->addRow($this->buttons);
		foreach ($this->buttons as $button) {
			$html .= $button;
//			$htmlTable->addRow($button);
		}
		return $html;
//		return $htmlTable->getHTML();
	}
	
	public function addParser($attributeName, $function) {
		$this->parsers[$attributeName] = $function;
		return $this;
	}
	
	protected function addDesignTypes() {
		$mouseActions = new VideoGUIMouseActions();
		$keyboardActions = new VideoGUIKeyboardActions();
		$presentationActions = new VideoGUIPresentationActions();
		$systemActions = new VideoGUISystemActions();
		$noActions = new VideoGUINoActions();
		$this->videoTable->addTypeDesignPair("openTab", $mouseActions);
		$this->videoTable->addTypeDesignPair("saveEntry", $mouseActions);
		$this->videoTable->addTypeDesignPair("newEntry", $mouseActions);
		$this->videoTable->addTypeDesignPair("clickButton", $mouseActions);
		$this->videoTable->addTypeDesignPair("show", $presentationActions);
		$this->videoTable->addTypeDesignPair("setValue", $keyboardActions);
		$this->videoTable->addTypeDesignPair("setValues", $keyboardActions);
		$this->videoTable->addTypeDesignPair("rme", $systemActions);
		$this->videoTable->addTypeDesignPair("switchApplication", $systemActions);
		$this->videoTable->addTypeDesignPair("login", $systemActions);
		$this->videoTable->addTypeDesignPair("logoff", $systemActions);
		$this->videoTable->addTypeDesignPair("rme", $systemActions);
		$this->videoTable->addTypeDesignPair("no", $noActions);
	}
	
	static public function invokeParser($function, $value, $element) {
		$c = explode("::", $function);
		$method = new ReflectionMethod($c[0], $c[1]);
		return $method->invoke(null, $value, $element);
	}
	
}

?>
