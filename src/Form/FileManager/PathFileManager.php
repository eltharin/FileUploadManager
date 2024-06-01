<?php

namespace Eltharin\FileUploadManagerBundle\Form\FileManager;

use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class PathFileManager implements FileManagerInterface
{
	protected string $path;
	protected string $newName;
	protected string $projectDir;

	public function __construct(protected KernelInterface $kernel, protected SluggerInterface $slugger)
	{
		$this->projectDir = $this->kernel->getProjectDir();
	}

	public function populate(&$fileData, UploadedFile $file, $options)
	{
        $this->unlinkFile($fileData, $options);

		$this->newName = $this->getFileName($file, $options);

		$this->path = $this->getSavePath($file, $options);

        if($options['data_class'] !== null)
        {
            $fileData->setPath($this->path . DIRECTORY_SEPARATOR . $this->newName);
            $fileData->setName($file->getClientOriginalName());
            $fileData->setSize($file->getSize());
            $fileData->setMimeType($file->getClientMimeType());
        }
        else
        {
            $fileData = json_encode([
                'path' => $this->path . DIRECTORY_SEPARATOR . $this->newName,
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mimeType' => $file->getClientMimeType(),
            ]);

        }

		$this->saveFile($file, $options);
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
            $link = str_starts_with($view->vars['value']->getPath(), DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR) ? substr($view->vars['value']->getPath(), 7) : '';
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
            $link = is_callable($options['file_downloadLink']) ? $options['file_downloadLink']($view) : $options['file_downloadLink'];
        }

		return [
			'thumbnail' => $options['file_type'] == 'image' ? $link : '',
			'download_link' => $link,
            'download_name' => $filename,
		];
	}

	protected function getFileName(UploadedFile $file, array $options)
	{
		$originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
		return $this->slugger->slug($originalFilename) . '_' . uniqid() . '.' . $file->guessExtension();
	}

	protected function getSavePath(UploadedFile $file, array $options)
	{
		return DIRECTORY_SEPARATOR . $options['file_storage_path'];
	}

	protected function saveFile(UploadedFile $file, array $options)
	{
		$file->move( $this->projectDir . $this->path, $this->newName);
	}
}