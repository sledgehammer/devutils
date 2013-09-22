<?php
/**
 * HtmlElement
 */
namespace Sledgehammer;
/**
 * Baseclass for Components based on an Element.
 *
 * @package MVC
 */
class HtmlElement extends Object implements View {

	/**
	 * Element type: "div", "span", etc
	 * @var string
	 */
	public $tag = 'div';

	/**
	 * Attributes for the html element.
	 * (Keys are lowercase)
	 * @var array
	 */
	protected $attributes = array();

	/**
	 * Constructor
	 * @param array $options  Array containing properties and attribute values
	 */
	function __construct($options = array()) {
		// Extract attributes override
		if (array_key_exists('attributes', $options)) {
			$this->attributes = $options['attributes'];
			unset($options['attributes']);
		}
		// Set properties and attributes
		foreach ($options as $option => $value) {
			if (property_exists($this, $option)) {
				$this->$option = $value;
			} elseif (is_object($value) === false) {
				if (is_int($option)) {
					$this->setAttribute($value, true);
				} else {
					$this->setAttribute($option, $value);
				}
			} else {
				warning('Property "'.$option.'" doesn\'t exist in a '.get_class($this).' object', build_properties_hint(reflect_properties($this)));
			}
		}
	}

	/**
	 * Render the element(s).
	 */
	function render() {
		if (method_exists($this, 'renderContents')) {
			echo Html::element($this->tag, $this->attributes, true);
			$this->renderContents();
			echo '</', $this->tag, '>';
		} else {
			echo Html::element($this->tag, $this->attributes);
		}
	}

	function __toString() {
		try {
			return view_to_string($this);
		} catch (\Exception $e) {
			report_exception($e);
			return '';
		}
	}

	// jQuery interface

	/**
	 * Get the value of an attribute or set one or more attributes.
	 *
	 * @param string $attribute
	 * @param mixed $value optional
	 * @return this|HtmlElement|mixed
	 */
	function attr($attribute, $value = null) {
		if (func_num_args() === 1) {
			if (is_array($attribute) == false) {
				return $this->getAttribute($attribute); // Get the value
			}
		} else {
			$attribute = array($attribute => $value);
		}
		foreach ($attribute as $name => $value) {
			if (is_int($name)) {
				$this->setAttribute($value, true);
			} else {
				if ($value === '') {
					$this->removeAttribute($name);
				} else {
					$this->setAttribute($name, $value);
				}
			}
		}
		return $this;
	}

	/**
	 * Adds the specified class(es) to the element.
	 * @param string|array $class  One or more space-separated classes to be added to the class attribute.
	 * @return this|HtmlElement
	 */
	function addClass($class) {
		if (is_string($class) && strpos(trim($class), ' ')) {
			$class = explode(' ', $class);
		}
		if (is_array($class) === false) {
			$class = array($class);
		}
		$classes = explode(' ', trim($this->getAttribute('class')));
		foreach ($class as $name) {
			if (in_array($name, $classes) == false) {
				$classes[] = $name; // Add class
			}
		}
		$this->setAttribute('class', trim(implode(' ', $classes)));
		return $this;
	}

	/**
	 * Determine whether the element is assigned the given class.
	 * @param string $class
	 * @return boolean
	 */
	function hasClass($class) {
		$classes = explode(' ', trim($this->getAttribute('class')));
		return in_array($class, $classes);
	}

	/**
	 * Remove a single class, multiple classes, or all classes from the element.
	 * @param string|array $class
	 * @return this|HtmlElement
	 */
	function removeClass($class) {
		if (is_string($class) && strpos(trim($class), ' ')) {
			$class = explode(' ', $class);
		}
		if (is_array($class) === false) {
			$class = array($class);
		}
		$classes = explode(' ', trim($this->getAttribute('class')));
		foreach ($class as $name) {
			$key = array_search($name, $classes);
			if ($key !== false) {
				unset($classes[$key]); // Remove class
			}
		}
		$this->setAttribute('class', trim(implode(' ', $classes)));
		return $this;
	}

	/**
	 * Add or remove one or more classes from each element in the set of matched elements, depending on either the class’s presence or the value of the switch argument.
	 * @param string $class
	 * @return this|HtmlElement
	 */
	function toggleClass($class) {
		if (is_string($class) && strpos(trim($class), ' ')) {
			$class = explode(' ', $class);
		}
		if (is_array($class) === false) {
			$class = array($class);
		}
		foreach ($class as $name) {
			if ($this->hasClass($name)) {
				$this->removeClass($name);
			} else {
				$this->addClass($name);
			}
		}
		return $this;
	}

	// DOM Interface https://developer.mozilla.org/en-US/docs/Web/API/element

	/**
	 * Returns the value of the named attribute on the element.
	 * If the named attribute does not exist, the value returned will be null.
	 *
	 * @param string $name
	 * @return mixed
	 */
	function getAttribute($name) {
		$key = strtolower($name);
		if (array_key_exists($key, $this->attributes)) {
			return $this->attributes[$key];
		}
		return null;
	}

	/**
	 * Adds a new attribute or changes the value of an existing attribute on the element.
	 *
	 * @param string $name The name of the attribute.
	 * @param mixed $value The desired new value of the attribute
	 */
	function setAttribute($name, $value) {
		$this->attributes[strtolower($name)] = $value;
	}

	/**
	 * Removes an attribute from the element.
	 *
	 * @param string $name
	 */
	function removeAttribute($name) {
		unset($this->attributes[strtolower($name)]);
	}

	/**
	 * Returns a boolean value indicating whether the specified element has the specified attribute or not.
	 *
	 * @param string $name
	 * @return boolean
	 */
	function hasAttribute($name) {
		$key = strtolower($name);
		return array_key_exists($this->attributes[$key]) && $this->attributes[$key] !== null;
	}

}

?>
