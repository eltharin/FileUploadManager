<?php

namespace Eltharin\FileUploadManagerBundle\Form\FileManager;

use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class PathFileManager implements FileManagerInterface
{
	public function __construct(private string $projectDir, private SluggerInterface $slugger)
	{
	}

	public function populate(&$fileData, UploadedFile $file, $options)
	{
        $this->unlinkFile($fileData, $options);
        
		$originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
		$newName = $this->slugger->slug($originalFilename) . '_' . uniqid() . '.' . $file->guessExtension();
		$path = DIRECTORY_SEPARATOR . $options['file_storage_path'];

        if($options['data_class'] !== null)
        {
            $fileData->setPath($path . DIRECTORY_SEPARATOR . $newName);
            $fileData->setName($file->getClientOriginalName());
            $fileData->setSize($file->getSize());
            $fileData->setMimeType($file->getClientMimeType());
        }
        else
        {
            $fileData = json_encode([
                'path' => $path . DIRECTORY_SEPARATOR . $newName,
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mimeType' => $file->getClientMimeType(),
            ]);

        }

		$file->move( $this->projectDir . $path, $newName);
	}

	public function remove(&$fileData, array $options)
	{
        $this->unlinkFile($fileData, $options);
		$fileData = null;
	}

    protected function unlinkFile($fileData, $options)
    {
        if(!empty($fileData) && $options['delete_on_remove'])
        {
            if($options['data_class'] !== null)
            {
                $path = $fileData->getPath();
            }
            else
            {
                $data = json_decode($fileData, true);
                $path = $data['path'];
            }

            if($path != null && file_exists($this->projectDir . $path))
            {
                unlink($this->projectDir . $path);
            }
        }
    }

	public function getFileViewData(FormView $view, array $options) : array
	{
        $link = '';
        $filename = 'file';

        if($options['data_class'] !== null)
        {
            $link = str_starts_with($view->vars['value']->getPath(), DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR) ? substr($view->vars['value']->getPath(), 7) : $view->vars['value']->getPath();
            $filename = $view->vars['value']->getName();
        }
        else
        {
            $data = json_decode($view->vars['value'], true);

            $link = str_starts_with( $data['path'], DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR) ? substr($data['path'], 7) : '';
            $filename = $data['name'];
        }

        $link = str_replace(DIRECTORY_SEPARATOR, '/',$link);

        if($options['file_downloadLink'] !== null)
        {
            $link = $options['file_downloadLink']($view);
        }

		return [
			'thumbnail' => $options['file_type'] == 'image' ? $link : '',
			'download_link' => $link,
            'download_name' => $filename,
		];
	}
}