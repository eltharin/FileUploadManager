<?php

namespace Eltharin\FileUploadManagerBundle\Form\FileManager;

use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class InlineFileManager implements FileManagerInterface
{
	public function populate(&$fileData, UploadedFile $file, $options)
	{
        if($options['data_class'] !== null)
        {
            $fileData->setContent(base64_encode($file->getContent()));
            $fileData->setName($file->getClientOriginalName());
            $fileData->setSize($file->getSize());
            $fileData->setMimeType($file->getClientMimeType());
        }
		elseif($options['file_storage_json'])
		{
            $fileData = json_encode([
				'content' => base64_encode($file->getContent()),
				'name' => $file->getClientOriginalName(),
				'size' => $file->getSize(),
				'mimeType' => $file->getClientMimeType(),
			]);
		}
		else
		{
            $fileData = base64_encode($file->getContent());
		}
	}

	public function remove(&$fileData, array $options)
	{
		$fileData = null;
	}

	public function getFileViewData(FormView $view, array $options) : array
	{
        $link = '';
		$filename = 'file';

        if($options['data_class'] !== null)
        {
            $link = 'data:' . $view->vars['value']->getMimeType() . ';base64,' . $view->vars['value']->getContent();
            $filename = $view->vars['value']->getName();
        }
        elseif($options['file_storage_json'])
		{
			$data = json_decode($view->vars['value'], true);
            $link = 'data:' . $data['mimeType'] . ';base64,' . ($data['content']??'');
            $filename = $data['name']??'';
		}
		else
		{
            $link = 'data:;base64, ' . $view->vars['value'];
		}

		return [
			'thumbnail' => $options['file_type'] == 'image' ? $link : '',
			'download_link' => $link,
			'download_name' => 'image.jpg',
		];
	}
}