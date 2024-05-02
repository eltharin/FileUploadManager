<?php

namespace Eltharin\FileUploadManagerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
class FilePath
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $path = null;

    #[ORM\Column]
    private ?int $size = null;

    #[ORM\Column(length: 255)]
    private ?string $mimeType = null;

	private $_upload = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): static
    {
        $this->path = $path;

        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(int $size): static
    {
        $this->size = $size;

        return $this;
    }

	public function getMimeType(): ?string
	{
		return $this->mimeType;
	}

	public function setMimeType(string $mimeType): static
	{
		$this->mimeType = $mimeType;

		return $this;
	}
	public function getUpload()
	{
		return $this->_upload;
	}

	public function setUpload( $_upload): static
	{
		$this->_upload = $_upload;

		return $this;
	}


}
