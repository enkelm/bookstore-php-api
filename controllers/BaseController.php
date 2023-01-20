<?php

namespace Controllers;

use Exception;
use Models\RolesModel;
use Models\UsersModel;
use Util\JWT;

define("SECRET_KEY", "test123");

class BaseController
{
	private $usersModel;

	public function __construct()
	{
		$this->usersModel = new UsersModel();
	}

	public function __call($name, $arguments)
	{
		$this->sendOutput('', array('HTTP/1.1 404 Not Found'));
	}

	/** 
	 * Get URI elements.
	 * @return array
	 */
	protected function getUriSegments()
	{
		$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
		$uri = explode('/', $uri);
		return $uri;
	}

	/**
	 * Get querystring params.
	 * @return array
	 */
	protected function getQueryStringParams()
	{
		return parse_str($_SERVER['QUERY_STRING'], $query);
	}

	/**
	 * Send API output.
	 * @param mixed $data
	 * @param string $httpHeader
	 */
	protected function sendOutput($data, $httpHeaders = array())
	{
		header_remove('Set-Cookie');
		if (is_array($httpHeaders) && count($httpHeaders)) {
			foreach ($httpHeaders as $httpHeader) {
				header($httpHeader);
			}
		}

		echo json_encode($data);
		exit;
	}


	public function validateToken(bool $roleValidation = false, string $requiredRole = '')
	{
		try {
			$token = $this->getBearerToken();
			$payload = JWT::decode($token, SECRET_KEY, ['HS256']);

			$user = $this->usersModel->fetchBy(['Username' => $payload[0]->Username]);

			if (!is_array($user)) {
				$this->returnResponse(INVALID_USER_PASS, "This user is not found in our database.");
			}

			if ($roleValidation && $requiredRole !== '') {
				$roleId = $user[0]['Role'];
				$rolesModel = new RolesModel;
				$role = $rolesModel->fetchBy(['RoleId' => $roleId]);
				$roleName = $role[0]['Name'];

				if ($roleName === $requiredRole) {
					return true;
				} else {
					return false;
				}
			}

			return true;
		} catch (Exception $e) {
			$this->throwError(ACCESS_TOKEN_ERRORS, $e->getMessage());
		}
	}

	public function getAuthorizationHeader()
	{
		$headers = null;
		if (isset($_SERVER['Authorization'])) {
			$headers = trim($_SERVER["Authorization"]);
		} else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
			$headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
		} elseif (function_exists('apache_request_headers')) {
			$requestHeaders = apache_request_headers();
			// Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
			$requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
			if (isset($requestHeaders['Authorization'])) {
				$headers = trim($requestHeaders['Authorization']);
			}
		}
		return $headers;
	}

	public function getBearerToken()
	{
		$headers = $this->getAuthorizationHeader();
		// HEADER: Get the access token from the header
		if (!empty($headers)) {
			if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
				return $matches[1];
			}
		}
		$this->throwError(ATHORIZATION_HEADER_NOT_FOUND, 'Access Token Not found');
	}

	public function throwError($code, $message)
	{
		header("content-type: application/json");
		$errorMsg = json_encode(['error' => ['status' => $code, 'message' => $message]]);
		echo $errorMsg;
		exit;
	}
}
