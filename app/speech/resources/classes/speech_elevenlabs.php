<?php


/**
 * ai_elevenlabs class
 *
 */
class speech_elevenlabs implements speech_interface {

	private $voice;
	private $path;
	private $message;
	private $format;
	private $filename;
	private $languages;
	private $api_key;
	private $model;

	public function __construct($settings) {
		$this->voice = "";
		$this->path = "";
		$this->message = "";
		$this->format = "mp3";
		$this->filename = "";
		//build the setting object and get the recording path
		$this->api_key = $settings->get('speech', 'api_key');
	}

	public function set_filename(string $audio_filename) {
		$this->filename = $audio_filename;
	}

	public function set_format(string $audio_format) {
		$this->format = $audio_format;
	}

	public function set_message(string $audio_message) {
		$this->message = $audio_message;
	}

	public function set_path(string $audio_path) {
		$this->path = $audio_path;
	}

	public function set_voice(string $audio_voice) {
		$this->voice = $audio_voice;
	}

	public function speech(): bool {

		//get the model automatically
		$model_id = $this->get_model();

		//if model is version 1 replace it with version 2
		if ($model_id == 'eleven_multilingual_v1') {
			$model_id = 'eleven_multilingual_v2';
		}

		// set the request URL
		$url = 'https://api.elevenlabs.io/v1/text-to-speech/' . $this->voice;

		// set the request headers
		$headers[] = 'Content-Type: application/json';
		$headers[] = 'xi-api-key: '.$this->api_key;

		// set the http data
		$data['model_id'] = $model_id;
		$data['text'] = $this->message;
		//$data['pronunciation_dictionary_locators'][0]['pronunciation_dictionary_id'];
		//$data['pronunciation_dictionary_locators'][0]['version_id'];
		$data['voice_settings']['similarity_boost'] = 1;
		$data['voice_settings']['stability'] = 1;
		$data['voice_settings']['style'] = 0;
		$data['voice_settings']['use_speaker_boost'] = 'true';

		// initialize curl handle
		$ch = curl_init($url);

		// set the curl options
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

		// run the curl request and get the response
		$response = curl_exec($ch);

		// get the errors
		$error = curl_error($ch);

		// get the http code
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		// close the handle
		curl_close($ch);

		// show the result when there is an error
		if ($http_code != 200) {
			echo "error ".$error."\n";
			echo "http_code ".$http_code."\n";
			if (strlen($response) < 500) {
				view_array(json_decode($response, true));
			}
			exit;
		}

		// save the audio file
		if ($http_code == 200) {
			//get the file and path details
			$path_array = pathinfo($this->filename);

			//save the file as an mp3
			file_put_contents($this->path.'/'.$path_array['filename'].'.mp3', $response);

			//find the location of sox
			$command = 'which sox';
			$sox = system($command);

			//use sox to convert mp3 to wav
			if (file_exists($this->path.'/'.$path_array['filename'].'.mp3')) {
				$command = $sox . " '".$this->path."/".$path_array['filename'].".mp3' -r 44100 -b 16 '".$this->path."/".$path_array['filename'].".wav'";
				system($command);
			}

			//delete the extra file
			if (file_exists($this->path.'/'.$path_array['filename'].'.wav')) {
				unlink($this->path.'/'.$path_array['filename'].'.mp3');
			}

			return true;
		}
		return false;

		//$curl = new curl('https://api.elevenlabs.io/v1/text-to-speech/' . $this->voice);
		//$response = $curl->set_headers($headers)->post(json_encode($data));
		//$error = $curl->get_error();
		//$http_code = $curl->get_http_code();
		//if ($curl->get_http_code() == 200) {
		//save the audio
		//if ($http_code == 200) {
		//	file_put_contents($this->path . '/' . $this->filename, $response);
		//	return true;
		//}
		//return false;
	}

	public function is_language_enabled(): bool {
		return false;
	}

	public function is_model_enabled(): bool {
		return false;
	}

	public function get_languages(): array {
		return ['en' => 'English'];
	}

	public function get_voices(): array {
		$return_value = [];
		$url = 'https://api.elevenlabs.io/v1/voices';
		$headers = [
			'Content-Type: application/json',
			"xi-api-key: $this->api_key",
		];
		$curl = curl_init();
		curl_setopt_array($curl, [
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
		]);

		$response = curl_exec($curl);
		$error = curl_error($curl);

		curl_close($curl);
		if (!empty($response)) {
			$json_array = json_decode($response, true);
			foreach($json_array['voices'] as $row) {
				$voice_id = $row['voice_id'];
				$name = $row['name'];
				$gender = $row['labels']['gender'] ?? '';
				$accent = $row['labels']['accent'] ?? '';
				$use_case = $row['labels']['use case'] ?? '';
				$recommended_model = $row['high_quality_base_model_ids'][0] ?? '';
				$return_value[$voice_id] = "$name ($gender, $accent";
				if (!empty($use_case)) {
					$return_value[$voice_id] .= ", " . $use_case;
				}
				$return_value[$voice_id] .= ")";
				if (!empty($recommended_model)) {
					$return_value[$voice_id] .= " - $recommended_model";
				}
			}
		}
		return $return_value;
	}

	public function set_language(string $audio_language) {
		$this->languages = $audio_language;
	}

	public function set_model(string $model): void {
		if (array_key_exists($model, $this->get_models())) {
			$this->model = $model;
		}
	}

	public function get_model() {

			//if the voice is not set return the default model
			if (empty($this->voice)) {
				return 'eleven_monolingual_v1';
			}

			//get the voices and automatically find the model
			$url = 'https://api.elevenlabs.io/v1/voices';
			$headers = [
				'Content-Type: application/json',
				"xi-api-key: $this->api_key",
			];
			$curl = curl_init();
			curl_setopt_array($curl, [
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => "",
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 30,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => "GET",
			]);
			$response = curl_exec($curl);
			$error = curl_error($curl);
			curl_close($curl);
			if (!empty($response)) {
				$json_array = json_decode($response, true);
				foreach($json_array['voices'] as $row) {
					if ($this->voice == $row['voice_id'] && !empty($row['high_quality_base_model_ids'][0])) {
						return $row['high_quality_base_model_ids'][0];
					}
				}
				return 'eleven_monolingual_v1';
			}
	}

	public function get_models(): array {
		return [
			'eleven_monolingual_v1' => 'Default',
			'eleven_turbo_v1' => 'Eleven Turbo v1',
			'eleven_turbo_v2' => 'Eleven Turbo v2',
			'eleven_multilingual_v1' => 'Eleven Multilingual v1',
			'eleven_multilingual_v2' => 'Eleven Multilingual v2',
		];
	}
}

?>
