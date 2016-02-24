<?php
/**
 * FileBrowser
 */
namespace Sledgehammer\Devutils;
/**
 * Een eenvoudige filebrowser
 */
class FileBrowser extends VirtualFolder {

	private $path;
	private $options;
	private $extensionToIconMap = array(
		'docx' => 'msword.png',
		'doc' => 'msword.png',
		'php' => 'php.png',
		'ini' => 'ini_file.png',
		'html' => 'html.png',
		'xhtml' => 'html.png',
		'jpg' => 'image.png',
		'png' => 'image.png',
		'gif' => 'image.png',
		'txt' => 'text.png',
		'avi' => 'movie.png',
		'mkv' => 'movie.png',
		'mpg' => 'movie.png',
		'flv' => 'movie.png',
	); // De mapping tussen extenties en iconen

	/**
	 * @param string $path Het volledige pad
	 * @param array $options assoc array met de de volgende opties (die standaard op "false" staan)
	 *   (bool) show_fullpath       Show the fullpath at the top of the page
	 *   (bool) show_hidden_files  Show files starting with a "."
	 *   (bool) hide_filesize      Hide the filesize
	 *   (bool) hide_header        Verberg de header waar het path in getoond wordt
	 *   (enum) document_title     Verander de $Document->title in: 'subfolder', 'path'
	 *   (enum) php_files          Opties 'include', 'show_source', false(403 forbidden)
	 */

	function __construct($path, $options = array()) {
		parent::__construct();
		$this->handle_filenames_without_extension = true;
		$this->path = $path;
		$this->options = $options;
		$validOptions = array('show_fullpath', 'show_hidden_files', 'hide_filesize', 'hide_header', 'document_title', 'php_files');
		foreach ($options as $option => $value) {
			if (!in_array($option, $validOptions)) {
				notice('Option: "'.$option.'" is ignored', array('Valid options' => $validOptions));
			}
		}
	}

	/**
	 * Show directory listing
	 */
	function index() {
		if (!file_exists($this->path)) {
			notice('DataFolder: "'.$this->path.'" not found');
			return new HttpError(404);
		}
		$Dir = new \DirectoryIterator($this->path);
		$folders = array();
		$files = array();
		$folder_info = false;
		foreach ($Dir as $Entry) {
			if ($Entry->isDot() || $Entry->getFilename() == '.svn') {
				continue;
			}
			if (empty($this->options['show_hidden_files']) && (substr($Entry->getFilename(), 0, 1) == '.' || substr($Entry->getFilename(), 0, 3) == ':2e')) { // Should the file/folder be hidden?
				continue;
			}
			if ($Entry->getFilename() == 'folderinfo.txt') {
				$folder_info = file_get_contents($path.'folderinfo.txt');
			} elseif ($Entry->isDir()) {
				$folders[$Entry->getFilename().'/'] = array(
					'icon' => WEBROOT.'icons/folder.png',
					'label' => $Entry->getFilename()
				);
			} else {
				$label = $Entry->getFilename();
				if (empty($this->options['hide_filesize'])) {
					$label .= ' ('.$this->formatFilesize($Entry->getSize()).')';
				}
				$files[rawurlencode($Entry->getFilename())] = array(
					'icon' => $this->toIcon($Entry->getFilename()),
					'label' => $label
				);
			}
		}
		if (value($this->options['hide_header'])) {
			$visible_path = false;
		} elseif (value($this->options['show_fullpath'])) {
			$visible_path = $this->path;
		} else {
			$visible_path = basename($this->path);
		}
		ksort($folders);
		ksort($files);
		return new Template('FileBrowser.html', array(
			'path' => $visible_path,
			'folder_info' => $folder_info,
			'folders' => new Nav($folders),
			'files' => new Nav($files),
				), array(
			'title' => basename($visible_path).'/',
		));
	}

	function dynamicFilename($filename) {
		if (!file_exists($this->path.$filename)) {
			return $this->onFileNotFound();
		}
		$extension = file_extension($filename);
		if ($extension != 'php') {
			return new FileDocument($this->path.$filename);
		}
		switch (value($this->options['php_files'])) {

			case 'plain':
				return new FileDocument($this->path.$filename);

			case 'highlight':
				$html = highlight_file($this->path.$filename, true);
				$document = new Document;
				$document->component = new Html($html);
				return $document;

				exit();
				return new Html('<div style="font: 12px Courier, monospace;color: #008000;border: 1px dashed #CFCFCF;margin: 0;margin-bottom: 16px;padding: 8px;line-height: 14px;background-color: #FBFBFB;text-align: left">'.$html.'</div>');

			case 'include';
				chdir($this->path);
				include($this->path.$filename);
				exit;

			default:
				return new HttpError(403);
		}
	}

	function dynamicFoldername($folder) {
		$this->addCrumb($folder, $this->getPath(true));
		if (value($this->options['document_title']) == 'subfolder') {
			getDocument('');
		}
		$fileBrowser = new FileBrowser($this->path.$folder.'/', $this->options);
		return $fileBrowser->generateContent();
	}

	private function toIcon($filename) {
		$extension = file_extension($filename);
		$prefix = WEBROOT.'icons/';
		if (isset($this->extensionToIconMap[$extension])) {
			return $prefix.$this->extensionToIconMap[$extension];
		} else {
			return $prefix.'blank.png';
		}
	}

	/**
	 * @param int $bytes
	 */
	private function formatFilesize($bytes) {
		$sizes = array('K', 'M', 'G');
		$size = $bytes;
		foreach ($sizes as $name) {
			$size /= 1024;
			if ($size > 1024) {
				continue;
			}
			return number_format($size, 2).' '.$name.'iB';
		}
	}

}

?>