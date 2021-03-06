<?php
namespace KanbanBoard;

class GithubOAuth implements IOAuth {

    private $baseUrl="https://github.com/login/oauth/";

    private $_client_id = NULL;
	private $_client_secret = NULL;
	protected $_state = NULL;

    public function __construct(string $client_id, string $client_secret) {

        $this->_client_id = $client_id;
        $this->_client_secret = $client_secret;

    }

    private function setState():string {
		$this->_state = substr(md5(microtime()), 0, 18);
		return $this->_state;
	}

	public function getBaseUrl():string {
		return $this->baseUrl;
	}

	private function getState():string {
		if ($this->_state == NULL) {
			$this->setState();
		}
		return $this->_state;
	}

    public function authorize(): void
    {
        $url = 'Location: '.$this->baseUrl.'authorize';
		$url .= '?client_id=' . $this->_client_id;
		$url .= '&scope=repo';
		$url .= '&state='.$this->setState().'';
		header($url);
		exit();
    }

    public function getAccessToken(string $code):string
    {
        $url = $this->baseUrl.'access_token';
		$data = array(
			'code' => $code,
			'state' => ''.$this->getState().'',
			'client_id' => $this->_client_id,
			'client_secret' => $this->_client_secret);
		$options = array(
			'http' => array(
				'method' => 'POST',
				'header' => "Content-type: application/x-www-form-urlencoded\r\n",
				'content' => http_build_query($data),
			),
		);
		$context = stream_context_create($options);
		$result = file_get_contents($url, false, $context);
		if ($result === FALSE)
			die('Error');
		$result = explode('=', explode('&', $result)[0]);
		array_shift($result);
		return array_shift($result);
    }
}

?>