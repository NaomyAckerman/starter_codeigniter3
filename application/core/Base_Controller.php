<?php defined('BASEPATH') or exit('No direct script access allowed');

class Base_Controller extends MX_Controller
{
	private string $layout = '';
	private string $theme = '';
	private array $data = [];

	const HTTP_OK = 200;
	const HTTP_CREATED = 201;
	const HTTP_NOT_MODIFIED = 304;
	const HTTP_BAD_REQUEST = 400;
	const HTTP_UNAUTHORIZED = 401;
	const HTTP_FORBIDDEN = 403;
	const HTTP_NOT_FOUND = 404;
	const HTTP_METHOD_NOT_ALLOWED = 405;
	const HTTP_NOT_ACCEPTABLE = 406;
	const HTTP_INTERNAL_ERROR = 500;

	public function __construct()
	{
		parent::__construct();
		$this->data = [
			'meta' => (object) [
				'content' => '',
				'module' => $this->router->fetch_module(),
				'class' => $this->router->fetch_class(),
				'method' => $this->router->fetch_method(),
				'theme' => $this->theme,
				'layout' => $this->layout,
				'breadcrumbs' => [],
				'scripts' => [],
				'links' => [],
			],
			'session' => $this->session->userdata()
		];
	}

	public function responseJson(array $response = [], int $code = 200)
	{
		$this->output
			->set_status_header($code)
			->set_content_type('application/json')
			->set_output(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
			->_display();
		exit();
	}

	public function setScript($scripts, array $attr = [])
	{
		$scripts = is_array($scripts) ? $scripts : [$scripts];
		$attr = html_attributes($attr);
		foreach ($scripts as $script) {
			$data_script[] = html_escape('<script src="' . $script . '" ' . $attr . '></script>');
		}
		$this->data['meta']->scripts = $data_script;
		return $this;
	}

	public function setLink($links, array $attr = [])
	{
		$links = is_array($links) ? $links : [$links];
		$attr = html_attributes($attr);
		foreach ($links as $link) {
			$this->data['meta']->links[] = html_escape('<link href="' . $link . '" ' . $attr . '/>');
		}
		return $this;
	}

	public function setBreadcrumb($key, $link = null)
	{
		$keys = is_array($key) ? $key : [$key => $link];
		foreach ($keys as $key => $link) {
			$this->data['meta']->breadcrumbs[$key] = $link;
		}
		return $this;
	}

	public function setMeta($key, $value = null)
	{
		$keys = is_array($key) ? $key : [$key => $value];
		$this->data['meta'] = (object) array_merge($keys, (array) $this->data['meta']);
	}

	public function setTheme(string $theme = null, string $layout = null)
	{
		if ($layout) {
			$this->data['meta']->layout = $layout;
			$this->layout = $layout;
		}
		if ($theme) {
			$this->data['meta']->theme = $theme;
			$this->theme = $theme;
			$this->config->set_item('error_views_path', FCPATH . "themes/$theme/errors/");
		}
		return $this;
	}

	public function setData($key, $value = null)
	{
		$keys = is_array($key) ? $key : [$key => $value];
		foreach ($keys as $key => $value) {
			in_array($key, ['meta', 'session'])
				? show_error("Data '$key' cannot be set because it is immutable")
				: $this->data[$key] = $value;
		}
		return $this;
	}

	public function getData(string $key = null)
	{
		$data = $this->data;
		$keys = $key ? explode('.', $key) : [];
		foreach ($keys as $key) {
			if ($data === null)
				break;
			$data = is_array($data) ? $data[$key] ?? null : $data->$key ?? null;
		}
		;
		return $data;
	}

	public function display(string $view, bool $return = false)
	{
		// set content content
		$content = $this->load->view($view, $this->data, true);
		$this->data['meta']->content = $content;

		// set view content & check if used layout
		$view_content = $this->layout ? ($this->theme ? "$this->theme/layouts/$this->layout" : "layouts/$this->layout") : $view;

		return $this->load->view($view_content, $this->data, $return);
	}
}

/* End of file Base_Controller.php */
