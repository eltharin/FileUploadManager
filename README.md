Symfony FileUploadManager Bundle
==========================

[![Latest Stable Version](http://poser.pugx.org/eltharin/fileuploadmanager/v)](https://packagist.org/packages/eltharin/fileuploadmanager) 
[![Total Downloads](http://poser.pugx.org/eltharin/fileuploadmanager/downloads)](https://packagist.org/packages/eltharin/fileuploadmanager) 
[![Latest Unstable Version](http://poser.pugx.org/eltharin/fileuploadmanager/v/unstable)](https://packagist.org/packages/eltharin/fileuploadmanager) 
[![License](http://poser.pugx.org/eltharin/fileuploadmanager/license)](https://packagist.org/packages/eltharin/fileuploadmanager)


Installation
------------

* Require the bundle with composer:

``` bash
composer require eltharin/fileuploadmanager
```


What is fileuploadmanager Bundle?
---------------------------

FileUploadManager Bundle make files uploads simpliests, with a very little configuration you can have your uploads automatics.

How works ? 
---------------------------

You can save files in two different ways : 
* inline : the content will be save in database in base64
* in filesystem : database will store the file path

You can choose to create an entity for files or not.

Cases
---

You have an entity Foo witch have a field bar reprensenting a file. Bar is from type Text and nullable (if not file), for a path you can set string.

``` php
#[ORM\Entity(repositoryClass: FooRepository::class)]
class Foo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $bar = null;

...

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBar(): ?string
    {
        return $this->bar;
    }

    public function setBar(?string $bar): static
    {
        $this->bar = $bar;

        return $this;
    }

 ...
}
```

Differents cases now :
--
1- content inline in base64

In the FormType : you have just to add an item for bar property, from FileUploadType with some parameters.

Now if you want set the file's content directly in the field in base64 you can set these options : 

``` php
use Eltharin\FileUploadManagerBundle\Form\FileUploadType;

class FooType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('bar', FileUploadType::class, [
                'file_storage_inline' => true,
                'file_storage_json' => false,
            ]);
    }
}
```

Field Content will be like :

`` 
iVBORw0KG`...`5ErkJggg==
``

-------

2- content inline in json

If you want the field content to be a Json with the name, size, content and mime-type, options will be : 

``` php
use Eltharin\FileUploadManagerBundle\Form\FileUploadType;

class FooType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('bar', FileUploadType::class, [
                'file_storage_inline' => true,
                'file_storage_json' => true, //true is the default value, you can omit this line
            ]);
    }
}
```
Field Content will be like :

``
{"content":"iVBORw0KG`...`5ErkJggg==","name":"55c488f3e3888f054ca531dbd7252c34.png","size":81049,"mimeType":"image\/png"}
``

-------

3- content in filesystem in json in public folder

If you want the field content to be a Json with the name, size, mime-type ans path to the file, options will be : 

``` php
use Eltharin\FileUploadManagerBundle\Form\FileUploadType;

class FooType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('bar', FileUploadType::class, [
                'file_storage_inline' => false, // false is the default value, you can omit this line
                'file_storage_json' => true, // true is the default value, you can omit this line
                'file_storage_path' => '/public/files',// "/public/files" is the default value, you can omit this line
            ]);
    }
}
```
Field Content will be like :

``
{"path":"\/public\/files\/55c488f3e3888f054ca531dbd7252c34_65de10fd784d8.png","name":"55c488f3e3888f054ca531dbd7252c34.png","size":81049,"mimeType":"image\/png"}
``

-------

4- content in filesystem in json NOT in public folder

If you want the field content to be a Json with the name, size, content and mime-type

``` php
use Eltharin\FileUploadManagerBundle\Form\FileUploadType;

class FooType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('bar', FileUploadType::class, [
                'file_storage_inline' => false, // false is the default value, you can omit this line
                'file_storage_json' => true, // true is the default value, you can omit this line
                'file_storage_path' => '/var/data/files',
            ]);
    }
}
```
Field Content will be like :

``
{"path":"\/var\/data\/files\/55c488f3e3888f054ca531dbd7252c34_65de10fd784d8.png","name":"55c488f3e3888f054ca531dbd7252c34.png","size":81049,"mimeType":"image\/png"}
``

As you can see, the cases 3 and 4 are similar because it's the same operation BUT, as the file is not in the public folder, the system does'nt kwon how search it for make it downloadable so in the Form the link disapear.

For resolve that you can pass a function to the parameter : 'file_downloadLink' as this : 

``` php
use Eltharin\FileUploadManagerBundle\Form\FileUploadType;

class FooType extends AbstractType
{
    public function __construct(private UrlGeneratorInterface $router)
    {

    }
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('bar', FileUploadType::class, [
                'file_storage_inline' => false, // false is the default value, you can omit this line
                'file_storage_json' => true, // true is the default value, you can omit this line
                'file_storage_path' => '/var/data/files',
                'file_downloadLink' => function (FormView $view) {return $this->router->generate('app_foo_showimg', ['id' => $view->parent->vars['value']->getId()]);},
            ]);
    }
}
```

In the function, we ask to the route generator to get the route named app_foo_showimg whitch need Foo id to show associate img, Foo id is obtained getting $view->parent->vars['value']->getId(). $view is the UplodFormType, $view->parent the precedent type so FooType and getting the value and its id.

the file_downloadLink function is also used in case of image type file, in this case image is also display in the form, to set this option, you have to set 'file_type' option to 'image'.


----
Files in separate entities
--

You either can want to heve files stored in a separate entity for lighten DB Queries or to have a ManyToX relation for have many files associates to your entity

You have two MappedSuperClass for that, Eltharin\FileUploadManagerBundle\Entity\FileInline and Eltharin\FileUploadManagerBundle\Entity\FilePath you just have to create your own entity, extends it form one or other MappedSuperClass and make your own relations.

We will change our Foo Class with an entity File : 

``` php
namespace App\Entity;

#[ORM\Entity(repositoryClass: FooRepository::class)]
class Foo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne( cascade: ['persist'])]
    private ?File $fileInEntity = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFileInEntity(): ?File
    {
        return $this->fileInEntity;
    }

    public function setFileInEntity(?File $fileInEntity): static
    {
        $this->fileInEntity = $fileInEntity;

        return $this;
    }
}
```
and File entity : 
```php
namespace App\Entity;

#[ORM\Entity(repositoryClass: FileRepository::class)]
class File extends \Eltharin\FileUploadManagerBundle\Entity\FileInline
{
}
```

Now you juste have to set the entityclass as : 


``` php
use Eltharin\FileUploadManagerBundle\Form\FileUploadType;
use App\Entity\File;

class FooType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('imageentityinline', FileUploadType::class, [
                'data_class' => File::class,
            ]);
    }
}
```

When using Eltharin\FileUploadManagerBundle\Entity\FileInline , you can omit file_storage_inline options, it is set automaticly.

You can use a manyToMany relation too, with its associated Collection : 

``` php
#[ORM\Entity(repositoryClass: FooRepository::class)]
class Foo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToMany(targetEntity: FilePath::class,cascade: ['persist'])]
    private Collection $manyimage;
    
    /**
     * @return Collection<int, FilePath>
     */
    public function getManyimage(): Collection
    {
        return $this->manyimage;
    }

    public function addManyimage(FilePath $manyimage): static
    {
        if (!$this->manyimage->contains($manyimage)) {
            $this->manyimage->add($manyimage);
        }

        return $this;
    }

    public function removeManyimage(FilePath $manyimage): static
    {
        $this->manyimage->removeElement($manyimage);

        return $this;
    }
}
```

Form Type : 

``` php
use Eltharin\FileUploadManagerBundle\Form\FileUploadType;
use App\Entity\File;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FooType extends AbstractType
{
    public function __construct(private UrlGeneratorInterface $router)
    {

    }
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('manyimage', CollectionType::class,[
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                
                'entry_type' => FileUploadType::class,
                'entry_options' => [
                    'data_class' => File::class,
                    'file_type' => 'image',
                    'file_storage_path' => '/var/data/files',
                    'file_downloadLink' => function (FormView $view) {return $this->router->generate('app_foo_showimg2', ['foo' => $view->parent->parent->vars['value']->getId(), 'file' => $view->vars['value']->getId()]);},
                    'delete_on_remove' => false,
                ],

            ]);
    }
}
```

In the case of a ManyToMany relation consider to set the delete_on_remove option to false, this option can be replaced by an eventlistener on File Entity on the onDelete Event.

---
Other Options
---

- allow_update : Allow user to replace file by setting an input[type=file] if a file already exist. Default value : true;

- allow_delete : Allow user to delete the file without replacing by other, in the form a checkbox will appear for that.

- upload_options : options to pass to input field
- delete_options : options to pass to delete checkbox field

- file_manager : if you miss some options, you can create your own File manager Service : 

    - You have to create a class with autoConfonfigure Attribute with tag app.fileManager
    - class must implements Eltharin\FileUploadManagerBundle\Form\FileManager\FileManagerInterface
    - complete your own functions

```php 

#[Autoconfigure(tags: ['app.fileManager'])]
class FileManagerService implements FileManagerInterface
{
    public function populate(&$fileData, UploadedFile $file, $options)
	{
        ...
	}

	public function remove(&$fileData, array $options)
	{
		$fileData = null;
	}

	public function getFileViewData(FormView $view, array $options) : array
	{
	    ...
		return [
			'thumbnail' => $thumbnail,
			'download_link' => $link,
			'download_name' => $name,
		];
	}
}

```
 and pass it to Type options : 

``` php
use Eltharin\FileUploadManagerBundle\Form\FileUploadType;
use App\Entity\File;

class FooType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', FileUploadType::class, [
                'file_manager' => FileManagerService::class,
            ]);
    }
}
```

You don't like my default values ? 

You can create a Yaml (or XML or PHP according your configuration) in /conf/packages/ named eltharin_file_upload_manager.yaml

and set your own default values: 

eltharin_file_upload_manager:
    default:
        allow_update: false
        allow_delete: true
        data_class: null
        delete_on_remove: false
        file_storage_inline: true
        file_manager: 'App\Service\FileManager'
        file_storage_json: false
        file_storage_path: '/public/documents'