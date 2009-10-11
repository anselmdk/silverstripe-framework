<?php
/**
 * A simple parser that allows you to map BBCode-like "shortcodes" to an arbitrary callback.
 *
 * Shortcodes can take the form:
 * <code>
 * [shortcode]
 * [shortcode attributes="example" /]
 * [shortcode]enclosed content[/shortcode]
 * </code>
 *
 * @package sapphire
 * @subpackage misc
 */
class ShortcodeParser {
	
	private static $instances = array();
	
	private static $active_instance = 'default';
	
	// -----------------------------------------------------------------------------------------------------------------
	
	protected $shortcodes = array();
	
	// -----------------------------------------------------------------------------------------------------------------
	
	/**
	 * Get the {@link ShortcodeParser} instance that is attached to a particular identifier.
	 *
	 * @param string $identifier Defaults to "default".
	 * @return ShortcodeParser
	 */
	public static function get($identifier = 'default') {
		if(!array_key_exists($identifier, self::$instances)) {
			self::$instances[$identifier] = new ShortcodeParser();
		}
		
		return self::$instances[$identifier];
	}
	
	/**
	 * Get the currently active/default {@link ShortcodeParser} instance.
	 *
	 * @return ShortcodeParser
	 */
	public static function get_active() {
		return self::get(self::$active_instance);
	}
	
	/**
	 * Set the identifier to use for the current active/default {@link ShortcodeParser} instance.
	 *
	 * @param string $identifier
	 */
	public static function set_active($identifier) {
		self::$active_instance = (string) $identifier;
	}
	
	// -----------------------------------------------------------------------------------------------------------------
	
	/**
	 * Register a shortcode, and attach it to a PHP callback.
	 *
	 * The callback for a shortcode will have the following arguments passed to it:
	 *   - Any parameters attached to the shortcode as an associative array (keys are lower-case).
	 *   - Any content enclosed within the shortcode (if it is an enclosing shortcode). Note that any content within
	 *     this will not have been parsed, and can optionally be fed back into the parser.
	 *   - The {@link ShortcodeParser} instance used to parse the content.
	 *   - The shortcode tag name that was matched within the parsed content.
	 *
	 * @param string $shortcode The shortcode tag to map to the callback - normally in lowercase_underscore format.
	 * @param callback $callback The callback to replace the shortcode with.
	 */
	public function register($shortcode, $callback) {
		if(is_callable($callback)) $this->shortcodes[$shortcode] = $callback;
	}
	
	/**
	 * Check if a shortcode has been registered.
	 *
	 * @param string $shortcode
	 * @return bool
	 */
	public function registered($shortcode) {
		return array_key_exists($shortcode, $this->shortcodes);
	}
	
	/**
	 * Remove a specific registered shortcode.
	 *
	 * @param string $shortcode
	 */
	public function unregister($shortcode) {
		if($this->registered($shortcode)) unset($this->shortcodes[$shortcode]);
	}
	
	/**
	 * Remove all registered shortcodes.
	 */
	public function clear() {
		$this->shortcodes = array();
	}
	
	// -----------------------------------------------------------------------------------------------------------------
	
	/**
	 * Parse a string, and replace any registered shortcodes within it with the result of the mapped callback.
	 *
	 * @param string $content
	 * @return string
	 */
	public function parse($content) {
		if(!$this->shortcodes) return $content;
		
		$shortcodes = implode('|', array_map('preg_quote', array_keys($this->shortcodes)));
		$pattern    = "/(.?)\[($shortcodes)(.*?)(\/)?\](?(4)|(?:(.+?)\[\/\s*\\2\s*\]))?(.?)/s";
		
		return preg_replace_callback($pattern, array($this, 'handleShortcode'), $content);
	}
	
	/**
	 * @ignore
	 */
	protected function handleShortcode($matches) {
		$prefix    = $matches[1];
		$suffix    = $matches[6];
		$shortcode = $matches[2];
		
		// allow for escaping shortcodes by enclosing them in double brackets ([[shortcode]])
		if($prefix == '[' && $suffix == ']') {
			return substr($matches[0], 1, -1);
		}
		
		$attributes = array(); // Parse attributes into into this array.
		
		if(preg_match_all('/(\w+) *= *(?:([\'"])(.*?)\\2|([^ "\'>]+))/', $matches[3], $match, PREG_SET_ORDER)) {
			foreach($match as $attribute) {
				if(!empty($attribute[4])) {
					$attributes[strtolower($attribute[1])] = $attribute[4];
				} elseif(!empty($attribute[3])) {
					$attributes[strtolower($attribute[1])] = $attribute[3];
				}
			}
		}
		
		return $prefix . call_user_func($this->shortcodes[$shortcode], $attributes, $matches[5], $this, $shortcode) . $suffix;
	}
	
}