<?php
namespace App\Libraries;

class Saj {

	protected $plant_uid, $device_serial_number, $cookie, $date;
	protected $base_url = "https://fop.saj-electric.com";
	protected $path_url = "/saj/monitor/site/getPlantDetailChart2";
	protected $query_string;

	public function __construct(string $plant_uid, string $device_serial_number, array $cookie_parameters)
	{
		$this->plant_uid = $plant_uid;
		$this->device_serial_number = $device_serial_number;
		$this->cookie = $this->buildCookieString($cookie_parameters);
	}

	public function buildCookieString(array $cookies)
	{
		$cookie_string = "";

		foreach ($cookies as $cookie_key => $cookie_value)
			$cookie_string .= $cookie_key . "=" . $cookie_value . ";";

		return $cookie_string;
	}

	public function buildQueryString(array $query_parameters)
	{
		$query_string = "?" . http_build_query($query_parameters);

		return $query_string;
	}

	public function request(array $query_parameters = [])
	{
		$query_parameters += [
			"plantuid" => $this->plant_uid,
			"deviceSnArr" => $this->device_serial_number,
			"chartDateType" => 1,
			"chartCountType" => 2,
		];

		$this->query_string = $this->buildQueryString($query_parameters);

		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL => $this->base_url . $this->path_url . $this->query_string,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_COOKIE => $this->cookie,
			//CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			//CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			//CURLOPT_CUSTOMREQUEST => "GET",
			//CURLOPT_POSTFIELDS => "{}",
		));
		$response_headers = [];
		curl_setopt($ch, CURLOPT_HEADERFUNCTION,
			function($curl, $header) use (&$response_headers)
			{
				$len = strlen($header);
				$header = explode(':', $header, 2);
				if (count($header) < 2) // ignore invalid headers
					return $len;

				$response_headers[strtolower(trim($header[0]))][] = trim($header[1]);

				return $len;
			}
		);

		$response = curl_exec($ch);
		if ($err = curl_error($ch))
			dd("cURL Error #:" . $err);

		curl_close($ch);

		return json_decode($response);
	}
}