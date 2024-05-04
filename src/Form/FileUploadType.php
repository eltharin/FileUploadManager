<?php

namespace Eltharin\FileUploadManagerBundle\Form;

use Eltharin\FileUploadManagerBundle\Entity\FileInline;
use Eltharin\FileUploadManagerBundle\Entity\FilePath;
use Eltharin\FileUploadManagerBundle\Form\FileManager\PathFileManager;
use Eltharin\FileUploadManagerBundle\Form\FileUploaderMapper;
use Eltharin\FileUploadManagerBundle\Form\FileManager\InlineFileManager;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FileUploadType extends AbstractType
{
	public function __construct(private ParameterBagInterface $parameterBag,
                                private ServiceLocator $fileManagerServiceLocator,
	                            private FileUploaderMapper      $fileUploaderMapper,
	)
	{
	}


	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder->add('_upload', FileType::class, $options['upload_options']);

		$builder->addEventListener(
			FormEvents::SUBMIT,
			function (FormEvent $event) use($options) : void {
				if($options['allow_delete'] == true)
				{
					if($event->getData() !== null && !$event->getForm()->has('_delete'))
					{
						$event->getForm()->add('_delete', CheckboxType::class, ['mapped' => false, 'data' => false, 'attr' => ['checked' => false]]);
					}
					elseif($event->getData() === null && $event->getForm()->has('_delete'))
					{
						$event->getForm()->remove('_delete');
					}
				}
			}
		);

		$builder->addEventListener(
			FormEvents::POST_SET_DATA,
			function (FormEvent $event) use($options): void {

				if($options['allow_delete'] == true && $event->getData())
				{
					$event->getForm()->add('_delete', CheckboxType::class, ['mapped' => false, 'data' => false, 'attr' => ['checked' => false]]);
				}

			}
		);

		$builder->setDataMapper($this->fileUploaderMapper->setParams($options, $this->fileManagerServiceLocator->get($options['file_manager'])));
	}


	public function finishView(FormView $view, FormInterface $form, array $options) :void
	{
		if($view->vars['value'] === null)
		{
			$view->vars['file'] = [
				'thumbnail' => null,
				'download_link' => null,
			];
		}
		else
		{
			$view->vars['file'] = $this->fileManagerServiceLocator->get($options['file_manager'])->getFileViewData($view, $options);
		}

		if($view->vars['value'] != null && $options['allow_update'] == false)
		{
			$offset = array_search('file', $view['_upload']->vars['block_prefixes']);
			array_splice($view['_upload']->vars['block_prefixes'], $offset+1, 0, 'hidden_file');
		}
	}

	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'required' => false,
			'file_manager' => $this->parameterBag->get('eltharin_fileuploadmanager_default__filemanager'),
			'allow_update' => $this->parameterBag->get('eltharin_fileuploadmanager_default__allow_update'),
			'allow_delete' => $this->parameterBag->get('eltharin_fileuploadmanager_default__allow_delete'),
			'file_storage_json' => $this->parameterBag->get('eltharin_fileuploadmanager_default__file_storage_json'),
			'file_storage_path' => $this->parameterBag->get('eltharin_fileuploadmanager_default__file_storage_path'),
			'file_type' => null,
			'delete_on_remove' => $this->parameterBag->get('eltharin_fileuploadmanager_default__delete_on_remove'),
			'file_downloadLink' => null,
            'upload_options' => [],
            'delete_options' => [],
            'data_class' => $this->parameterBag->get('eltharin_fileuploadmanager_default__data_class'),
		]);

        $resolver->setDefault('is_entity', function (Options $options): bool {
            return $options['data_class'] !== null;
        });

        $resolver->setDefault('file_storage_inline', function (Options $options): bool {
            if($options['data_class'] !== null)
            {
                if(is_a($options['data_class'],FilePath::class,true))
                {
                    return false;
                }
                if(is_a($options['data_class'],FileInline::class,true))
                {
                    return true;
                }
            }
            return $this->parameterBag->get('eltharin_fileuploadmanager_default__file_storage_inline');
        });

		$resolver->setNormalizer('file_manager', function (Options $options, ?string $value): ?string {
			if ($value === null)
			{
				if($options['file_storage_inline'])
				{
					$value = InlineFileManager::class;
				}
				else
				{
					$value = PathFileManager::class;
				}
			}
			return $value;
		});
	}
}