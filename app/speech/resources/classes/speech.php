<?php

/**
 * audio class
 *
 * @method null download
 */
if (!class_exists('speech')) {
	class speech {

		/**
		 * declare private variables
		 */
		private $api_key;

		/** @var string $engine */
		private $engine;

		/** @var template_engine $object */
		private $speech_object;

		private $settings;

		public $audio_path;
		public $audio_filename;
		public $audio_format;
		public $audio_model;
		public $audio_voice;
		public $audio_language;
		public $audio_message;

		/**
		 * called when the object is created
		 */
		public function __construct(settings $settings = null) {
			//make the setting object
			if ($settings === null) {
				$settings = new settings();
			}

			//add the settings object to the class
			$this->settings = $settings;

			//build the setting object and get the recording path
			$this->api_key = $settings->get('speech', 'api_key');
			$this->engine = $settings->get('speech', 'engine');
		}

		/**
		 * get_voices - get the list voices
		 */
		public function get_voices() : array {

			//set the class interface to use the _template suffix
			$classname = 'speech_'.$this->engine;

			//create the object
			$object = new $classname($this->settings);

			//return the voices array
			return $object->get_voices();
		}

		/**
		 * get_voices - get the list voices
		 */
		public function get_models() : array {

			//set the class interface to use the _template suffix
			$classname = 'speech_'.$this->engine;

			//create the object
			$object = new $classname($this->settings);

			//return the voices array
			return $object->get_models();
		}

		/**
		 * is_translate_enabled - get whether the engine can do translations
		 */
		public function is_translate_enabled() : bool {

			//set the class interface to use the _template suffix
			$classname = 'speech_'.$this->engine;

			//create the object
			$object = new $classname($this->settings);

			//return the translate_enabled
			return $object->is_translate_enabled();
		}

		/**
		 * is_language_enabled - get whether the engine allows to set the language
		 */
		public function is_language_enabled() : bool {

			//set the class interface to use the _template suffix
			$classname = 'speech_'.$this->engine;

			//create the object
			$object = new $classname($this->settings);

			//return the language_enabled
			return $object->is_language_enabled();
		}

		/**
		 * get_languages - get the list languages
		 */
		public function get_languages() : array {

			//set the class interface to use the _template suffix
			$classname = 'speech_'.$this->engine;

			//create the object
			$object = new $classname($this->settings);

			//return the languages array
			return $object->get_languages();
		}

		/**
		 * speech - text to speech
		 */
		public function speech() {
			if (!empty($this->engine)) {
				//set the class interface to use the _template suffix
				$class_name = 'speech_'.$this->engine;

				//create the object
				$object = new $class_name($this->settings);

				//ensure the class has implemented the speech_interface interface
				if ($object instanceof speech_interface) {
					$object->set_path($this->audio_path);
					$object->set_filename($this->audio_filename);
					$object->set_format($this->audio_format);
					$object->set_voice($this->audio_voice);
					//$object->set_model($this->audio_model);
					//$object->set_language($this->audio_language);
					//$object->set_translate($this->audio_translate);
					$object->set_message($this->audio_message);
					$object->speech();
				}
				else {
					return false;
				}
			}
		}

	}
}

?>