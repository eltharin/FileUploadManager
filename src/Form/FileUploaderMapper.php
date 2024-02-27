<?php

namespace Eltharin\FileUploadManagerBundle\Form;

use Eltharin\FileUploadManagerBundle\Form\FileManager\FileManagerInterface;
use Symfony\Component\Form\Extension\Core\DataMapper\DataMapper;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploaderMapper extends DataMapper
{

	private ?FileManagerInterface $fileManager = null;
	private array $params = [];

	public function setParams(array $params, FileManagerInterface $fileManager) : static
	{
		$this->params = $params;
		$this->fileManager = $fileManager;
		return clone($this);
	}

	public function mapDataToForms(mixed $data, \Traversable $forms): void
	{
		if($this->params['is_entity'] === true)
		{
			parent::mapDataToForms($data, $forms);
		}
		else
		{
			$data = [];
		}
	}

	public function mapFormsToData(\Traversable $forms, mixed &$data): void
	{
		$iteratorForms = iterator_to_array($forms);

		if(array_key_exists('_delete', $iteratorForms) && $iteratorForms['_delete']->getData() == true)
		{
			$this->fileManager->remove($data, $this->params);
		}
		elseif(array_key_exists('_upload', $iteratorForms) && $iteratorForms['_upload']->getData() instanceof UploadedFile)
		{
			$this->fileManager->populate($data, $iteratorForms['_upload']->getData(), $this->params);
		}
		elseif($data === [])
		{
			$data = null;
		}

		if($this->params['is_entity'] === true)
		{
			parent::mapFormsToData($forms, $data);
		}
	}
}