<?php
declare(strict_types=1);

namespace BaseApi;
use ShinePHP\Http\{IncomingRequest, IncomingRequestException};

final class Api {

	private $required_request_method;
	private $content_type;
	private $input_data;

	public function __construct(string $method = 'GET', string $content_type = 'application/json') {

		\header('Allow: '.$method);
		\header('Content-Type: '.$content_type);

		$this->required_request_method = $method;
		$this->content_type = $content_type;

	}

	public function validate_request(array $required_params = array()): void {

		if (!IncomingRequest::validate_request_method($this->required_request_method)) {
			\header($_SERVER['SERVER_PROTOCOL'].' 405 Method Not Allowed');
			throw new ApiException('Sorry, you used the wrong request method. Please use: '.$this->required_request_method);
		}

		if (!IncomingRequest::validate_content_type($this->content_type)) {
			\header($_SERVER['SERVER_PROTOCOL'].' 400 Bad Request');
			throw new ApiException('Sorry, you used the wrong content type. Please use: '.$this->content_type);
		}

		if ($this->required_request_method === 'GET') {
			$input_data = $_GET;
		} else if (\strpos($this->content_type, 'json') !== false) {
			$input_data = IncomingRequest::retrieve_json_input();
		} else {
			$input_data = $_POST;
		}

		if (!empty($required_params)) {
			$this->set_input_data($input_data, $required_params);

		} else {
			$this->set_input_data($input_data);
		}

	}

	public function validate_request_no_data(): void {

		if (!IncomingRequest::validate_request_method($this->required_request_method)) {
			\header($_SERVER['SERVER_PROTOCOL'].' 405 Method Not Allowed');
			throw new ApiException('Sorry, you used the wrong request method. Please use: '.$this->required_request_method);
		}

		if (!IncomingRequest::validate_content_type($this->content_type)) {
			\header($_SERVER['SERVER_PROTOCOL'].' 400 Bad Request');
			throw new ApiException('Sorry, you used the wrong content type. Please use: '.$this->content_type);
		}

	}

	private function set_input_data(array $input_data, array $required_params = array()): void {

		try {
			IncomingRequest::require_input_data($input_data, $required_params);
			$this->input_data = $input_data;
		} catch (IncomingRequestException $ire) {
			\header($_SERVER['SERVER_PROTOCOL'].' 400 Bad Request');
			throw new ApiException($ire->getMessage());
		}

	}

	public function get_input_data(): array {
		return $this->input_data;
	}

	public static function output(bool $success, string $message, array $return_data = array()): string {

		$api_output = array(
			'success' => $success,
			'message' => $message
		);

		if ($success) {
			$api_output['data'] = $return_data;
		}

		return \json_encode($api_output);

	}

}

final class ApiException extends \Exception {}
