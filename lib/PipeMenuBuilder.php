<?php
//PipeMenuBuilder class -- It builds menus.

class PipeMenu {
	private $className = "pipemenu";
	private $entries = array();

	public function setClass($class) {
		$this->className = $class;
	}

	public function getClass() {
		return $this->className;
	}

	public function add($entry) {
		$this->entries[] = $entry;
	}

	public function pop() {
		return array_pop($this->entries);
	}

	public function shift() {
		return array_shift($this->entries);
	}

	public function build() {
		if(count($this->entries) == 0)
			return "";

		$html = "<ul class=\"" . $this->className . "\">";

		foreach ($this->entries as $entry) {
			$html .= $entry->build();
		}

		$html .= "</ul>";
		return $html;
	}
}

interface PipeMenuEntry {
	public function build($l=true);
}

class PipeMenuLinkEntry implements PipeMenuEntry {
	private $label;
	private $action;
	private $id;
	private $args;

	public function __construct($label, $action, $id = 0, $args = "") {
		$this->label = $label;
		$this->action = $action;
		$this->id = $id;
		$this->args = $args;
	}

	public function build($l=true) {
		return ($l?"<li>":'')."<a href=\"" . htmlspecialchars(actionLink($this->action, $this->id, $this->args)) . "\">" . $this->label . "</a>".($l?"</li>":'');
	}
}

class PipeMenuTextEntry implements PipeMenuEntry {
	private $text;

	public function __construct($text) {
		$this->text = $text;
	}

	public function build($l=true) {
		return ($l?"<li>":'') . htmlspecialchars($this->text) . ($l?"</li>":'');
	}
}

class PipeMenuHtmlEntry implements PipeMenuEntry {
	private $html;

	public function __construct($html) {
		$this->html = $html;
	}

	public function build($l=true) {
		return ($l?"<li>":'') . $this->html . ($l?"</li>":'');
	}
}
