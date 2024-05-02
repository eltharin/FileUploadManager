<?php

namespace Eltharin\FileUploadManagerBundle\Form\FileManager;

use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface FileManagerInterface
{
	public function populate(&$fileData, UploadedFile $file, array $params);
	public function remove(&$fileData, array $options);
	public function getFileViewData(FormView $view, array $options) : array;
}