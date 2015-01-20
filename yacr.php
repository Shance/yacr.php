<?php

/**
 *
 * @copyright  Copyright (C) 2012 - 2014 Saity74 LLC. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */
if (version_compare(PHP_VERSION, '5.3.10', '<'))
{
	die('Your host needs to use PHP 5.3.10 or higher to run this version of Joomla!');
}

define('_JEXEC', 1);


error_reporting(0);
ini_set('display_errors', 0);


if (!defined('_JDEFINES'))
{
	define('JPATH_BASE', realpath(__DIR__.'/../'));
	
	require_once JPATH_BASE . '/includes/defines.php';
}

require_once JPATH_LIBRARIES . '/import.legacy.php';

JError::setErrorHandling(E_NOTICE, 'message');
JError::setErrorHandling(E_WARNING, 'message');
JError::setErrorHandling(E_ERROR, 'message', array('JError', 'customErrorPage'));

require_once JPATH_LIBRARIES . '/cms.php';

ob_start();
if (file_exists(JPATH_CONFIGURATION . '/configuration.php'))
{
	if (file_get_contents('http://'.$_SERVER['HOSTNAME'].'/configuration.php'))
	{
		require_once JPATH_CONFIGURATION . '/configuration.php';
	}
}

ob_end_clean();

if (!class_exists('JConfig'))
{
	class JConfig {
		public $offline = '0';
		public $offline_message = 'Сайт закрыт на техническое обслуживание.<br /> Пожалуйста, зайдите позже.';
		public $display_offline_message = '1';
		public $offline_image = '';
		public $sitename = '';
		public $editor = 'codemirror';
		public $captcha = '0';
		public $list_limit = '10';
		public $access = '1';
		public $debug = '1';
		public $debug_lang = '0';
		public $dbtype = 'mysql';
		public $host = 'localhost';
		public $user = '';
		public $password = '';
		public $db = '';
		public $dbprefix = '';
		public $live_site = '';
		public $secret = 'cnNT0izyyiVFLfHN';
		public $gzip = '1';
		public $error_reporting = 'development';
		public $helpurl = 'http://help.joomla.org/proxy/index.php?option=com_help&keyref=Help{major}{minor}:{keyref}';
		public $ftp_host = '';
		public $ftp_port = '';
		public $ftp_user = '';
		public $ftp_pass = '';
		public $ftp_root = '';
		public $ftp_enable = '0';
		public $offset = 'UTC';
		public $mailonline = '1';
		public $mailer = 'mail';
		public $mailfrom = '';
		public $fromname = '';
		public $sendmail = '/usr/sbin/sendmail';
		public $smtpauth = '1';
		public $smtpuser = '';
		public $smtppass = '';
		public $smtphost = 'smtp.yandex.ru';
		public $smtpsecure = 'ssl';
		public $smtpport = '465';
		public $caching = '0';
		public $cache_handler = 'file';
		public $cachetime = '15';
		public $MetaDesc = '';
		public $MetaKeys = '';
		public $MetaTitle = '1';
		public $MetaAuthor = '1';
		public $MetaVersion = '0';
		public $robots = '';
		public $sef = '1';
		public $sef_rewrite = '1';
		public $sef_suffix = '1';
		public $unicodeslugs = '0';
		public $feed_limit = '10';
		public $log_path = '/logs';
		public $tmp_path = '/tmp';
		public $lifetime = '15';
		public $session_handler = 'database';
		public $memcache_persist = '1';
		public $memcache_compress = '0';
		public $memcache_server_host = 'localhost';
		public $memcache_server_port = '11211';
		public $memcached_persist = '1';
		public $memcached_compress = '0';
		public $memcached_server_host = 'localhost';
		public $memcached_server_port = '11211';
		public $proxy_enable = '0';
		public $proxy_host = '';
		public $proxy_port = '';
		public $proxy_user = '';
		public $proxy_pass = '';
		public $MetaRights = '';
		public $sitename_pagetitles = '0';
		public $force_ssl = '0';
		public $session_memcache_server_host = 'localhost';
		public $session_memcache_server_port = '11211';
		public $session_memcached_server_host = 'localhost';
		public $session_memcached_server_port = '11211';
		public $frontediting = '1';
		public $feed_email = 'author';
		public $cookie_domain = '';
		public $cookie_path = '';
		public $asset_id = '1';
	}
}

require_once JPATH_BASE.'/libraries/import.php';

/**
 * Yacr Manager
 *
 */

class YacrManager extends JApplicationWeb
{
	public $mimeType 			= 'text/pain';
	public $charSet 			= 'utf-8';
	
	protected $task;
	protected $taskMap;
	protected $exclude			= array( '.svn', 'CVS','.DS_Store','__MACOSX');
	protected $excludefilter 	= array('^\..*', '.*~', '\.swp');
	protected $request			= null;
	protected $signature        = null;
	
	protected $file_ext_icons = array(
		'php'		=> 'fa-file-code-o',
		'html'		=> 'fa-file-code-o',
		'js' 		=> 'fa-file-code-o',
		'xml' 		=> 'fa-file-code-o',
		'jpg'		=> 'fa-file-image-o',
		'txt'		=> 'fa-file-text-o',
		'zip'		=> 'fa-file-archive-o',
		'gz'		=> 'fa-file-archive-o',
		'__default' => 'fa-file-o'
	);
	
	public function __construct()
	{
		parent::__construct();
		
		$xMethods = get_class_methods('JApplicationWeb');
		
		$this->taskMap['__default'] = 'display';
		
		$r = new ReflectionClass($this);
		$rMethods = $r->getMethods(ReflectionMethod::IS_PUBLIC);

		foreach ($rMethods as $rMethod)
		{
			$mName = $rMethod->getName();

			if (!in_array($mName, $xMethods) || $mName == 'display')
			{
				$this->methods[] = strtolower($mName);
				$this->taskMap[strtolower($mName)] = $mName;
			}
		}
		
		$this->request = new JRegistry();
	}
	
	public function initialise($session = NULL, $document = NULL, $language = NULL, $dispatcher = NULL)
	{
		
		// Create the language based on the application logic.
		if ($language !== false)
		{
			$this->loadLanguage($language);
		}

		$this->loadDispatcher($dispatcher);

		return $this;
	}
	
	public function execute()
	{

		$this->task = strtolower($this->input->get('task', 'display'));

		if (isset($this->taskMap[$this->task]))
		{
			$doTask = $this->taskMap[$this->task];
		}
		elseif (isset($this->taskMap['__default']))
		{
			$doTask = $this->taskMap['__default'];
		}
		else
		{
			throw new Exception(JText::sprintf('JLIB_APPLICATION_ERROR_TASK_NOT_FOUND', $this->task), 404);
		}
		
		$this->loadData();
		
		if (!$this->verifyData())
		{
			header('HTTP/1.0 401 Unauthorized - Invalid Signature');
			$this->close();
		}
		$this->doTask = $doTask;

		$this->$doTask();
		
		$this->respond();
	}
	
	protected function respond()
	{
		
		$this->setHeader('Content-Type', $this->mimeType . '; charset=' . $this->charSet);
		$this->setHeader('Expires', 'Mon, 1 Jan 2001 00:00:00 GMT', true);
		$this->setHeader('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT', true);
		$this->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0', false);

		$this->setHeader('Pragma', 'no-cache');
		
		$this->sendHeaders();

		echo $this->getBody();
		
		$this->close();
	}
	
	protected function loadData()
	{
		$this->signature = base64_decode($this->input->post->get('__sig', '', 'base64'));
		
		if ($this->signature)
		{
			$post = $this->input->post->getArray();
			unset($post['__sig']);
			$this->request->loadArray($post);
		}
		
		return $this->request;
	}
	
	protected function verifyData()
	{
		$data = $this->request->toString();
		
		$f = fopen('/etc/ssl/cert/public.key', 'r');
		$pkey = fread($f, 2048);
		fclose($f);
		
		$public_key_pem = openssl_pkey_get_public($pkey);
		$v = openssl_verify($data, $this->signature, $public_key_pem, "sha256WithRSAEncryption");
		
		if ($v == 1) {
			return true;
		} elseif ($v == 0) {
			return false;
		} else {
			die("error: ".openssl_error_string());
		}
	}
	
	public function display()
	{
		//no echo!
		//use $this->prependBody($content);
		//or $this->appendBody($content);
		
	}
	
	public function ls()
	{
		$fileinfo = [];
		$ret = [];
		$path = $this->request->get('path', base64_encode('/'));
		
		$path = base64_decode($path);
		
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		
		$folders 	= JFolder::folders($path, '.', false, true);
		
		$files 		= JFolder::files($path,
			'.',
			false,
			true,
			$this->exclude,
			$this->excludefilter
		);
		
		$filelist = array_merge($folders, $files);
		
		foreach($filelist as $key => $file)
		{
			$file = JPath::clean($file);
			$stat = stat($file);
			$perms_str = JPath::getPermissions($file);
			$ext = substr(strrchr($file, '.'), 1);
	
			
			$fileinfo[$key] = $stat;
			$fileinfo[$key]['full_path'] 	= $file;
			$fileinfo[$key]['base64_path'] 	= base64_encode($file);
			$fileinfo[$key]['filename'] 		= basename($file);
			$fileinfo[$key]['perms'] 		= $perms_str;
			$fileinfo[$key]['mtime'] 		= date('d.m.Y H:i:s', $stat['mtime']);
			$fileinfo[$key]['type'] 			= is_dir($file) ? 'd' : '-';
			$fileinfo[$key]['ext'] 			= $ext;
			$fileinfo[$key]['icon_class'] 	= $this->getIconByExt($ext);
		}
		
		$ret['files'] = $fileinfo;
		$ret['path'] = $path;
		
		$ret['base64_path'] = base64_encode($path);
		$ret['base64_up'] = base64_encode(dirname($path));
		
		
		$ret['base64_up'] = $path == '/' ? $ret['base64_path'] : base64_encode(dirname($path));
		
		$respond = array(
			'result' => true,
			'code' => 200,
			'message' => 'Список файлов загружен успешно',
			'files' => json_encode($ret)
		);
		
		$this->appendBody(json_encode($respond));
		$this->respond();
		
	}
	
	public function cat()
	{
		$ret = array();
		$path = $this->request->get('path', base64_encode('/'));
		
		$path = base64_decode($path);
		
		
		if (!file_exists($path))
		{
			$respond = array(
				'result' => false,
				'code' => 404,
				'message' => 'Файл не найден'
			);
			
			$this->appendBody(json_encode($respond));
			$this->respond();
		}
		
		if (!is_file($path))
		{
			$respond = array(
				'result' => false,
				'code' => 400,
				'message' => 'Не удается открыть файл'
			);

			$this->appendBody(json_encode($respond));
			$this->respond();
		}
		
		echo file_get_contents($path);
	}
	
	public function save()
	{
		
		$path = $this->request->get('path', base64_encode('/'));
		$content = $this->request->get('content', '');
		
		$path = base64_decode($path);
		$content = urldecode(base64_decode(urldecode($content)));
		
		if (!file_exists($path))
		{
			$this->appendBody(json_encode(array(
				'result' => false,
				'code' => 404,
				'message' => 'Файл не найден'
			)));
			$this->respond();
		}
		
		if (!is_file($path))
		{
			$this->appendBody(json_encode(array(
				'result' => false,
				'code' => 403,
				'message' => 'Не удается открыть файл'
			)));
			$this->respond();
		}
		
		if (file_put_contents($path, $content) !== false)
		{
			$this->appendBody(json_encode(array(
				'result' => true,
				'code' => 200,
				'message' => 'Файл успешно сохранен.'
			)));
			$this->respond();
		} else {
			$this->appendBody(json_encode(array(
				'result' => false,
				'code' => 403,
				'message' => 'Не удалось записать в файл'
			)));
			$this->respond();
		}
	}
	
	public function load_cfg()
	{
		$config = JFactory::getConfig();
		$this->appendBody($config->toString());
		unset($config);
		
		$this->respond();
	}
	
	public function rm()
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		
		$path = $this->request->get('path', '');
		$respond = array(
			'result' => true,
			'code' => 200,
			'message' => 'Удаление завершено успешно'
		);

		$notdeleted = [];

		if(!$path) {
			$this->close();	
		}
		
		$path = json_decode(base64_decode($path));
		
		foreach($path as $base64_path)
		{
			$one_file_path = base64_decode($base64_path);
			if (is_dir($one_file_path))
			{
				if(!JFolder::delete($one_file_path))
				{
					$notdeleted[] = $one_file_path;
				}
			} else {
				if(!JFile::delete($one_file_path))
				{
					$notdeleted[] = $one_file_path;
				}
			}
		}

		if(!empty($notdeleted)) {
			$respond = array(
				'result' => false,
				'code' => 400,
				'message' => 'Не удалось удалить следующие файлы: ' . implode(',', $notdeleted),
			);
		}
		
		$this->appendBody(json_encode($respond));
		$this->respond();
	}
	
	public function zip()
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		
		$path = $this->request->get('path', '');
		$name = $this->request->get('name', '');
		$base_dir = $this->request->get('base', '');
		
		if(!$path)
		{
			$respond = array(
				'result' => false,
				'code' => 400,
				'message' => 'Неверные параметры'
			);
			$this->appendBody(json_encode($respond));
			$this->respond();
		}
		
		$filenames = json_decode(base64_decode($path));
		
		if(!$base_dir)
		{
			$respond = array(
				'result' => false,
				'code' => 400,
				'message' => 'Неверные параметры'
			);

			$this->appendBody(json_encode($respond));
			$this->respond();
		} else {
			$base_dir = base64_decode($base_dir);
		}
		
		chdir($base_dir);
		
		if(!$name)
		{
			$name = 'archive';
		} else {
			$name = base64_decode($name);
		}
		
		
		if(sizeof($filenames) == 1)
		{
			$base_name = $filenames[0];
			$name = $filenames[0];
		} else {
			$base_name = $name;
		}
		
		$i = 1;
		while(is_file($name . '.zip') && $i++ < 100)
		{
			echo $name;
			$name = $base_name . '(' . $i . ')';
		}
		
		$zip = new ZipArchive();
		if(!$zip->open($name.'.zip', ZipArchive::CREATE))
		{
			$respond = array(
				'result' => false,
				'code' => 400,
				'message' => 'Не могу создать архив'
			);

			$this->appendBody(json_encode($respond));
			$this->respond();
		}
		
		foreach($filenames as $filename)
		{
			if(!$zip->addFile($filename))
			{
				$zip->close();
				$respond = array(
					'result' => false,
					'code' => 400,
					'message' => "Не могу поместить $filename в архив"
				);

				$this->appendBody(json_encode($respond));
				$this->respond();
			}
		}
		
		$zip->close();
		$respond = array(
			'result' => true,
			'code' => 200,
			'message' => "Архив $name.zip успешно создан"
		);

		$this->appendBody(json_encode($respond));
		$this->respond();
	}
	
	public function unzip()
	{
	    
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		
		$path = $this->request->get('path', '');
		
		if(!$path) {
			$this->close();	
		}

		$path = base64_decode($path);
		
		chdir(preg_replace('/\/[^\/]*$/', '', $path));
		
		if(!is_file($path))
		{
			$respond = array(
				'result' => false,
				'code' => 400,
				'message' => 'Неверные параметры'
			);
			$this->appendBody(json_encode($respond));
			$this->respond();
		}
		
		$zip = new ZipArchive();
		if(!$zip->open($path)) {
			$respond = array(
				'result' => false,
				'code' => 400,
				'message' => 'Не могу открыть архив'
			);
			$this->appendBody(json_encode($respond));
			$this->respond();
		};
		
		$exists_files = [];
		for ($idx = 0; $idx < $zip->numFiles; $idx++) {
			$filename = $zip->getNameIndex($idx);
			if(is_file($filename)) {
				$exists_files[] = $filename;
			}
		}
		
		if(empty($exists_files)){
			$zip->extractTo('./');
			$respond = array(
				'result' => true,
				'code' => 200,
				'message' => 'Архив успешно распакован'
			);
		} else {
			$respond = array(
				'result' => false,
				'code' => 400,
				'message' => 'Не удалось распаковать архив, потому что следующие файлы существуют: ' . implode(', ', $exists_files)
			);
		}
		
		$zip->close();
		$this->appendBody(json_encode($respond));
		
		$this->respond();
	}
	
	public function addYandexCounter()
	{
		$ya_counter = $this->request->get('ya_counter', '');
		
		$db = JFactory::getDbo();
		
		$query = $db->getQuery(true);
		$query->select('id, template, params')
			->from('#__template_styles')
			->where('home = 1 AND client_id = 0');
		$db->setQuery($query);
		
		$tmpl_style = $db->loadObject();
		
		
		if ($tmpl_style->template == 'blank_j3')
		{
			$params = new JRegistry();
			$params->loadObject($tmpl_style->params);
		
			$params->set('yandex_metrika_id', $ya_counter);
			
			$data = $params->toString();
			
			$query = $db->getQuery(true);
			$query->update('#__template_styles')
				->set('params = '.$db->quote($data))
				->where('id = '.$tmpl_style->id);
			$db->setQuery($query);
			
			$db->execute();
			
		}
		else
		{
			$tmpl_index = JPATH_BASE.'/templates/'.$tmpl_style->template.'/index.php';
			$index_php = file_get_contents($tmpl_index);
			$ya_code = '<!-- Yandex.Metrika counter --><script type="text/javascript">(function (d, w, c) { (w[c] = w[c] || []).push(function() { try { w.yaCounter'.$ya_counter.' = new Ya.Metrika({id:'.$ya_counter.', webvisor:true, clickmap:true, accurateTrackBounce:true}); } catch(e) { } }); var n = d.getElementsByTagName("script")[0], s = d.createElement("script"), f = function () { n.parentNode.insertBefore(s, n); }; s.type = "text/javascript"; s.async = true; s.src = (d.location.protocol == "https:" ? "https:" : "http:") + "//mc.yandex.ru/metrika/watch.js"; if (w.opera == "[object Opera]") { d.addEventListener("DOMContentLoaded", f, false); } else { f(); } })(document, window, "yandex_metrika_callbacks");</script><noscript><div><img src="//mc.yandex.ru/watch/'.$ya_counter.'" style="position:absolute; left:-9999px;" alt="" /></div></noscript><!-- /Yandex.Metrika counter --><script>function toggleContainer(name)
		{
			var e = document.getElementById(name);// MooTools might not be available ;)
			e.style.display = (e.style.display == 'none') ? 'block' : 'none';
		}</script><div id="system-debug" class="profiler"><h1>Консоль отладки Joomla!</h1><div class="dbg-header" onclick="toggleContainer('dbg_container_session');"><a href="javascript:void(0);"><h3>Сессия</h3></a></div><div  style="display: none;" class="dbg-container" id="dbg_container_session"><div class="dbg-header" onclick="toggleContainer('dbg_co