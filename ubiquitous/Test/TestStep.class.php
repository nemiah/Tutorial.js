<?php

/**
 *  This file is part of ubiquitous.

 *  ubiquitous is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  ubiquitous is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *  2007 - 2013, Rainer Furtmeier - Rainer@Furtmeier.IT
 */
class TestStep extends PersistentObject {

	public static $counter = array();
	public static $step2Audio = array();

	public function newAttributes() {
		$A = parent::newAttributes();

		$A->TestStepSort = 2000;

		return $A;
	}

	public function saveMe($checkUserData = true, $output = false) {
		$duration = 1;
		
		switch($this->A("TestStepType")){
			case "show":
			case "openTab":
			case "newEntry":
			case "setValues":
			case "clickButton":
			case "saveEntry":
				if($this->A("TestStepAudioDuration") != "0")
					$duration += $this->A("TestStepAudioDuration");
				else {
					$words = count(explode(" ", $this->A("TestStepDescription")));
					$wait = ceil($words / 2);
					if($wait < 0)
						$wait = 0;
					$duration += $wait;
				}
			break;
			
		}
		
		$this->changeA("TestStepPresentationDuration", ceil($duration));
		
		return parent::saveMe($checkUserData, $output);
	}
	
	/**
	 * Generiert die Methode des aktuellen Testschrittes
	 * @return string 
	 */
	public function generateTestMethod() {
		if (!isset(self::$counter[$this->A("TestStepTestID")]))
			self::$counter[$this->A("TestStepTestID")] = 0;

		if (!isset(self::$step2Audio[$this->A("TestStepTestID")]))
			self::$step2Audio[$this->A("TestStepTestID")] = array();

		$f = $this->A("TestStepType");
		$mouseAnimationFunction = $f."MouseAnimation";
		if(trim($f) == "")
			return "";

		$condition = $this->A("TestStepCondition");
		if($condition != ""){
			$condTest = true;
			eval("\$condTest = (".$condition.");");#.($condition).";");
			if(!$condTest)
				return "";
		}
		
		$js = "
		
	step".self::$counter[$this->A("TestStepTestID")].": function(){"
			.$this->$f()."
	}";
		
		if(method_exists($this, $mouseAnimationFunction))
		$js .= ",
	
	stepMouseAnimation".self::$counter[$this->A("TestStepTestID")].": function(onFinished) {"
			.$this->$mouseAnimationFunction()."
	}";
		
		$inputAnimationFunction = $f."InputAnimation";
		if(method_exists($this, $inputAnimationFunction))
			$js .= ",
	
	stepInputAnimation".self::$counter[$this->A("TestStepTestID")].": function() {"
			.$this->$inputAnimationFunction()."
	}";
		
		self::$step2Audio[$this->A("TestStepTestID")][self::$counter[$this->A("TestStepTestID")]] = $this->getID();
		self::$counter[$this->A("TestStepTestID")]++;
		return $js;
	}

	private function overlay(){
		return "
		if(\$j('#pUnitPointer').length)
			\$j('#pUnitPointer').fadeIn();
		Overlay.hideDark(0.3); \$j('#overlayText').fadeOut(500, function(){ \$j(this).remove(); }); Test.c.next();";
	}
	
	private function overlayMouseAnimation() {
		return "
		if(\$j('#pUnitPointer').length)
			\$j('#pUnitPointer').fadeOut();
		\$j('.qtip.ui-tooltip').qtip('hide');
		Overlay.showDark(0.2, 0.9);
		\$j('body').append('<div id=\"overlayText\" style=\"display:hidden;width:800px;font-size:40px;font-weight:bold;line-height:1.5;color:#DDD;position:absolute;top:100px;z-index:100000;\">".str_replace("\n", "", $this->fixUml(addslashes($this->A("TestStepDescription"))))."</div>'); \$j('#overlayText').css('left', \"200px\").fadeIn(200);
		
		".$this->next(4)."";
	}
	
	private function rme(){
		return "
		".OnEvent::rme(new TestRmeTargetGUI(-1), $this->A("TestStepEntry"), $this->A("TestStepTestID"), ($this->A("TestStepEntry") == "fixMenu" ? "function(){ Menu.refresh(function(){ Test.c.next(); }); }" : "function(){ Test.c.next(); }"));
	}
	
	private function switchApplication() {
		return "
			\$j('#navigation').find('img').each(function(){
				if (\$j(this).attr('alt') == 'Abmelden/Anwendung wechseln') {
					\$j('html').ajaxComplete(function(e, xhr, settings) {
						if(settings.url.search('SysMessages;displayCategory') >= 0 || (settings.data && settings.data.search('class=Test') >= 0)){
							return;
						}
						if(Test.c.requests > 1){
							Test.c.requests--;
							return;
						}

						\$j('html').unbind('ajaxComplete');
						\$j('#cMData').find('td').each(function(){
							if (\$j(this).html() == '" . $this->A("TestStepValue") . "') {
								\$j(this).parent('tr').delay(500).trigger('click');
								if(Test.c.requests > 0)
									\$j('html').ajaxComplete(function(e, xhr, settings) {
										if(settings.url.search('SysMessages;displayCategory') >= 0 || (settings.data && settings.data.search('class=Test') >= 0)){
											return;
										}
										if(Test.c.requests > 1){
											Test.c.requests--;
											return;
										}

										\$j('html').unbind('ajaxComplete');

										if(!Test".$this->A("TestStepTestID").".presentation)
										setTimeout(function(){ Test.c.next(); }, 1000);
										else
											setTimeout(function(){ Test.c.next(); }, 400);
								});
							}
						});
					});
					\$j(this).delay(500).trigger('click');
				}
			});
		";
	}
	
	private function logoff() {
		return "
			\$j('#navigation').find('img').each(function(){
				if (\$j(this).attr('alt') == 'Abmelden/Anwendung wechseln') {
					\$j('html').ajaxComplete(function(e, xhr, settings) {
						if(settings.url.search('SysMessages;displayCategory') >= 0 || (settings.data && settings.data.search('class=Test') >= 0)){
							return;
						}
						if(Test.c.requests > 1){
							Test.c.requests--;
							return;
						}

						\$j('html').unbind('ajaxComplete');

						setTimeout(function(){
							\$j('#cMData').find('p').each(function(){
								if (\$j(this).html() == '<b>" . $this->A("TestStepValue") . "</b>') {
									\$j('html').ajaxComplete(function(e, xhr, settings) {
										if(settings.url.search('SysMessages;displayCategory') >= 0 || (settings.data && settings.data.search('class=Test') >= 0)){
											return;
										}
										if(Test.c.requests > 1){
											Test.c.requests--;
											return;
										}
										Test.c.completed.push(".$this->getID().");

										\$j('html').unbind('ajaxComplete');

										if(!Test".$this->A("TestStepTestID").".presentation)
										setTimeout(function(){ Test.c.next(); }, 1000);
										else
											setTimeout(function(){ Test.c.next(); }, 400);
									});
									\$j(this).parent('div').trigger('click');
								}
							});
							
						}, 1000);
					});
					\$j(this).trigger('click');
				}
			});
		";
	}
	
	private function login() {
		$values = explode("\n", $this->A("TestStepValue"));

		$r = "
		var ok = true;
		var inputElement = null;";

		foreach ($values as $val) {
			$match = array();
			preg_match("/^([^:]+):(.+)/", $val, $match);
			$r .= "inputElement = \$j('#" . $match[1] . "');
				if (inputElement)
					inputElement.val('" . $match[2] . "');
				else
					ok = false;";
		}
			$r .= "
			
			if(ok)
				Test.c.completed.push(".$this->getID().");
			else
				Test.c.errors.push(".$this->getID().");
			
			localStorage.setItem('punitTest', serialize(Test));
			
			\$j('html').ajaxComplete(function(e, xhr, settings) {
				if(settings.url.search('SysMessages;displayCategory') >= 0 || (settings.data && settings.data.search('class=Test') >= 0)){
					return;
				}
				if(Test.c.requests > 1){
					Test.c.requests--;
					return;
				}

				\$j('html').unbind('ajaxComplete');

				if(!Test".$this->A("TestStepTestID").".presentation)
					setTimeout(function(){ continueTest(); }, 1000);
				else
					setTimeout(function(){ continueTest(); }, 400);
			});
			\$j('input[type=button]').trigger('click');
		";

		return $r;
	}
	
	private function newEntry() {
		return "
		/*Aspect.registerOnLoadFrame('contentLeft', '".$this->A("TestStepClass")."', true, function(){
			Test.c.next();
			return true;
		}, true);*/
		

		var button = \$j('#buttonNewEntry".$this->A("TestStepClass")."');
		if(button){
			button.trigger('click');
			Test.c.completed.push(".$this->getID().");
				
			\$j('html').ajaxComplete(function(e, xhr, settings) {
				if(settings.url.search('loadFrame.php') == -1){
					return;
				}

				if(settings.url.search('SysMessages;displayCategory') > 0){
					return;
				}
				if (settings.url.search('p=".$this->A("TestStepClass")."') == -1)
					return;

				\$j('html').unbind('ajaxComplete');

				setTimeout(function(){ Test.c.next(); }, 1000);
			});
		} else
			Test.c.errors.push(".$this->getID().");";
	}
	
	private function newEntryMouseAnimation() {
		return "
		TestManager.Mouse.moveTo(\$j('#buttonNewEntry".$this->A("TestStepClass")."'), true, '".$this->fixUml(addslashes($this->A("TestStepDescription")))."', function(){
			".$this->next()."
		});";
	}

	private function setValue() {
		return "
		var entry = \$j('".$this->A("TestStepEntry") ."');
		if(entry) {
			entry.val(".$this->A("TestStepValue").");
			entry.effect('highlight', {}, 600);
			Test.c.completed.push(".$this->getID().");
		} else
			Test.c.errors.push(".$this->getID().");

		Test.c.next();";
	}
	
	private function setValueMouseAnimation() {
		return "
		TestManager.Mouse.moveTo(\$j('".$this->A("TestStepEntry") ."'), false, '".$this->fixUml(addslashes($this->A("TestStepDescription")))."', function(){ if(typeof onFinished == 'function') onFinished(); ".$this->next()." });";
	}

	private function setValues() {
		$values = explode("\n", $this->A("TestStepValue"));

		$r = "
		var ok = true;";

		$json = "{";
		foreach ($values as $val) {
			$match = array();
			preg_match("/^([^:]+):(.+)/", $val, $match);
			$json .= '"' . $match[1] . '":"' . $match[2] . '",';
		}
		$json = preg_replace("/,\s*$/", "", $json);
		$json .= "}";
		
			$r .= "
		Test.c.lastInsertedValues = " . $json . ";
		TestManager.getGeneratedString('" . $json . "', function (data) {
			\$j.each(data, function(entryKey, entryValue) {
				var input = \$j('#' + entryKey);
				if(input.length){
					var flag = true;
					if (input.prop('tagName') == 'SELECT') {
						flag = false;
						input.find('option').each(function(){
							var  \$this = \$j(this);
							if (\$this.html() == entryValue) {
								input.val(\$this.val());
							}
						});
					}
					if(!Test".$this->A("TestStepTestID").".presentation && flag)
						input.val(entryValue);
				} else
					ok = false;
			});
			Test.c.lastStepID = ".$this->getID().";
			Test.c.next();
		});";

		return $r;
	}
	
	private function setValuesMouseAnimation() {
		$values = explode("\n", $this->A("TestStepValue"));
		$e = explode(":", $values[0]);
		
		return "
		TestManager.Mouse.moveTo(\$j('#".$e[0]."'), false, '".$this->fixUml(addslashes($this->A("TestStepDescription")))."', function(){ if(typeof onFinished == 'function') onFinished(); ".$this->next()." });";
	}
	
	private function setValuesInputAnimation(){
		$values = explode("\n", $this->A("TestStepValue"));

		$r = "";

		foreach ($values AS $v) {
			$e = explode(":", $v);
			$r .= "
		
		var input = \$j('#".$e[0]."');
		var content = '".$e[1]."';
		if(input.length){
			input.effect('highlight', {}, 600);
			if(input.attr('type') == 'text'){
				input.jTypeWriter({text: content});
			} else
				input.val(content);
		} else
			ok = false;";
		}
		
		return "
			$r";
	}

	private function saveEntry() {
		return "
		var button = \$j('#".$this->A("TestStepForm")." input[name=currentSaveButton]');
		if(button.length) {
			Test.c.completed.push(".$this->getID().");
			button.trigger('click');
		} else
			Test.c.errors.push(".$this->getID().");

		contentManager.rmePCR('Test', '-1', 'getLastID', '".$this->A("TestStepClass")."', function(transport){ Test.c.lastID = transport.responseText; TestManager.checkInsertedValues('" . $this->A("TestStepClass") . "', " . $this->getID() . "); });
		";
	}
	
	private function saveEntryMouseAnimation() {
		return "
		TestManager.Mouse.moveTo(\$j('input[name=currentSaveButton]'), true, '".$this->fixUml(addslashes($this->A("TestStepDescription")))."', function(){
			".$this->next()."
		});";
	}

	private function openTab() {
		return "
		var tab = \$j('#".$this->A("TestStepClass")."MenuEntry > div');
		if(tab.length){
			tab.trigger('click');
			\$j('html').ajaxComplete(function(e, xhr, settings) {
				\$j('html').unbind('ajaxComplete');
				
				setTimeout(function(){ Test.c.next(); }, 1000);
			});
			Test.c.completed.push(".$this->getID().");
		} else {
			Test.c.errors.push(".$this->getID().");
		}";
	}
	
	private function openTabMouseAnimation() {
		return "
		TestManager.Mouse.moveTo(\$j('#".$this->A("TestStepClass")."MenuEntry > div'), true, '".$this->fixUml(addslashes($this->A("TestStepDescription")))."', function(){
			".$this->next()."
		});";
	}
	
	
	private function show(){
		return "
		Test.c.completed.push(".$this->getID().");
		Test.c.next();";
		/*"
		if(!Test".$this->A("TestStepTestID").".presentation)
			 Test.c.next();
		else
			setTimeout(function(){ Test.c.next(); }, ".ceil($this->A("TestStepAudioDuration") * 1000).");";*/
	}
	
	private function showMouseAnimation() {
		return "
		TestManager.Mouse.moveTo(\$j('".$this->A("TestStepEntry")."'), false, '".$this->fixUml(addslashes($this->A("TestStepDescription")))."', function(){
			".$this->next()."
		});";
	}#340/144
	
	private function clickButton() {
		return "
		".($this->A("TestStepValue") != "" ? $this->A("TestStepValue") : "")."
		var button = ".(substr($this->A("TestStepEntry"), 0, 2) == "\$j" ? $this->A("TestStepEntry").";" : "\$j('".$this->A("TestStepEntry")."');")."
		
		if (button.length) {
			button.trigger('click');
			Test.c.completed.push(".$this->getID().");
			Test.c.requests = ".($this->A("TestStepValue") != "" ? $this->A("TestStepValue") : "1").";
			
			if(Test.c.requests > 0)
				\$j('html').ajaxComplete(function(e, xhr, settings) {
					if(settings.url.search('SysMessages;displayCategory') >= 0 || (settings.data && settings.data.search('class=Test') >= 0)){
						return;
					}
					if(Test.c.requests > 1){
						Test.c.requests--;
						return;
					}
					
					\$j('html').unbind('ajaxComplete');

					if(!Test".$this->A("TestStepTestID").".presentation)
					setTimeout(function(){ Test.c.next(); }, 1000);
					else
						setTimeout(function(){ Test.c.next(); }, 400);
				});
			else
				setTimeout(function(){ Test.c.next(); }, 1000);
		} else {
			Test.c.errors.push(".$this->getID().");
			Test.c.next();
		}";
	}
	
	private function clickButtonMouseAnimation() {
		return "
		TestManager.Mouse.moveTo(".(substr($this->A("TestStepEntry"), 0, 2) == "\$j" ? $this->A("TestStepEntry")."" : "\$j('".$this->A("TestStepEntry")."')").", true, '".$this->fixUml(addslashes($this->A("TestStepDescription")))."', function(){
			".$this->next()."
		});";
	}

	private function next($minSec = null){
		$duration = $this->A("TestStepAudioDuration");
		if($this->A("TestStepAudioDuration") == "0")
			$duration = $this->calcWait($this->A("TestStepDescription"));
		
		if($duration < $minSec)
			$duration = $minSec;
		
		return "TestManager.continueIn(".ceil(($duration + 1) * 1000).");";
	}
	
	private function fixUml($text){
		return str_replace(array("Ã¤", "Ã¶", "Ã¼", "Ã„", "Ã–", "Ãœ", "ÃŸ"), array("&auml;", "&ouml;", "&uuml;", "&Auml;", "&Ouml;", "&Uuml;", "&szlig;"), $text);
	}
	
	private function calcWait($text){
		$words = count(explode(" ", $text));
		$wait = ceil($words / 2);# + 1;# + 3;
		if($wait < 0)
			$wait = 0;
		#$wait *= 1000;
		
		return $wait;#"Test".$this->A("TestStepTestID").".presentation ? $wait : 0";
	}
}

?>